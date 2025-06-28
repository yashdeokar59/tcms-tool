// TestFlow Pro - JavaScript Application Logic

// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadProjects();
    loadUsers();
});

// Section Management
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionName + '-section');
    if (targetSection) {
        targetSection.style.display = 'block';
    }
    
    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Load section-specific data
    switch(sectionName) {
        case 'test-cases':
            loadTestCases();
            break;
        case 'projects':
            loadProjects();
            break;
        case 'defects':
            loadDefects();
            break;
        case 'users':
            loadUsers();
            break;
    }
}

// Statistics Loading
function loadStats() {
    fetch('index.php?action=get_stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('stats-cards').innerHTML = `
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">${data.total_projects}</div>
                        <div class="stat-label"><i class="fas fa-folder me-1"></i>Projects</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <div class="stat-number">${data.total_test_cases}</div>
                        <div class="stat-label"><i class="fas fa-list-check me-1"></i>Test Cases</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8, #6f42c1);">
                        <div class="stat-number">${data.total_executions}</div>
                        <div class="stat-label"><i class="fas fa-play me-1"></i>Executions</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
                        <div class="stat-number">${data.total_defects}</div>
                        <div class="stat-label"><i class="fas fa-bug me-1"></i>Defects</div>
                    </div>
                </div>
            `;
        })
        .catch(error => console.error('Error loading stats:', error));
}

// Test Cases Management
function loadTestCases() {
    fetch('index.php?action=get_test_cases')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#test-cases-table tbody');
            if (tbody) {
                tbody.innerHTML = data.map(testCase => `
                    <tr>
                        <td>
                            <strong>${escapeHtml(testCase.title)}</strong>
                            <br><small class="text-muted">${escapeHtml(testCase.description.substring(0, 60))}...</small>
                        </td>
                        <td><span class="badge bg-info">${escapeHtml(testCase.project_name || 'N/A')}</span></td>
                        <td><span class="badge bg-${getPriorityColor(testCase.priority)}">${testCase.priority}</span></td>
                        <td><span class="badge bg-${getStatusColor(testCase.status)}">${testCase.status}</span></td>
                        <td>${escapeHtml(testCase.assignee_name || 'Unassigned')}</td>
                        <td>
                            ${testCase.last_status ? 
                                `<span class="badge bg-${getExecutionStatusColor(testCase.last_status)}">${testCase.last_status}</span>` : 
                                '<span class="text-muted">Never executed</span>'
                            }
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-success" onclick="showExecuteTestModal(${testCase.id})" title="Execute Test">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewTestCase(${testCase.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading test cases:', error));
}

// Projects Management
function loadProjects() {
    fetch('index.php?action=get_projects')
        .then(response => response.json())
        .then(data => {
            // Populate project select in modals
            const projectSelect = document.getElementById('project-select');
            if (projectSelect) {
                projectSelect.innerHTML = '<option value="">Select Project</option>' + 
                    data.map(project => `<option value="${project.id}">${escapeHtml(project.name)}</option>`).join('');
            }
            
            // Populate manager select
            const managerSelect = document.getElementById('manager-select');
            if (managerSelect) {
                fetch('index.php?action=get_users')
                    .then(response => response.json())
                    .then(users => {
                        const managers = users.filter(user => ['admin', 'manager'].includes(user.role));
                        managerSelect.innerHTML = '<option value="">Select Manager</option>' + 
                            managers.map(user => `<option value="${user.id}">${escapeHtml(user.name)}</option>`).join('');
                    });
            }
            
            // Populate projects table
            const tbody = document.querySelector('#projects-table tbody');
            if (tbody) {
                tbody.innerHTML = data.map(project => `
                    <tr>
                        <td><strong>${escapeHtml(project.name)}</strong></td>
                        <td>${escapeHtml(project.description.substring(0, 80))}...</td>
                        <td><span class="badge bg-${getProjectStatusColor(project.status)}">${project.status}</span></td>
                        <td>${escapeHtml(project.manager_name || 'Unassigned')}</td>
                        <td><span class="badge bg-secondary">${project.test_case_count}</span></td>
                        <td><small class="text-muted">${formatDate(project.created_at)}</small></td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading projects:', error));
}

// Defects Management
function loadDefects() {
    fetch('index.php?action=get_defects')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#defects-table tbody');
            if (tbody) {
                tbody.innerHTML = data.map(defect => `
                    <tr>
                        <td>
                            <strong>${escapeHtml(defect.title)}</strong>
                            ${defect.test_case_title ? `<br><small class="text-muted">From: ${escapeHtml(defect.test_case_title)}</small>` : ''}
                        </td>
                        <td><span class="badge bg-info">${escapeHtml(defect.project_name || 'N/A')}</span></td>
                        <td><span class="badge bg-${getSeverityColor(defect.severity)}">${defect.severity}</span></td>
                        <td><span class="badge bg-${getDefectStatusColor(defect.status)}">${defect.status}</span></td>
                        <td>${escapeHtml(defect.reporter_name || 'Unknown')}</td>
                        <td>${escapeHtml(defect.assignee_name || 'Unassigned')}</td>
                        <td><small class="text-muted">${formatDate(defect.created_at)}</small></td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading defects:', error));
}

// User Management (Admin Only)
function loadUsers() {
    fetch('index.php?action=get_users')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#users-table tbody');
            if (tbody) {
                tbody.innerHTML = data.map(user => `
                    <tr>
                        <td><strong>${escapeHtml(user.name)}</strong></td>
                        <td>${escapeHtml(user.email)}</td>
                        <td><span class="badge bg-${getRoleColor(user.role)}">${getRoleIcon(user.role)} ${user.role}</span></td>
                        <td><span class="badge bg-${user.is_active ? 'success' : 'danger'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                        <td><small class="text-muted">${user.last_login_at ? formatDate(user.last_login_at) : 'Never'}</small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="showChangeRoleModal(${user.id}, '${user.role}')" title="Change Role">
                                <i class="fas fa-user-cog"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})" title="Deactivate User">
                                <i class="fas fa-user-times"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

// Modal Functions
function showCreateTestCaseModal() {
    loadProjects(); // Ensure projects are loaded
    new bootstrap.Modal(document.getElementById('createTestCaseModal')).show();
}

function showExecuteTestModal(testCaseId) {
    document.getElementById('executeTestCaseId').value = testCaseId;
    new bootstrap.Modal(document.getElementById('executeTestModal')).show();
}

function showCreateProjectModal() {
    new bootstrap.Modal(document.getElementById('createProjectModal')).show();
}

function showCreateUserModal() {
    new bootstrap.Modal(document.getElementById('createUserModal')).show();
}

function showChangeRoleModal(userId, currentRole) {
    document.getElementById('changeRoleUserId').value = userId;
    document.getElementById('changeRoleSelect').value = currentRole;
    new bootstrap.Modal(document.getElementById('changeRoleModal')).show();
}

// CRUD Operations
function createTestCase() {
    const form = document.getElementById('createTestCaseForm');
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
            bootstrap.Modal.getInstance(document.getElementById('createTestCaseModal')).hide();
            form.reset();
            loadTestCases();
            loadStats();
            showAlert('Test case created successfully!', 'success');
        } else {
            showAlert(result.error || 'Failed to create test case', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while creating the test case', 'danger');
    });
}

function executeTest() {
    const form = document.getElementById('executeTestForm');
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
            bootstrap.Modal.getInstance(document.getElementById('executeTestModal')).hide();
            form.reset();
            loadTestCases();
            loadStats();
            loadDefects();
            
            let message = 'Test executed successfully!';
            if (data.status === 'failed') {
                message += ' A defect has been automatically created.';
            }
            showAlert(message, 'success');
        } else {
            showAlert('Failed to execute test', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while executing the test', 'danger');
    });
}

function createProject() {
    const form = document.getElementById('createProjectForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('index.php?action=create_project', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('createProjectModal')).hide();
            form.reset();
            loadProjects();
            loadStats();
            showAlert('Project created successfully!', 'success');
        } else {
            showAlert('Failed to create project', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while creating the project', 'danger');
    });
}

function createUser() {
    const form = document.getElementById('createUserForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('index.php?action=create_user', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
            form.reset();
            loadUsers();
            showAlert('User created successfully!', 'success');
        } else {
            showAlert(result.error || 'Failed to create user', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while creating the user', 'danger');
    });
}

function changeUserRole() {
    const form = document.getElementById('changeRoleForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('index.php?action=update_user_role', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('changeRoleModal')).hide();
            loadUsers();
            showAlert('User role updated successfully!', 'success');
        } else {
            showAlert('Failed to update user role', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating the user role', 'danger');
    });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to deactivate this user?')) {
        fetch('index.php?action=delete_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadUsers();
                showAlert('User deactivated successfully!', 'success');
            } else {
                showAlert(result.error || 'Failed to deactivate user', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deactivating the user', 'danger');
        });
    }
}

// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.p-4');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Color Helper Functions
function getPriorityColor(priority) {
    const colors = { low: 'success', medium: 'warning', high: 'danger', critical: 'dark' };
    return colors[priority] || 'secondary';
}

function getStatusColor(status) {
    const colors = { 
        draft: 'secondary', review: 'warning', approved: 'success', 
        active: 'primary', deprecated: 'danger' 
    };
    return colors[status] || 'secondary';
}

function getExecutionStatusColor(status) {
    const colors = { 
        passed: 'success', failed: 'danger', blocked: 'warning', skipped: 'secondary' 
    };
    return colors[status] || 'secondary';
}

function getProjectStatusColor(status) {
    const colors = { 
        active: 'success', planning: 'info', 'on-hold': 'warning', completed: 'primary' 
    };
    return colors[status] || 'secondary';
}

function getSeverityColor(severity) {
    const colors = { low: 'success', medium: 'warning', high: 'danger', critical: 'dark' };
    return colors[severity] || 'secondary';
}

function getDefectStatusColor(status) {
    const colors = { 
        open: 'danger', 'in-progress': 'warning', resolved: 'success', closed: 'secondary' 
    };
    return colors[status] || 'secondary';
}

function getRoleColor(role) {
    const colors = { admin: 'danger', manager: 'primary', developer: 'info', tester: 'success' };
    return colors[role] || 'secondary';
}

function getRoleIcon(role) {
    const icons = { admin: '👑', manager: '📊', developer: '👨‍💻', tester: '🧪' };
    return icons[role] || '👤';
}

// Auto-refresh functionality
setInterval(() => {
    if (document.getElementById('dashboard-section').style.display !== 'none') {
        loadStats();
    }
}, 30000); // Refresh every 30 seconds
