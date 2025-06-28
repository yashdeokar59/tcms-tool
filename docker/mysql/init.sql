-- TestFlow Pro Database Schema for MySQL
-- Production-ready database initialization

SET FOREIGN_KEY_CHECKS = 0;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'developer', 'tester') NOT NULL DEFAULT 'tester',
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'planning', 'on-hold', 'completed', 'archived') DEFAULT 'active',
    start_date DATE NULL,
    end_date DATE NULL,
    created_by INT NOT NULL,
    manager_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_active (is_active),
    INDEX idx_manager (manager_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modules table
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    project_id INT NOT NULL,
    parent_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES modules(id) ON DELETE SET NULL,
    INDEX idx_project (project_id),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Requirements table
CREATE TABLE IF NOT EXISTS requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('functional', 'non-functional', 'business', 'technical') DEFAULT 'functional',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('draft', 'approved', 'implemented', 'tested', 'closed') DEFAULT 'draft',
    project_id INT NOT NULL,
    module_id INT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_project (project_id),
    INDEX idx_module (module_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test Suites table
CREATE TABLE IF NOT EXISTS test_suites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    project_id INT NOT NULL,
    parent_id INT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES test_suites(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_project (project_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test Environments table
CREATE TABLE IF NOT EXISTS test_environments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    url VARCHAR(500) NULL,
    project_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test Cases table
CREATE TABLE IF NOT EXISTS test_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    project_id INT NOT NULL,
    test_suite_id INT NULL,
    module_id INT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    type ENUM('functional', 'integration', 'system', 'acceptance', 'performance', 'security') DEFAULT 'functional',
    complexity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('draft', 'review', 'approved', 'active', 'deprecated') DEFAULT 'draft',
    preconditions TEXT,
    test_steps TEXT,
    expected_result TEXT,
    postconditions TEXT,
    test_data TEXT,
    assigned_to INT NULL,
    created_by INT NOT NULL,
    estimated_time INT NULL COMMENT 'Estimated time in minutes',
    tags TEXT,
    parent_id INT NULL COMMENT 'For cloned test cases',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (test_suite_id) REFERENCES test_suites(id) ON DELETE SET NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (parent_id) REFERENCES test_cases(id) ON DELETE SET NULL,
    INDEX idx_project (project_id),
    INDEX idx_suite (test_suite_id),
    INDEX idx_module (module_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_type (type),
    FULLTEXT idx_search (title, description, test_steps)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test Cycles table
CREATE TABLE IF NOT EXISTS test_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    project_id INT NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    status ENUM('planning', 'active', 'completed', 'cancelled') DEFAULT 'planning',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_project (project_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test Runs table
CREATE TABLE IF NOT EXISTS test_runs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    test_cycle_id INT NOT NULL,
    environment_id INT NULL,
    assigned_to INT NULL,
    status ENUM('planned', 'in-progress', 'completed', 'cancelled') DEFAULT 'planned',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (test_cycle_id) REFERENCES test_cycles(id) ON DELETE CASCADE,
    FOREIGN KEY (environment_id) REFERENCES test_environments(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_cycle (test_cycle_id),
    INDEX idx_environment (environment_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test Executions table
CREATE TABLE IF NOT EXISTS test_executions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_case_id INT NOT NULL,
    test_run_id INT NULL,
    status ENUM('passed', 'failed', 'blocked', 'skipped') NOT NULL,
    actual_result TEXT,
    comments TEXT,
    executed_by INT NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    execution_time INT NULL COMMENT 'Execution time in minutes',
    environment_id INT NULL,
    browser VARCHAR(100) NULL,
    os VARCHAR(100) NULL,
    build_version VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (test_case_id) REFERENCES test_cases(id) ON DELETE CASCADE,
    FOREIGN KEY (test_run_id) REFERENCES test_runs(id) ON DELETE SET NULL,
    FOREIGN KEY (executed_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (environment_id) REFERENCES test_environments(id) ON DELETE SET NULL,
    INDEX idx_test_case (test_case_id),
    INDEX idx_test_run (test_run_id),
    INDEX idx_executed_by (executed_by),
    INDEX idx_status (status),
    INDEX idx_executed_at (executed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Defects table
CREATE TABLE IF NOT EXISTS defects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('open', 'in-progress', 'resolved', 'closed', 'rejected') DEFAULT 'open',
    project_id INT NOT NULL,
    module_id INT NULL,
    test_case_id INT NULL,
    test_execution_id INT NULL,
    reported_by INT NOT NULL,
    assigned_to INT NULL,
    steps_to_reproduce TEXT,
    expected_behavior TEXT,
    actual_behavior TEXT,
    environment VARCHAR(255) NULL,
    browser VARCHAR(100) NULL,
    os VARCHAR(100) NULL,
    resolution TEXT NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL,
    FOREIGN KEY (test_case_id) REFERENCES test_cases(id) ON DELETE SET NULL,
    FOREIGN KEY (test_execution_id) REFERENCES test_executions(id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_project (project_id),
    INDEX idx_module (module_id),
    INDEX idx_test_case (test_case_id),
    INDEX idx_reported_by (reported_by),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_priority (priority),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attachments table
CREATE TABLE IF NOT EXISTS attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    attachable_type ENUM('test_case', 'defect', 'test_execution', 'requirement') NOT NULL,
    attachable_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_attachable (attachable_type, attachable_id),
    INDEX idx_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    commentable_type ENUM('test_case', 'defect', 'test_execution', 'test_run') NOT NULL,
    commentable_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_commentable (commentable_type, commentable_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    user_id INT NOT NULL,
    related_type VARCHAR(50) NULL,
    related_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project Users (Many-to-Many relationship)
CREATE TABLE IF NOT EXISTS project_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member', 'lead', 'viewer') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_user (project_id, user_id),
    INDEX idx_project (project_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Requirement Test Cases (Many-to-Many relationship)
CREATE TABLE IF NOT EXISTS requirement_test_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requirement_id INT NOT NULL,
    test_case_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE,
    FOREIGN KEY (test_case_id) REFERENCES test_cases(id) ON DELETE CASCADE,
    UNIQUE KEY unique_requirement_testcase (requirement_id, test_case_id),
    INDEX idx_requirement (requirement_id),
    INDEX idx_test_case (test_case_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Insert default admin user
INSERT INTO users (name, email, password, role, is_active) VALUES 
('System Administrator', 'admin@testflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE),
('Project Manager', 'manager@testflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', TRUE),
('Lead Developer', 'developer@testflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'developer', TRUE),
('QA Tester', 'tester@testflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tester', TRUE);

-- Insert sample project
INSERT INTO projects (name, description, status, created_by, manager_id) VALUES 
('TestFlow Demo Project', 'Sample project for demonstration purposes', 'active', 1, 2);

-- Insert sample modules
INSERT INTO modules (name, description, project_id) VALUES 
('User Management', 'User authentication and authorization module', 1),
('Test Management', 'Test case and execution management module', 1),
('Reporting', 'Analytics and reporting module', 1);

-- Insert sample test environment
INSERT INTO test_environments (name, description, url, project_id) VALUES 
('Production', 'Production environment', 'https://app.testflow.com', 1),
('Staging', 'Staging environment for testing', 'https://staging.testflow.com', 1);

-- Insert sample requirements
INSERT INTO requirements (title, description, type, priority, project_id, module_id, created_by) VALUES 
('User Login', 'Users should be able to login with email and password', 'functional', 'high', 1, 1, 1),
('Test Case Creation', 'Users should be able to create test cases', 'functional', 'high', 1, 2, 1),
('Test Execution', 'Users should be able to execute test cases', 'functional', 'high', 1, 2, 1);

-- Insert sample test suite
INSERT INTO test_suites (name, description, project_id, created_by) VALUES 
('Smoke Tests', 'Basic functionality smoke tests', 1, 1),
('Regression Tests', 'Full regression test suite', 1, 1);

-- Insert sample test cases
INSERT INTO test_cases (title, description, project_id, test_suite_id, module_id, priority, type, status, preconditions, test_steps, expected_result, assigned_to, created_by) VALUES 
('Login with valid credentials', 'Test user login functionality with valid email and password', 1, 1, 1, 'high', 'functional', 'approved', 'User account exists and is active', '1. Navigate to login page\n2. Enter valid email\n3. Enter valid password\n4. Click login button', 'User should be logged in successfully and redirected to dashboard', 4, 1),
('Create new test case', 'Test the ability to create a new test case', 1, 1, 2, 'medium', 'functional', 'approved', 'User is logged in with tester role', '1. Navigate to test cases page\n2. Click "Create Test Case" button\n3. Fill in required fields\n4. Click save', 'Test case should be created and visible in the list', 4, 1),
('Execute test case', 'Test the ability to execute a test case and record results', 1, 1, 2, 'medium', 'functional', 'approved', 'Test case exists and user has execution permissions', '1. Navigate to test case\n2. Click execute button\n3. Select status\n4. Add comments\n5. Save execution', 'Test execution should be recorded with proper status', 4, 1);

-- Link requirements to test cases
INSERT INTO requirement_test_cases (requirement_id, test_case_id) VALUES 
(1, 1),
(2, 2),
(3, 3);

-- Assign users to project
INSERT INTO project_users (project_id, user_id, role) VALUES 
(1, 1, 'lead'),
(1, 2, 'lead'),
(1, 3, 'member'),
(1, 4, 'member');
