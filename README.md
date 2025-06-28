# TestFlow Pro - Production-Ready Test Case Management System

A complete, secure, and scalable test case management application built with PHP, MySQL, and Docker.

## 🚀 Quick Deployment

### Prerequisites
- Docker & Docker Compose
- SSL certificates (optional - self-signed will be generated)

### One-Command Deployment
```bash
chmod +x deploy.sh
./deploy.sh
```

## 🔧 Manual Deployment Steps

### 1. Clone and Setup
```bash
git clone <your-repo>
cd testcase-management-tool
```

### 2. Configure Environment
```bash
cp .env.production .env
# Edit .env with your settings
```

### 3. Deploy with Docker
```bash
docker-compose -f docker-compose.prod.yml up -d --build
```

### 4. Access Application
- **HTTPS**: https://your-domain.com
- **HTTP**: http://your-domain.com (redirects to HTTPS)

## 🔐 Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@testflow.com | admin123 |
| Manager | manager@testflow.com | manager123 |
| Developer | developer@testflow.com | dev123 |
| Tester | tester@testflow.com | test123 |

## 📊 Monitoring

- **Grafana**: http://your-domain.com:3000 (admin/admin123)
- **Prometheus**: http://your-domain.com:9090

## 🛡️ Security Features

- SSL/TLS encryption
- Rate limiting
- CSRF protection
- SQL injection prevention
- XSS protection
- Role-based access control
- Secure session management

## 🗄️ Database

- **Engine**: MySQL 8.0
- **Backup**: Automated daily backups
- **Retention**: 30 days (configurable)

## 📁 Directory Structure

```
├── docker/                 # Docker configurations
├── public/                 # Web root
├── storage/                # Application storage
├── logs/                   # Application logs
├── backups/                # Database backups
├── ssl/                    # SSL certificates
└── docker-compose.prod.yml # Production deployment
```

## 🔧 Management Commands

```bash
# View logs
docker-compose -f docker-compose.prod.yml logs -f

# Restart services
docker-compose -f docker-compose.prod.yml restart

# Stop services
docker-compose -f docker-compose.prod.yml down

# Update deployment
./deploy.sh

# Database backup
docker-compose -f docker-compose.prod.yml exec backup /backup.sh
```

## 🎯 Features

### ✅ Complete Test Management
- Test case creation, editing, deletion
- Test execution with status tracking
- Requirements traceability
- Defect management with auto-creation
- File attachments and comments

### ✅ Role-Based Access
- **Admin**: Full system control
- **Manager**: Project oversight and reporting
- **Developer**: Code integration and bug management
- **Tester**: Test execution and case management

### ✅ Production Ready
- Docker containerization
- SSL/HTTPS support
- Database clustering ready
- Horizontal scaling support
- Monitoring and alerting
- Automated backups

## 🔒 SSL Configuration

### Using Your Own Certificates
1. Place your certificates in the `ssl/` directory:
   - `ssl/cert.pem` - Your SSL certificate
   - `ssl/key.pem` - Your private key

2. Restart the deployment:
   ```bash
   docker-compose -f docker-compose.prod.yml restart nginx
   ```

### Let's Encrypt (Recommended)
```bash
# Install certbot
sudo apt install certbot

# Generate certificate
sudo certbot certonly --standalone -d your-domain.com

# Copy certificates
sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem ssl/cert.pem
sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem ssl/key.pem

# Set permissions
sudo chmod 644 ssl/cert.pem
sudo chmod 600 ssl/key.pem

# Restart nginx
docker-compose -f docker-compose.prod.yml restart nginx
```

## 🔧 Environment Variables

Key environment variables in `.env`:

```bash
# Database
DB_HOST=mysql
DB_DATABASE=testflow_pro
DB_USERNAME=testflow_user
DB_PASSWORD=your-secure-password

# Redis
REDIS_PASSWORD=your-redis-password

# Application
APP_URL=https://your-domain.com
APP_KEY=your-app-key

# Security
JWT_SECRET=your-jwt-secret
```

## 📈 Performance Optimization

### Database Optimization
- MySQL 8.0 with optimized configuration
- Connection pooling
- Query optimization
- Proper indexing

### Application Optimization
- PHP OPcache enabled
- Redis caching
- Gzip compression
- Static file caching

### Infrastructure Optimization
- Nginx reverse proxy
- SSL termination
- Rate limiting
- Health checks

## 🔍 Troubleshooting

### Common Issues

1. **SSL Certificate Issues**
   ```bash
   # Check certificate validity
   openssl x509 -in ssl/cert.pem -text -noout
   ```

2. **Database Connection Issues**
   ```bash
   # Check MySQL status
   docker-compose -f docker-compose.prod.yml exec mysql mysqladmin ping
   ```

3. **Permission Issues**
   ```bash
   # Fix permissions
   chmod -R 775 storage public/attachments logs
   ```

### Logs Location
- **Application**: `logs/`
- **Nginx**: `logs/nginx/`
- **MySQL**: Docker logs
- **PHP**: `logs/php_errors.log`

## 🚀 Scaling

### Horizontal Scaling
1. Use external MySQL cluster
2. Use external Redis cluster
3. Deploy multiple app containers
4. Use load balancer

### Vertical Scaling
1. Increase container resources
2. Optimize database configuration
3. Tune PHP-FPM settings

## 📞 Support

For issues and questions:
1. Check logs in `logs/` directory
2. Review Docker container status
3. Verify environment configuration
4. Check SSL certificate validity

## 📄 License

MIT License - see LICENSE file for details.

---

**TestFlow Pro** - Complete Test Case Management Solution 🧪
