#!/bin/bash

<<<<<<< HEAD
# TestFlow Pro - Simple Deployment Script
# Single command deployment for cms-prod.baseel.com:9899

set -e

echo "🚀 Deploying TestFlow Pro - Simple Version"
echo "=========================================="

# Create uploads directory
mkdir -p uploads
chmod 777 uploads

# Stop any existing containers
docker-compose down 2>/dev/null || true

# Clean up Docker networks
docker network prune -f

# Start the application
echo "Starting containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "Waiting for database to be ready..."
sleep 30

# Check if containers are running
if docker-compose ps | grep -q "Up"; then
    echo "✅ Deployment successful!"
    echo ""
    echo "🌐 Access your application:"
    echo "   URL: http://cms-prod.baseel.com:9899"
    echo "   Local: http://localhost:9899"
    echo ""
    echo "🔐 Login credentials:"
    echo "   Admin: admin@testflow.com / password"
    echo "   Manager: manager@testflow.com / password"
    echo "   Developer: developer@testflow.com / password"
    echo "   Tester: tester@testflow.com / password"
    echo ""
    echo "🐳 Management commands:"
    echo "   View logs: docker-compose logs -f"
    echo "   Stop: docker-compose down"
    echo "   Restart: docker-compose restart"
    echo ""
else
    echo "❌ Deployment failed. Check logs:"
    docker-compose logs
fi
=======
# TestFlow Pro Production Deployment Script
# This script deploys the application using Docker Compose

set -e

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

print_header() {
    echo -e "${BLUE}"
    echo "========================================"
    echo "  TestFlow Pro - Production Deployment"
    echo "========================================"
    echo -e "${NC}"
}

# Check if Docker and Docker Compose are installed
check_dependencies() {
    print_info "Checking dependencies..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    print_status "Dependencies check passed"
}

# Create necessary directories
create_directories() {
    print_info "Creating necessary directories..."
    
    mkdir -p logs/nginx
    mkdir -p storage/{logs,cache,sessions,uploads}
    mkdir -p public/attachments
    mkdir -p backups
    mkdir -p ssl
    
    print_status "Directories created"
}

# Set proper permissions
set_permissions() {
    print_info "Setting proper permissions..."
    
    chmod -R 755 .
    chmod -R 775 storage public/attachments logs backups
    chmod +x deploy.sh
    
    # Set ownership if running as root
    if [ "$EUID" -eq 0 ]; then
        chown -R www-data:www-data storage public/attachments
    fi
    
    print_status "Permissions set"
}

# Generate SSL certificates if they don't exist
generate_ssl() {
    print_info "Checking SSL certificates..."
    
    if [ ! -f ssl/cert.pem ] || [ ! -f ssl/key.pem ]; then
        print_warning "SSL certificates not found. Generating self-signed certificates..."
        print_warning "⚠️  Please replace with your actual SSL certificates for production!"
        
        openssl req -x509 -newkey rsa:4096 -keyout ssl/key.pem -out ssl/cert.pem -days 365 -nodes \
            -subj "/C=US/ST=State/L=City/O=Organization/CN=testflow.local" 2>/dev/null || {
            print_error "Failed to generate SSL certificates"
            exit 1
        }
        
        chmod 600 ssl/key.pem
        chmod 644 ssl/cert.pem
        
        print_warning "Self-signed SSL certificates generated"
        print_warning "Replace ssl/cert.pem and ssl/key.pem with your actual certificates"
    else
        print_status "SSL certificates found"
    fi
}

# Copy environment file
setup_environment() {
    print_info "Setting up environment configuration..."
    
    if [ ! -f .env ]; then
        if [ -f .env.production ]; then
            cp .env.production .env
            print_status "Environment file created from .env.production"
        else
            print_error ".env.production file not found"
            exit 1
        fi
    else
        print_status "Environment file already exists"
    fi
}

# Build and start containers
deploy_containers() {
    print_info "Building and starting Docker containers..."
    
    # Stop existing containers
    docker-compose -f docker-compose.prod.yml down 2>/dev/null || true
    
    # Remove unused Docker resources
    docker system prune -f
    
    # Build and start containers
    docker-compose -f docker-compose.prod.yml up -d --build
    
    print_status "Containers started"
}

