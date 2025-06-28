<?php
require_once 'config.php';
requireAuth();

$user = getCurrentUser();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_stats':
            $stats = [
                'total_projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
                'total_test_cases' => $pdo->query("SELECT COUNT(*) FROM test_cases")->fetchColumn(),
                'total_executions' => $pdo->query("SELECT COUNT(*) FROM test_executions")->fetchColumn(),
                'total_defects' => $pdo->query("SELECT COUNT(*) FROM defects")->fetchColumn(),
            ];
            echo json_encode($stats);
            exit;
            
        case 'get_test_cases':
            $stmt = $pdo->query("
                SELECT tc.*, p.name as project_name, u.name as assignee_name
                FROM test_cases tc
                LEFT JOIN projects p ON tc.project_id = p.id
                LEFT JOIN users u ON tc.assigned_to = u.id
                ORDER BY tc.created_at DESC
                LIMIT 20
            ");
            echo json_encode($stmt->fetchAll());
            exit;
            
        case 'create_test_case':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $pdo->prepare("
                    INSERT INTO test_cases (title, description, project_id, priority, test_steps, expected_result, created_by, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $result = $stmt->execute([
                    $data['title'],
                    $data['description'],
                    $data['project_id'],
                    $data['priority'] ?? 'medium',
                    $data['test_steps'] ?? '',
                    $data['expected_result'] ?? '',
                    $user['id']
                ]);
                echo json_encode(['success' => $result, 'id' => $pdo->lastInsertId()]);
            }
            exit;
            
        case 'execute_test':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $pdo->prepare("
                    INSERT INTO test_executions (test_case_id, status, actual_result, comments, executed_by, executed_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute([
                    $data['test_case_id'],
                    $data['status'],
                    $data['actual_result'] ?? '',
                    $data['comments'] ?? '',
                    $user['id']
                ]);
                
                // Auto-create defect for failed tests
                if ($data['status'] === 'failed') {
                    $testCase = $pdo->query("SELECT * FROM test_cases WHERE id = " . $data['test_case_id'])->fetch();
                    if ($testCase) {
                        $stmt = $pdo->prepare("
                            INSERT INTO defects (title, description, project_id, test_case_id, reported_by, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        $stmt->execute([
                            'Test Failure: ' . $testCase['title'],
                            'Auto-created from failed test execution',
                            $testCase['project_id'],
                            $data['test_case_id'],
                            $user['id']
                        ]);
                    }
                }
                
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
    <title>TestFlow Pro - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card { 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            color: white; border-radius: 15px; padding: 25px; text-align: center; 
        }
        .stat-number { font-size: 2.5rem; font-weight: bold; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-flask me-2"></i>TestFlow Pro
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($user['name']) ?> (<?= ucfirst($user['role']) ?>)
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">
        <h1 class="h3 mb-4">
            <i class="fas fa-tachometer-alt text-primary me-2"></i><?= ucfirst($user['role']) ?> Dashboard
        </h1>

        <!-- Statistics Cards -->
        <div class="row mb-4" id="stats-cards">
            <!-- Stats will be loaded here -->
        </div>

        <!-- Test Cases Section -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Test Cases</h5>
                        <button class="btn btn-primary btn-sm" onclick="showCreateModal()">
                            <i class="fas fa-plus me-1"></i>Create Test Case
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="test-cases-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Project</th>
                                        <th>Priority</th>
                                        <th>Status</th>
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
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="showCreateModal()">
                                <i class="fas fa-plus me-2"></i>Create Test Case
                            </button>
                            <button class="btn btn-outline-success" onclick="loadTestCases()">
                                <i class="fas fa-refresh me-2"></i>Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Test Case Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Test Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createForm">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Steps</label>
                            <textarea class="form-control" name="test_steps" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expected Result</label>
                            <textarea class="form-control" name="expected_result" rows="2"></textarea>
                        </div>
                        <input type="hidden" name="project_id" value="1">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createTestCase()">Create</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Execute Test Modal -->
    <div class="modal fade" id="executeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Execute Test Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="executeForm">
                        <input type="hidden" name="test_case_id" id="executeTestCaseId">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="passed">✅ Passed</option>
                                <option value="failed">❌ Failed</option>
                                <option value="blocked">🚫 Blocked</option>
                                <option value="skipped">⏭️ Skipped</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Actual Result</label>
                            <textarea class="form-control" name="actual_result" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea class="form-control" name="comments" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="executeTest()">Execute</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadTestCases();
        });

        function loadStats() {
            fetch('index.php?action=get_stats')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('stats-cards').innerHTML = `
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-number">${data.total_projects}</div>
                                <div class="stat-label">Projects</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                                <div class="stat-number">${data.total_test_cases}</div>
                                <div class="stat-label">Test Cases</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8, #6f42c1);">
                                <div class="stat-number">${data.total_executions}</div>
                                <div class="stat-label">Executions</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
                                <div class="stat-number">${data.total_defects}</div>
                                <div class="stat-label">Defects</div>
                            </div>
                        </div>
                    `;
                });
        }

        function loadTestCases() {
            fetch('index.php?action=get_test_cases')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#test-cases-table tbody');
                    tbody.innerHTML = data.map(testCase => `
                        <tr>
                            <td><strong>${testCase.title}</strong></td>
                            <td><span class="badge bg-info">${testCase.project_name || 'N/A'}</span></td>
                            <td><span class="badge bg-${getPriorityColor(testCase.priority)}">${testCase.priority}</span></td>
                            <td><span class="badge bg-${getStatusColor(testCase.status)}">${testCase.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-success" onclick="showExecuteModal(${testCase.id})">
                                    <i class="fas fa-play"></i> Execute
                                </button>
                            </td>
                        </tr>
                    `).join('');
                });
        }

        function getPriorityColor(priority) {
            const colors = { low: 'success', medium: 'warning', high: 'danger', critical: 'dark' };
            return colors[priority] || 'secondary';
        }

        function getStatusColor(status) {
            const colors = { draft: 'secondary', active: 'primary', completed: 'success' };
            return colors[status] || 'secondary';
        }

        function showCreateModal() {
            new bootstrap.Modal(document.getElementById('createModal')).show();
        }

        function showExecuteModal(testCaseId) {
            document.getElementById('executeTestCaseId').value = testCaseId;
            new bootstrap.Modal(document.getElementById('executeModal')).show();
        }

        function createTestCase() {
            const form = document.getElementById('createForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            fetch('index.php?action=create_test_case', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
                    form.reset();
                    loadTestCases();
                    loadStats();
                    alert('Test case created successfully!');
                }
            });
        }

        function executeTest() {
            const form = document.getElementById('executeForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            fetch('index.php?action=execute_test', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('executeModal')).hide();
                    form.reset();
                    loadStats();
                    let message = 'Test executed successfully!';
                    if (data.status === 'failed') {
                        message += ' A defect has been automatically created.';
                    }
                    alert(message);
                }
            });
        }
    </script>
</body>
</html>
