#!/bin/bash

echo "🚀 Deploying Enhanced Test Case Management Tool"
echo "=============================================="

# Set proper permissions
echo "📁 Setting permissions..."
chmod -R 755 /var/www/html/testcase-management-tool
chmod -R 775 /var/www/html/testcase-management-tool/storage
chmod -R 775 /var/www/html/testcase-management-tool/bootstrap/cache

# Create storage directories
echo "📂 Creating storage directories..."
mkdir -p /var/www/html/testcase-management-tool/storage/app/public/attachments
mkdir -p /var/www/html/testcase-management-tool/storage/logs
mkdir -p /var/www/html/testcase-management-tool/storage/framework/cache
mkdir -p /var/www/html/testcase-management-tool/storage/framework/sessions
mkdir -p /var/www/html/testcase-management-tool/storage/framework/views

# Set up environment
echo "⚙️ Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "APP_KEY=" >> .env
    echo "DB_CONNECTION=sqlite" >> .env
    echo "DB_DATABASE=/var/www/html/testcase-management-tool/database/database.sqlite" >> .env
fi

# Create SQLite database
echo "🗄️ Creating database..."
touch /var/www/html/testcase-management-tool/database/database.sqlite

# Create a simple migration runner since artisan migrate is not working
echo "🔧 Setting up database schema..."
php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';

// Create database connection
\$pdo = new PDO('sqlite:/var/www/html/testcase-management-tool/database/database.sqlite');
\$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create users table
\$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT \"tester\",
    is_active BOOLEAN DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    preferences TEXT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');

// Create projects table
\$pdo->exec('CREATE TABLE IF NOT EXISTS projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(50) DEFAULT \"active\",
    start_date DATE NULL,
    end_date DATE NULL,
    created_by INTEGER NOT NULL,
    manager_id INTEGER NULL,
    settings TEXT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (manager_id) REFERENCES users(id)
)');

// Create test_suites table
\$pdo->exec('CREATE TABLE IF NOT EXISTS test_suites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    project_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id)
)');

// Create modules table
\$pdo->exec('CREATE TABLE IF NOT EXISTS modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    project_id INTEGER NOT NULL,
    parent_id INTEGER NULL,
    repository_url VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (parent_id) REFERENCES modules(id)
)');