# Wait for services to be ready
wait_for_services() {
    print_info "Waiting for services to be ready..."
    
    # Wait for MySQL
    print_info "Waiting for MySQL to be ready..."
    timeout=60
    while ! docker-compose -f docker-compose.prod.yml exec -T mysql mysqladmin ping -h localhost --silent; do
        sleep 2
        timeout=$((timeout - 2))
        if [ $timeout -le 0 ]; then
            print_error "MySQL failed to start within 60 seconds"
            exit 1
        fi
    done
    
    # Wait for Redis
    print_info "Waiting for Redis to be ready..."
    timeout=30
    while ! docker-compose -f docker-compose.prod.yml exec -T redis redis-cli ping > /dev/null 2>&1; do
        sleep 2
        timeout=$((timeout - 2))
        if [ $timeout -le 0 ]; then
            print_error "Redis failed to start within 30 seconds"
            exit 1
        fi
    done
    
    # Wait for application
    print_info "Waiting for application to be ready..."
    sleep 10
    
    print_status "All services are ready"
}

# Test deployment
test_deployment() {
    print_info "Testing deployment..."
    
    # Test HTTP redirect
    if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "301"; then
        print_status "HTTP to HTTPS redirect working"
    else
        print_warning "HTTP to HTTPS redirect may not be working"
    fi
    
    # Test HTTPS (skip certificate verification for self-signed)
    if curl -k -s -o /dev/null -w "%{http_code}" https://localhost | grep -q "200"; then
        print_status "HTTPS endpoint responding"
    else
        print_warning "HTTPS endpoint may not be responding correctly"
    fi
    
    # Test database connection
    if docker-compose -f docker-compose.prod.yml exec -T mysql mysql -u testflow_user -ptestflow_pass_2024 -e "SELECT 1" testflow_pro > /dev/null 2>&1; then
        print_status "Database connection working"
    else
        print_warning "Database connection may have issues"
    fi
    
    print_status "Deployment tests completed"
}

# Show deployment information
show_info() {
    echo ""
    print_header
    print_status "TestFlow Pro has been successfully deployed!"
    echo ""
    print_info "🌐 Application URLs:"
    echo "   • HTTPS (Primary): https://localhost"
    echo "   • HTTP (Redirects): http://localhost"
    echo ""
    print_info "🔐 Default Login Credentials:"
    echo "   • Admin: admin@testflow.com / admin123"
    echo "   • Manager: manager@testflow.com / manager123"
    echo "   • Developer: developer@testflow.com / dev123"
    echo "   • Tester: tester@testflow.com / test123"
    echo ""
    print_info "📊 Monitoring & Management:"
    echo "   • Grafana Dashboard: http://localhost:3000 (admin/admin123)"
    echo "   • Prometheus Metrics: http://localhost:9090"
    echo ""
    print_info "🐳 Docker Management Commands:"
    echo "   • View logs: docker-compose -f docker-compose.prod.yml logs -f"
    echo "   • Stop services: docker-compose -f docker-compose.prod.yml down"
    echo "   • Restart services: docker-compose -f docker-compose.prod.yml restart"
    echo "   • Update deployment: ./deploy.sh"
    echo ""
    print_info "📁 Important Directories:"
    echo "   • Application logs: ./logs/"
    echo "   • Database backups: ./backups/"
    echo "   • File uploads: ./public/attachments/"
    echo "   • SSL certificates: ./ssl/"
    echo ""
    print_warning "🔒 Security Reminders:"
    echo "   • Replace self-signed SSL certificates with real ones"
    echo "   • Change default passwords in .env file"
    echo "   • Configure firewall rules"
    echo "   • Set up regular backups"
    echo "   • Monitor application logs"
    echo ""
    print_status "🎉 Your TestFlow Pro application is ready for production use!"
    echo ""
}

# Main deployment function
main() {
    print_header
    
    check_dependencies
    create_directories
    set_permissions
    generate_ssl
    setup_environment
    deploy_containers
    wait_for_services
    test_deployment
    show_info
}

# Run main function
main "$@"
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19
