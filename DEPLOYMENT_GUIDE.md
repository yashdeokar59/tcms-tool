# 🚀 TestFlow Pro - Production Deployment Guide

## ✅ **PRODUCTION-READY APPLICATION WITH MYSQL & SSL**

Your TestFlow Pro application is now optimized for production deployment with Docker Compose, MySQL database, SSL support, and comprehensive security features.

---

## 📋 **Pre-Deployment Checklist**

### **Server Requirements**
- ✅ **Docker** (20.10+)
- ✅ **Docker Compose** (2.0+)
- ✅ **Minimum 2GB RAM**
- ✅ **Minimum 10GB Storage**
- ✅ **SSL Certificates** (optional - self-signed will be generated)

### **Network Requirements**
- ✅ **Port 80** (HTTP - redirects to HTTPS)
- ✅ **Port 443** (HTTPS - main application)
- ✅ **Port 3000** (Grafana monitoring - optional)
- ✅ **Port 9090** (Prometheus metrics - optional)

---

## 🚀 **One-Command Deployment**

### **Quick Start**
```bash
# Clone your application
git clone <your-repository>
cd testcase-management-tool

# Deploy with one command
chmod +x deploy.sh
./deploy.sh
```

**That's it! Your application will be running at `https://your-domain.com`**

---

## 🔧 **Manual Deployment Steps**

### **1. Server Preparation**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker
```

### **2. Application Setup**
```bash
# Clone application
git clone <your-repository>
cd testcase-management-tool

# Set permissions
chmod +x deploy.sh
chmod +x docker/backup/backup.sh
```

### **3. SSL Certificate Setup**

#### **Option A: Use Your Own SSL Certificates**
```bash
# Copy your certificates to ssl/ directory
cp your-certificate.crt ssl/cert.pem
cp your-private-key.key ssl/key.pem

# Set proper permissions
chmod 644 ssl/cert.pem
chmod 600 ssl/key.pem
```

#### **Option B: Let's Encrypt (Recommended)**
```bash
# Install certbot
sudo apt install certbot

# Stop any running web server
sudo systemctl stop apache2 nginx 2>/dev/null || true

# Generate certificate
sudo certbot certonly --standalone -d your-domain.com

# Copy certificates
sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem ssl/cert.pem
sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem ssl/key.pem

# Set permissions
sudo chmod 644 ssl/cert.pem
sudo chmod 600 ssl/key.pem
```

#### **Option C: Self-Signed (Development/Testing)**
```bash
# The deployment script will automatically generate self-signed certificates
# No action needed - certificates will be created during deployment
```

### **4. Environment Configuration**
```bash
# Copy environment template
cp .env.production .env

# Edit configuration (IMPORTANT!)
nano .env
```

**Update these critical settings in `.env`:**
```bash
# Your domain
APP_URL=https://your-domain.com

# Secure database passwords
DB_PASSWORD=your-very-secure-database-password
DB_ROOT_PASSWORD=your-very-secure-root-password

# Secure Redis password
REDIS_PASSWORD=your-very-secure-redis-password

# Secure Grafana password
GRAFANA_PASSWORD=your-secure-grafana-password
```

### **5. Deploy Application**
```bash
# Run deployment script
./deploy.sh
```

**OR manually:**
```bash
# Build and start containers
docker-compose -f docker-compose.prod.yml up -d --build

# Wait for services to be ready
sleep 30

# Check status
docker-compose -f docker-compose.prod.yml ps
```

---

## 🔐 **Security Configuration**

### **Firewall Setup**
```bash
# Install UFW
sudo apt install ufw

# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH
sudo ufw allow ssh

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow monitoring (optional)
sudo ufw allow 3000/tcp  # Grafana
sudo ufw allow 9090/tcp  # Prometheus

# Enable firewall
sudo ufw enable
```

### **SSL Security Headers**
The application automatically includes:
- ✅ **HSTS** (HTTP Strict Transport Security)
- ✅ **CSP** (Content Security Policy)
- ✅ **X-Frame-Options**
- ✅ **X-Content-Type-Options**
- ✅ **X-XSS-Protection**

### **Rate Limiting**
Configured automatically:
- ✅ **Login attempts**: 5 per minute
- ✅ **API requests**: 10 per second
- ✅ **General requests**: 1 per second

---

## 📊 **Application Access**

### **Main Application**
- **URL**: `https://your-domain.com`
- **HTTP**: Automatically redirects to HTTPS

### **Default Login Credentials**
| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **👑 Admin** | admin@testflow.com | admin123 | Full system control |
| **📊 Manager** | manager@testflow.com | manager123 | Project oversight |
| **👨‍💻 Developer** | developer@testflow.com | dev123 | Code integration |
| **🧪 Tester** | tester@testflow.com | test123 | Test execution |

### **Monitoring Dashboards**
- **Grafana**: `http://your-domain.com:3000` (admin/your-grafana-password)
- **Prometheus**: `http://your-domain.com:9090`

---

## 🗄️ **Database Information**

### **MySQL Configuration**
- **Engine**: MySQL 8.0
- **Database**: testflow_pro
- **User**: testflow_user
- **Host**: mysql (internal)
- **Port**: 3306

### **Backup System**
- **Frequency**: Daily at 2:00 AM
- **Retention**: 30 days (configurable)
- **Location**: `./backups/`
- **Format**: Compressed SQL dumps

### **Manual Backup**
```bash
# Create immediate backup
docker-compose -f docker-compose.prod.yml exec backup /backup.sh

# List backups
ls -la backups/

# Restore from backup
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p testflow_pro < backups/backup_file.sql
```