// Create requirements table
\$pdo->exec('CREATE TABLE IF NOT EXISTS requirements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type VARCHAR(50) DEFAULT \"functional\",
    priority VARCHAR(50) DEFAULT \"medium\",
    status VARCHAR(50) DEFAULT \"draft\",
    project_id INTEGER NOT NULL,
    module_id INTEGER NULL,
    created_by INTEGER NOT NULL,
    assigned_to INTEGER NULL,
    acceptance_criteria TEXT NULL,
    business_value TEXT NULL,
    tags TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (module_id) REFERENCES modules(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
)');

// Create test_cases table
\$pdo->exec('CREATE TABLE IF NOT EXISTS test_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    preconditions TEXT NULL,
    test_steps TEXT NOT NULL,
    expected_result TEXT NOT NULL,
    postconditions TEXT NULL,
    priority VARCHAR(50) DEFAULT \"medium\",
    type VARCHAR(50) DEFAULT \"functional\",
    complexity VARCHAR(50) DEFAULT \"medium\",
    automation_status VARCHAR(50) DEFAULT \"manual\",
    estimated_time INTEGER NULL,
    status VARCHAR(50) DEFAULT \"draft\",
    test_suite_id INTEGER NULL,
    project_id INTEGER NOT NULL,
    module_id INTEGER NULL,
    created_by INTEGER NOT NULL,
    assigned_to INTEGER NULL,
    parent_id INTEGER NULL,
    tags TEXT NULL,
    test_data TEXT NULL,
    is_template BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_suite_id) REFERENCES test_suites(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (module_id) REFERENCES modules(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES test_cases(id)
)');

// Create test_environments table
\$pdo->exec('CREATE TABLE IF NOT EXISTS test_environments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    url VARCHAR(255) NULL,
    type VARCHAR(50) DEFAULT \"testing\",
    status VARCHAR(50) DEFAULT \"active\",
    project_id INTEGER NOT NULL,
    configuration TEXT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id)
)');

// Create test_cycles table
\$pdo->exec('CREATE TABLE IF NOT EXISTS test_cycles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    project_id INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT \"planned\",
    created_by INTEGER NOT NULL,
    assigned_to INTEGER NULL,
    build_version VARCHAR(255) NULL,
    environment_id INTEGER NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (environment_id) REFERENCES test_environments(id)
)');

// Create test_runs table
\$pdo->exec('CREATE TABLE IF NOT EXISTS test_runs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    project_id INTEGER NOT NULL,
    test_suite_id INTEGER NULL,
    cycle_id INTEGER NULL,
    environment_id INTEGER NULL,
    status VARCHAR(50) DEFAULT \"planned\",
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_by INTEGER NOT NULL,
    assigned_to INTEGER NULL,
    build_version VARCHAR(255) NULL,
    configuration TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (test_suite_id) REFERENCES test_suites(id),
    FOREIGN KEY (cycle_id) REFERENCES test_cycles(id),
    FOREIGN KEY (environment_id) REFERENCES test_environments(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
)');

// Create test_executions table
\$pdo->exec('CREATE TABLE IF NOT EXISTS test_executions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    test_run_id INTEGER NULL,
    test_case_id INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT \"pending\",
    actual_result TEXT NULL,
    comments TEXT NULL,
    executed_at TIMESTAMP NULL,
    executed_by INTEGER NULL,
    execution_time INTEGER NULL,
    browser VARCHAR(255) NULL,
    os VARCHAR(255) NULL,
    build_version VARCHAR(255) NULL,
    environment_data TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_run_id) REFERENCES test_runs(id),
    FOREIGN KEY (test_case_id) REFERENCES test_cases(id),
    FOREIGN KEY (executed_by) REFERENCES users(id)
)');

// Create defects table
\$pdo->exec('CREATE TABLE IF NOT EXISTS defects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    severity VARCHAR(50) DEFAULT \"medium\",
    priority VARCHAR(50) DEFAULT \"medium\",
    status VARCHAR(50) DEFAULT \"open\",
    project_id INTEGER NOT NULL,
    module_id INTEGER NULL,
    test_case_id INTEGER NULL,
    test_execution_id INTEGER NULL,
    reported_by INTEGER NOT NULL,
    assigned_to INTEGER NULL,
    verified_by INTEGER NULL,
    steps_to_reproduce TEXT NULL,
    expected_behavior TEXT NULL,
    actual_behavior TEXT NULL,
    environment TEXT NULL,
    browser VARCHAR(255) NULL,
    os VARCHAR(255) NULL,
    resolution TEXT NULL,
    resolved_at TIMESTAMP NULL,
    verified_at TIMESTAMP NULL,
    tags TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (module_id) REFERENCES modules(id),
    FOREIGN KEY (test_case_id) REFERENCES test_cases(id),
    FOREIGN KEY (test_execution_id) REFERENCES test_executions(id),
    FOREIGN KEY (reported_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
)');

// Create attachments table
\$pdo->exec('CREATE TABLE IF NOT EXISTS attachments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(255) NOT NULL,
    attachable_type VARCHAR(255) NOT NULL,
    attachable_id INTEGER NOT NULL,
    uploaded_by INTEGER NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
)');

// Create comments table
\$pdo->exec('CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content TEXT NOT NULL,
    commentable_type VARCHAR(255) NOT NULL,
    commentable_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    parent_id INTEGER NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES comments(id)
)');

// Create notifications table
\$pdo->exec('CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT \"info\",
    data TEXT NULL,
    read_at TIMESTAMP NULL,
    action_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)');

// Create project_users table
\$pdo->exec('CREATE TABLE IF NOT EXISTS project_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    role VARCHAR(50) DEFAULT \"tester\",
    permissions TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE(project_id, user_id)
)');

// Create requirement_test_cases table
\$pdo->exec('CREATE TABLE IF NOT EXISTS requirement_test_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    requirement_id INTEGER NOT NULL,
    test_case_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requirement_id) REFERENCES requirements(id),
    FOREIGN KEY (test_case_id) REFERENCES test_cases(id),
    UNIQUE(requirement_id, test_case_id)
)');

echo \"✅ Database schema created successfully!\n\";

// Create default admin user
\$hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
\$stmt = \$pdo->prepare('INSERT OR IGNORE INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)');
\$stmt->execute(['Admin User', 'admin@testflow.com', \$hashedPassword, 'admin', 1]);

echo \"👤 Default admin user created: admin@testflow.com / admin123\n\";

// Create sample project
\$stmt = \$pdo->prepare('INSERT OR IGNORE INTO projects (name, description, status, created_by, is_active) VALUES (?, ?, ?, ?, ?)');
\$stmt->execute(['Sample Project', 'A sample project to get started with TestFlow', 'active', 1, 1]);

echo \"📁 Sample project created!\n\";
"

echo "🎉 Enhanced Test Case Management Tool deployed successfully!"
echo ""
echo "🔗 Access the application:"
echo "   URL: http://localhost:8000 (or your server URL)"
echo "   Admin Login: admin@testflow.com"
echo "   Password: admin123"
echo ""
echo "🚀 To start the application:"
echo "   cd /var/www/html/testcase-management-tool"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "📚 Features available:"
echo "   ✅ Role-based access (Admin, Manager, Developer, Tester)"
echo "   ✅ Test case management with execution tracking"
echo "   ✅ Requirements traceability"
echo "   ✅ Defect management with auto-creation"
echo "   ✅ File attachments and comments"
echo "   ✅ Comprehensive reporting and dashboards"
echo "   ✅ Real-time notifications"
echo "   ✅ Team performance analytics"
echo ""
