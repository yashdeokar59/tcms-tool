#!/bin/bash

echo "🚀 Starting TestFlow Pro - Advanced Test Case Management System"
echo "================================================================"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check if composer is available
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install --ignore-platform-req=php
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Create SQLite database for quick setup
echo "🗄️  Setting up database..."
touch database/database.sqlite

# Update .env for SQLite
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_DATABASE=testflow_pro/DB_DATABASE=database\/database.sqlite/' .env

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Seed the database
echo "🌱 Seeding database with sample data..."
php artisan db:seed --force

echo ""
echo "✅ TestFlow Pro is ready!"
echo ""
echo "🌐 Starting development server..."
echo "📍 Application will be available at: http://localhost:8000"
echo "👤 Default login: admin@testflowpro.com / password123"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start the development server
php artisan serve --host=0.0.0.0 --port=8000
