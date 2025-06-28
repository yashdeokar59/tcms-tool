#!/bin/bash

# TestFlow Pro Database Backup Script
# Runs daily backups and maintains retention policy

set -e

# Configuration
BACKUP_DIR="/backups"
MYSQL_HOST="${MYSQL_HOST:-mysql}"
MYSQL_USER="${MYSQL_USER:-testflow_user}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-testflow_pass_2024}"
MYSQL_DATABASE="${MYSQL_DATABASE:-testflow_pro}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Generate backup filename with timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/testflow_backup_$TIMESTAMP.sql"
COMPRESSED_FILE="$BACKUP_FILE.gz"

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Create database backup
create_backup() {
    log "Starting database backup..."
    
    # Create SQL dump
    mysqldump -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --hex-blob \
        --opt \
        "$MYSQL_DATABASE" > "$BACKUP_FILE"
    
    # Compress the backup
    gzip "$BACKUP_FILE"
    
    # Verify backup was created
    if [ -f "$COMPRESSED_FILE" ]; then
        BACKUP_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
        log "Backup created successfully: $(basename "$COMPRESSED_FILE") ($BACKUP_SIZE)"
    else
        log "ERROR: Backup file was not created"
        exit 1
    fi
}

# Clean old backups
cleanup_old_backups() {
    log "Cleaning up backups older than $RETENTION_DAYS days..."
    
    # Find and delete old backup files
    DELETED_COUNT=$(find "$BACKUP_DIR" -name "testflow_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete -print | wc -l)
    
    if [ "$DELETED_COUNT" -gt 0 ]; then
        log "Deleted $DELETED_COUNT old backup files"
    else
        log "No old backup files to delete"
    fi
}

# Verify database connection
verify_connection() {
    log "Verifying database connection..."
    
    if mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 1" "$MYSQL_DATABASE" > /dev/null 2>&1; then
        log "Database connection successful"
    else
        log "ERROR: Cannot connect to database"
        exit 1
    fi
}

# Main backup process
main() {
    log "Starting TestFlow Pro backup process"
    
    verify_connection
    create_backup
    cleanup_old_backups
    
    log "Backup process completed successfully"
}

# Run backup if script is executed directly
if [ "${BASH_SOURCE[0]}" == "${0}" ]; then
    main "$@"
fi

# Set up cron job for daily backups
if [ ! -f /etc/cron.d/testflow-backup ]; then
    echo "0 2 * * * root /backup.sh >> /var/log/backup.log 2>&1" > /etc/cron.d/testflow-backup
    chmod 0644 /etc/cron.d/testflow-backup
    crontab /etc/cron.d/testflow-backup
fi

# Keep container running
exec crond -f