---

## 🔧 **Management Commands**

### **Service Management**
```bash
# View all services status
docker-compose -f docker-compose.prod.yml ps

# View logs (all services)
docker-compose -f docker-compose.prod.yml logs -f

# View logs (specific service)
docker-compose -f docker-compose.prod.yml logs -f app
docker-compose -f docker-compose.prod.yml logs -f mysql
docker-compose -f docker-compose.prod.yml logs -f nginx

# Restart services
docker-compose -f docker-compose.prod.yml restart

# Stop services
docker-compose -f docker-compose.prod.yml down

# Update and redeploy
git pull
./deploy.sh
```

### **Database Management**
```bash
# Access MySQL shell
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p testflow_pro

# View database size
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema='testflow_pro';"

# Optimize database
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p -e "OPTIMIZE TABLE testflow_pro.*;"
```

### **Application Management**
```bash
# Clear application cache
docker-compose -f docker-compose.prod.yml exec app rm -rf /var/www/html/storage/cache/*

# View application logs
tail -f logs/php_errors.log

# Check disk usage
df -h
du -sh backups/
du -sh logs/
```

---

## 📈 **Performance Optimization**

### **Database Optimization**
```bash
# Edit MySQL configuration
nano docker/mysql/my.cnf

# Restart MySQL
docker-compose -f docker-compose.prod.yml restart mysql
```

### **Application Optimization**
```bash
# Edit PHP configuration
nano docker/php/php-prod.ini

# Rebuild application
docker-compose -f docker-compose.prod.yml up -d --build app
```

### **Nginx Optimization**
```bash
# Edit Nginx configuration
nano docker/nginx/default.conf

# Restart Nginx
docker-compose -f docker-compose.prod.yml restart nginx
```

---

## 🔍 **Troubleshooting**

### **Common Issues**

#### **1. SSL Certificate Issues**
```bash
# Check certificate validity
openssl x509 -in ssl/cert.pem -text -noout -dates

# Test SSL connection
openssl s_client -connect your-domain.com:443 -servername your-domain.com
```

#### **2. Database Connection Issues**
```bash
# Check MySQL status
docker-compose -f docker-compose.prod.yml exec mysql mysqladmin ping -h localhost

# Check MySQL logs
docker-compose -f docker-compose.prod.yml logs mysql

# Test database connection
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p -e "SELECT 1;"
```

#### **3. Application Not Loading**
```bash
# Check all services
docker-compose -f docker-compose.prod.yml ps

# Check application logs
docker-compose -f docker-compose.prod.yml logs app

# Check Nginx logs
docker-compose -f docker-compose.prod.yml logs nginx

# Test internal connectivity
docker-compose -f docker-compose.prod.yml exec nginx curl -I http://app:9000/health.php
```

#### **4. Permission Issues**
```bash
# Fix storage permissions
chmod -R 775 storage public/attachments logs backups

# Fix ownership (if needed)
sudo chown -R www-data:www-data storage public/attachments
```

### **Health Checks**
```bash
# Application health
curl -k https://your-domain.com/health

# Database health
docker-compose -f docker-compose.prod.yml exec mysql mysqladmin ping

# Redis health
docker-compose -f docker-compose.prod.yml exec redis redis-cli ping
```

---

## 🔄 **Updates and Maintenance**

### **Application Updates**
```bash
# Pull latest changes
git pull origin main

# Redeploy
./deploy.sh

# Or manually
docker-compose -f docker-compose.prod.yml up -d --build
```

### **Security Updates**
```bash
# Update base images
docker-compose -f docker-compose.prod.yml pull

# Rebuild with latest images
docker-compose -f docker-compose.prod.yml up -d --build

# Update SSL certificates (Let's Encrypt)
sudo certbot renew
sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem ssl/cert.pem
sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem ssl/key.pem
docker-compose -f docker-compose.prod.yml restart nginx
```

### **Database Maintenance**
```bash
# Optimize database tables
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p -e "OPTIMIZE TABLE testflow_pro.*;"

# Analyze tables
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p -e "ANALYZE TABLE testflow_pro.*;"

# Check table integrity
docker-compose -f docker-compose.prod.yml exec mysql mysql -u testflow_user -p -e "CHECK TABLE testflow_pro.*;"
```

---

## 📞 **Support and Monitoring**

### **Log Locations**
- **Application**: `logs/php_errors.log`
- **Nginx**: `logs/nginx/access.log`, `logs/nginx/error.log`
- **MySQL**: Docker logs
- **Backup**: `logs/backup.log`

### **Monitoring Setup**
1. **Grafana Dashboard**: `http://your-domain.com:3000`
2. **Prometheus Metrics**: `http://your-domain.com:9090`
3. **Application Health**: `https://your-domain.com/health`

### **Alerting (Optional)**
Configure Grafana alerts for:
- High CPU usage
- High memory usage
- Database connection issues
- Failed backups
- SSL certificate expiration

---

## 🎉 **Deployment Complete!**

Your TestFlow Pro application is now:

- ✅ **Production Ready** - Optimized for performance and security
- ✅ **SSL Secured** - HTTPS with proper security headers
- ✅ **Database Optimized** - MySQL with automated backups
- ✅ **Monitoring Enabled** - Grafana and Prometheus ready
- ✅ **Scalable Architecture** - Docker-based for easy scaling
- ✅ **Security Hardened** - Rate limiting, CSRF protection, input validation

**Your application is ready to handle production workloads!** 🚀

---

*TestFlow Pro - Complete Test Case Management Solution* ✨
