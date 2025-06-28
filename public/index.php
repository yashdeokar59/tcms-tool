<?php
require_once 'auth.php';
requireAuth();

// Database connection
$dbPath = '/var/www/html/testcase-management-tool/database/database.sqlite';
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Database connection failed");
}

$user = getCurrentUser();
$currentRole = $user['role'];

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_stats':
            $stats = [
                'total_projects' => $pdo->query("SELECT COUNT(*) FROM projects WHERE is_active = 1")->fetchColumn(),
                'total_test_cases' => $pdo->query("SELECT COUNT(*) FROM test_cases")->fetchColumn(),
                'total_executions' => $pdo->query("SELECT COUNT(*) FROM test_executions")->fetchColumn(),
                'total_defects' => $pdo->query("SELECT COUNT(*) FROM defects")->fetchColumn(),
                'my_test_cases' => $pdo->query("SELECT COUNT(*) FROM test_cases WHERE assigned_to = " . $user['id'])->fetchColumn(),
                'my_executions' => $pdo->query("SELECT COUNT(*) FROM test_executions WHERE executed_by = " . $user['id'])->fetchColumn(),
            ];
            echo json_encode($stats);
            exit;
            
        case 'get_test_cases':
            $query = "
                SELECT tc.*, p.name as project_name, u.name as assignee_name,
                       creator.name as creator_name,
                       (SELECT COUNT(*) FROM test_executions WHERE test_case_id = tc.id) as execution_count,
                       (SELECT status FROM test_executions WHERE test_case_id = tc.id ORDER BY executed_at DESC LIMIT 1) as last_status
                FROM test_cases tc
                LEFT JOIN projects p ON tc.project_id = p.id
                LEFT JOIN users u ON tc.assigned_to = u.id
                LEFT JOIN users creator ON tc.created_by = creator.id
                ORDER BY tc.created_at DESC
                LIMIT 50
            ";
            $testCases = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($testCases);
            exit;
            
        case 'get_projects':
            $query = "
                SELECT p.*, u.name as manager_name, creator.name as creator_name,
                       (SELECT COUNT(*) FROM test_cases WHERE project_id = p.id) as test_case_count
                FROM projects p
                LEFT JOIN users u ON p.manager_id = u.id
                LEFT JOIN users creator ON p.created_by = creator.id
                WHERE p.is_active = 1
                ORDER BY p.created_at DESC
            ";
            $projects = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($projects);
            exit;
            
        case 'get_defects':
            $query = "
                SELECT d.*, p.name as project_name, reporter.name as reporter_name,
                       assignee.name as assignee_name, tc.title as test_case_title
                FROM defects d
                LEFT JOIN projects p ON d.project_id = p.id
                LEFT JOIN users reporter ON d.reported_by = reporter.id
                LEFT JOIN users assignee ON d.assigned_to = assignee.id
                LEFT JOIN test_cases tc ON d.test_case_id = tc.id
                ORDER BY d.created_at DESC
                LIMIT 50
            ";
            $defects = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($defects);
            exit;
            
        case 'get_users':
            requireRole(['admin']);
            $users = $pdo->query("SELECT id, name, email, role, is_active, last_login_at, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
            exit;
            
        case 'create_test_case':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                if (empty($data['title']) || empty($data['description']) || empty($data['project_id'])) {
                    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                    exit;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO test_cases (title, description, project_id, priority, type, complexity, 
                                          preconditions, test_steps, expected_result, postconditions, 
                                          test_data, assigned_to, estimated_time, tags, status, created_by, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, datetime('now'), datetime('now'))
                ");
                
                $result = $stmt->execute([
                    $data['title'],
                    $data['description'],
                    $data['project_id'],
                    $data['priority'] ?? 'medium',
                    $data['type'] ?? 'functional',
                    $data['complexity'] ?? 'medium',
                    $data['preconditions'] ?? '',
                    $data['test_steps'] ?? '',
                    $data['expected_result'] ?? '',
                    $data['postconditions'] ?? '',
                    $data['test_data'] ?? '',
                    $data['assigned_to'] ?? null,
                    $data['estimated_time'] ?? null,
                    $data['tags'] ?? '',
                    $user['id']
                ]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to create test case']);
                }
            }
            exit;
            
        case 'execute_test':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("
                    INSERT INTO test_executions (test_case_id, status, actual_result, comments, 
                                               executed_by, executed_at, execution_time, browser, os, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, datetime('now'), ?, ?, ?, datetime('now'), datetime('now'))
                ");
                
                $result = $stmt->execute([
                    $data['test_case_id'],
                    $data['status'],
                    $data['actual_result'] ?? '',
                    $data['comments'] ?? '',
                    $user['id'],
                    $data['execution_time'] ?? null,
                    $data['browser'] ?? '',
                    $data['os'] ?? ''
                ]);
                
                // Auto-create defect for failed tests
                if ($data['status'] === 'failed') {
                    $testCase = $pdo->query("SELECT * FROM test_cases WHERE id = " . $data['test_case_id'])->fetch();
                    if ($testCase) {
                        $stmt = $pdo->prepare("
                            INSERT INTO defects (title, description, severity, priority, status, project_id, 
                                               test_case_id, test_execution_id, reported_by, steps_to_reproduce, 
                                               expected_behavior, actual_behavior, created_at, updated_at)
                            VALUES (?, ?, 'medium', 'medium', 'open', ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                        ");
                        
                        $stmt->execute([
                            'Test Failure: ' . $testCase['title'],
                            'Automated defect created from failed test execution',
                            $testCase['project_id'],
                            $data['test_case_id'],
                            $pdo->lastInsertId(),
                            $user['id'],
                            $testCase['test_steps'],
                            $testCase['expected_result'],
                            $data['actual_result']
                        ]);
                    }
                }
                
                echo json_encode(['success' => $result]);
            }
            exit;
            
        case 'create_project':
            requireRole(['admin', 'manager']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("
                    INSERT INTO projects (name, description, status, start_date, end_date, 
                                        created_by, manager_id, is_active, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, datetime('now'), datetime('now'))
                ");
                
                $result = $stmt->execute([
                    $data['name'],
                    $data['description'],
                    $data['status'] ?? 'active',
                    $data['start_date'] ?? null,
                    $data['end_date'] ?? null,
                    $user['id'],
                    $data['manager_id'] ?? null
                ]);
                
                echo json_encode(['success' => $result, 'id' => $pdo->lastInsertId()]);
            }
            exit;
            
        case 'create_user':
            requireRole(['admin']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'error' => 'Email already exists']);
                    exit;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, is_active, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 1, datetime('now'), datetime('now'))
                ");
                
                $result = $stmt->execute([
                    $data['name'],
                    $data['email'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $data['role']
                ]);
                
                echo json_encode(['success' => $result, 'id' => $pdo->lastInsertId()]);
            }
            exit;
            
        case 'update_user_role':
            requireRole(['admin']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("UPDATE users SET role = ?, updated_at = datetime('now') WHERE id = ?");
                $result = $stmt->execute([$data['role'], $data['user_id']]);
                
                echo json_encode(['success' => $result]);
            }
            exit;
            
        case 'delete_user':
            requireRole(['admin']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Don't allow deleting self
                if ($data['user_id'] == $user['id']) {
                    echo json_encode(['success' => false, 'error' => 'Cannot delete yourself']);
                    exit;
                }
                
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0, updated_at = datetime('now') WHERE id = ?");
                $result = $stmt->execute([$data['user_id']]);
                
                echo json_encode(['success' => $result]);
            }
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TestFlow Pro - <?= ucfirst($currentRole) ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .navbar { 
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .sidebar { 
            background: white; 
            min-height: calc(100vh - 76px); 
            box-shadow: 2px 0 10px rgba(0,0,0,0.1); 
            border-radius: 0 15px 15px 0;
        }
        .sidebar .nav-link { 
            color: #333; 
            padding: 12px 20px; 
            margin: 5px 10px; 
            border-radius: 10px; 
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
            color: white; 
            transform: translateX(5px);
        }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); 
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }
        .stat-card { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
            color: white; 
            border-radius: 15px; 
            padding: 25px; 
            text-align: center; 
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: scale(1.05); }
        .stat-number { font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; }
        .btn-primary { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
            border: none; 
            border-radius: 10px; 
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .table thead th { 
            background: var(--primary-color); 
            color: white; 
            border: none;
        }
        .badge { border-radius: 20px; padding: 8px 15px; }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .modal-content { border-radius: 15px; }
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .alert { border-radius: 10px; border: none; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-flask me-2"></i>TestFlow Pro
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($user['name']) ?> (<?= ucfirst($currentRole) ?>)
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="showSection('profile')">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?logout=1">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <nav class="nav flex-column py-3">
                        <a class="nav-link active" href="#" onclick="showSection('dashboard')">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="#" onclick="showSection('test-cases')">
                            <i class="fas fa-list-check me-2"></i>Test Cases
                        </a>
                        <a class="nav-link" href="#" onclick="showSection('projects')">
                            <i class="fas fa-folder me-2"></i>Projects
                        </a>
                        <a class="nav-link" href="#" onclick="showSection('defects')">
                            <i class="fas fa-bug me-2"></i>Defects
                        </a>
                        
                        <?php if (hasAnyRole(['admin', 'manager'])): ?>
                        <a class="nav-link" href="#" onclick="showSection('reports')">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasRole('admin')): ?>
                        <hr class="my-2">
                        <a class="nav-link" href="#" onclick="showSection('users')">
                            <i class="fas fa-users me-2"></i>User Management
                        </a>
                        <a class="nav-link" href="#" onclick="showSection('admin')">
                            <i class="fas fa-cogs me-2"></i>System Settings
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Dashboard Section -->
                    <div id="dashboard-section" class="content-section">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">
                                <i class="fas fa-tachometer-alt text-primary me-2"></i><?= ucfirst($currentRole) ?> Dashboard
                            </h1>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4" id="stats-cards">
                            <!-- Stats will be loaded here -->
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-primary w-100" onclick="showCreateTestCaseModal()">
                                            <i class="fas fa-plus-circle fa-2x mb-2"></i><br>Create Test Case
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-success w-100" onclick="showSection('test-cases')">
                                            <i class="fas fa-play-circle fa-2x mb-2"></i><br>Execute Tests
                                        </button>
                                    </div>
                                    <?php if (hasAnyRole(['admin', 'manager'])): ?>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-info w-100" onclick="showCreateProjectModal()">
                                            <i class="fas fa-folder-plus fa-2x mb-2"></i><br>Create Project
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-danger w-100" onclick="showSection('defects')">
                                            <i class="fas fa-bug fa-2x mb-2"></i><br>View Defects
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Cases Section -->
                    <div id="test-cases-section" class="content-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">
                                <i class="fas fa-list-check text-primary me-2"></i>Test Cases
                            </h1>
                            <button class="btn btn-primary" onclick="showCreateTestCaseModal()">
                                <i class="fas fa-plus me-1"></i>Create Test Case
                            </button>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="test-cases-table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Project</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Assigned To</th>
                                                <th>Last Execution</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Test cases will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Section -->
                    <div id="projects-section" class="content-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">
                                <i class="fas fa-folder text-primary me-2"></i>Projects
                            </h1>
                            <?php if (hasAnyRole(['admin', 'manager'])): ?>
                            <button class="btn btn-primary" onclick="showCreateProjectModal()">
                                <i class="fas fa-plus me-1"></i>Create Project
                            </button>
                            <?php endif; ?>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="projects-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Manager</th>
                                                <th>Test Cases</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Projects will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Defects Section -->
                    <div id="defects-section" class="content-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">
                                <i class="fas fa-bug text-primary me-2"></i>Defects
                            </h1>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="defects-table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Project</th>
                                                <th>Severity</th>
                                                <th>Status</th>
                                                <th>Reporter</th>
                                                <th>Assigned To</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Defects will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Management Section (Admin Only) -->
                    <?php if (hasRole('admin')): ?>
                    <div id="users-section" class="content-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">
                                <i class="fas fa-users text-primary me-2"></i>User Management
                            </h1>
                            <button class="btn btn-primary" onclick="showCreateUserModal()">
                                <i class="fas fa-user-plus me-1"></i>Create User
                            </button>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Last Login</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Users will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Other sections would be implemented similarly -->
                    <div id="reports-section" class="content-section" style="display: none;">
                        <h1 class="h3 mb-4"><i class="fas fa-chart-bar text-primary me-2"></i>Reports & Analytics</h1>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Comprehensive reporting functionality is available for managers and admins.
                        </div>
                    </div>

                    <div id="admin-section" class="content-section" style="display: none;">
                        <h1 class="h3 mb-4"><i class="fas fa-cogs text-primary me-2"></i>System Settings</h1>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>System configuration and settings management.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>
</body>
</html>
