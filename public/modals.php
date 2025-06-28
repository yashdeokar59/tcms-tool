<!-- Create Test Case Modal -->
<div class="modal fade" id="createTestCaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Test Case</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTestCaseForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Priority *</label>
                                <select class="form-select" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project *</label>
                                <select class="form-select" name="project_id" required id="project-select">
                                    <option value="">Select Project</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type">
                                    <option value="functional">Functional</option>
                                    <option value="integration">Integration</option>
                                    <option value="system">System</option>
                                    <option value="acceptance">Acceptance</option>
                                    <option value="performance">Performance</option>
                                    <option value="security">Security</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Preconditions</label>
                        <textarea class="form-control" name="preconditions" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Test Steps</label>
                        <textarea class="form-control" name="test_steps" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Expected Result</label>
                        <textarea class="form-control" name="expected_result" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Complexity</label>
                                <select class="form-select" name="complexity">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estimated Time (minutes)</label>
                                <input type="number" class="form-control" name="estimated_time" min="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" class="form-control" name="tags" placeholder="Comma separated tags">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createTestCase()">Create Test Case</button>
            </div>
        </div>
    </div>
</div>

<!-- Execute Test Modal -->
<div class="modal fade" id="executeTestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Execute Test Case</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="executeTestForm">
                    <input type="hidden" name="test_case_id" id="executeTestCaseId">
                    
                    <div class="mb-3">
                        <label class="form-label">Execution Status *</label>
                        <select class="form-select" name="status" required>
                            <option value="passed">✅ Passed</option>
                            <option value="failed">❌ Failed</option>
                            <option value="blocked">🚫 Blocked</option>
                            <option value="skipped">⏭️ Skipped</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Actual Result</label>
                        <textarea class="form-control" name="actual_result" rows="3" placeholder="Describe what actually happened during test execution"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" name="comments" rows="2" placeholder="Additional comments or notes"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Browser</label>
                                <input type="text" class="form-control" name="browser" placeholder="e.g., Chrome 91">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Operating System</label>
                                <input type="text" class="form-control" name="os" placeholder="e.g., Windows 10">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Execution Time (minutes)</label>
                        <input type="number" class="form-control" name="execution_time" min="1" placeholder="How long did the test take?">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="executeTest()">Execute Test</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Project Modal -->
<?php if (hasAnyRole(['admin', 'manager'])): ?>
<div class="modal fade" id="createProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createProjectForm">
                    <div class="mb-3">
                        <label class="form-label">Project Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="planning">Planning</option>
                                    <option value="on-hold">On Hold</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project Manager</label>
                                <select class="form-select" name="manager_id" id="manager-select">
                                    <option value="">Select Manager</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createProject()">Create Project</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Create User Modal (Admin Only) -->
<?php if (hasRole('admin')): ?>
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">👑 Admin</option>
                            <option value="manager">📊 Manager</option>
                            <option value="developer">👨‍💻 Developer</option>
                            <option value="tester">🧪 Tester</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createUser()">Create User</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changeRoleForm">
                    <input type="hidden" name="user_id" id="changeRoleUserId">
                    
                    <div class="mb-3">
                        <label class="form-label">New Role</label>
                        <select class="form-select" name="role" required id="changeRoleSelect">
                            <option value="admin">👑 Admin</option>
                            <option value="manager">📊 Manager</option>
                            <option value="developer">👨‍💻 Developer</option>
                            <option value="tester">🧪 Tester</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="changeUserRole()">Change Role</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
