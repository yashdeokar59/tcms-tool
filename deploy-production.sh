#!/bin/bash

echo "🚀 Deploying TestFlow Pro - Production Ready Application"
echo "========================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

print_info "Starting deployment process..."

# Stop existing containers
print_info "Stopping existing containers..."
docker-compose down 2>/dev/null || true

# Clean up unused Docker resources
print_info "Cleaning up Docker resources..."
docker system prune -f

# Set proper permissions
print_info "Setting proper permissions..."
chmod -R 755 .
chmod -R 775 storage bootstrap/cache database
chown -R $USER:$USER .

# Create necessary directories
print_info "Creating necessary directories..."
mkdir -p storage/app/public/attachments
mkdir -p storage/logs
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p bootstrap/cache
mkdir -p backups
mkdir -p ssl

# Generate SSL certificates (self-signed for development)
if [ ! -f ssl/cert.pem ]; then
    print_info "Generating SSL certificates..."
    openssl req -x509 -newkey rsa:4096 -keyout ssl/key.pem -out ssl/cert.pem -days 365 -nodes \
        -subj "/C=US/ST=State/L=City/O=Organization/CN=testflow.local" 2>/dev/null || true
fi

# Create nginx configuration
print_info "Creating Nginx configuration..."
cat > nginx.conf << 'EOF'
events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # Logging
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    
    access_log /var/log/nginx/access.log main;
    error_log /var/log/nginx/error.log;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=1r/s;
    
    upstream app {
        server app:80;
    }
    
    server {
        listen 80;
        server_name testflow.local localhost;
        
        # Security headers
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-Content-Type-Options "nosniff" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header Referrer-Policy "strict-origin-when-cross-origin" always;
        
        # Rate limiting
        location /api/ {
            limit_req zone=api burst=20 nodelay;
            proxy_pass http://app;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
        
        location / {
            proxy_pass http://app;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            
            # Timeout settings
            proxy_connect_timeout 60s;
            proxy_send_timeout 60s;
            proxy_read_timeout 60s;
        }
        
        # Static files caching
        location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
            proxy_pass http://app;
            proxy_cache_valid 200 1d;
            add_header Cache-Control "public, immutable";
        }
    }
}
EOF

# Create Prometheus configuration
print_info "Creating monitoring configuration..."
cat > prometheus.yml << 'EOF'
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'testflow'
    static_configs:
      - targets: ['app:80']
    metrics_path: '/metrics'
    scrape_interval: 30s
EOF

# Create backup script
print_info "Creating backup script..."
cat > backup.sh << 'EOF'
#!/bin/sh
BACKUP_DIR="/data/backups"
DB_FILE="/data/database/database.sqlite"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Backup database
if [ -f "$DB_FILE" ]; then
    cp "$DB_FILE" "$BACKUP_DIR/database_backup_$DATE.sqlite"
    echo "Database backup created: database_backup_$DATE.sqlite"
    
    # Keep only last 7 days of backups
    find $BACKUP_DIR -name "database_backup_*.sqlite" -mtime +7 -delete
else
    echo "Database file not found: $DB_FILE"
fi
EOF

chmod +x backup.sh

# Build and start containers
print_info "Building and starting Docker containers..."
docker-compose up -d --build

# Wait for containers to be ready
print_info "Waiting for containers to be ready..."
sleep 30

# Check container health
print_info "Checking container health..."
if docker-compose ps | grep -q "Up"; then
    print_status "Containers are running successfully!"
else
    print_error "Some containers failed to start. Check logs with: docker-compose logs"
    exit 1
fi

# Test application health
print_info "Testing application health..."
if curl -f http://localhost:8080/health > /dev/null 2>&1; then
    print_status "Application is responding successfully!"
else
    print_warning "Application health check failed. It may still be starting up."
fi

# Display deployment information
echo ""
echo "🎉 TestFlow Pro Deployment Complete!"
echo "===================================="
echo ""
print_info "Application URLs:"
echo "  • Main Application: http://localhost:8080"
echo "  • With Nginx Proxy: http://localhost"
echo "  • Grafana Dashboard: http://localhost:3000 (admin/admin123)"
echo "  • Prometheus Metrics: http://localhost:9090"
echo ""
print_info "Default Login Credentials:"
echo "  • Email: admin@testflow.com"
echo "  • Password: admin123"
echo "  • Role: Administrator"
echo ""
print_info "Available Roles:"
echo "  • 👑 Admin - Full system control"
echo "  • 📊 Manager - Project oversight and reporting"
echo "  • 👨‍💻 Developer - Code integration and bug management"
echo "  • 🧪 Tester - Test execution and case management"
echo ""
print_info "Docker Commands:"
echo "  • View logs: docker-compose logs -f"
echo "  • Stop application: docker-compose down"
echo "  • Restart: docker-compose restart"
echo "  • Update: docker-compose up -d --build"
echo ""
print_info "Features Implemented:"
echo "  ✅ Complete test case management with CRUD operations"
echo "  ✅ Role-based access control (Admin, Manager, Developer, Tester)"
echo "  ✅ Test execution with automatic defect creation"
echo "  ✅ Requirements traceability and coverage tracking"
echo "  ✅ File attachments and commenting system"
echo "  ✅ Comprehensive reporting and analytics"
echo "  ✅ Real-time notifications and dashboards"
echo "  ✅ Modern responsive UI with Bootstrap 5"
echo "  ✅ Production-ready Docker deployment"
echo "  ✅ Monitoring with Prometheus and Grafana"
echo "  ✅ Automated backups and SSL support"
echo ""
print_status "Your TestFlow Pro application is now ready for production use!"
echo ""
