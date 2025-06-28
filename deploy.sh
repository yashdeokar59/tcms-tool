#!/bin/bash

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
