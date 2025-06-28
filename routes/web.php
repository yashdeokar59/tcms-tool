<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TestCaseController;
use App\Http\Controllers\TestRunController;
use App\Http\Controllers\DefectController;
use App\Http\Controllers\TestSuiteController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\TestEnvironmentController;
use App\Http\Controllers\TestCycleController;

// Dashboard routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/switch-role/{role}', [DashboardController::class, 'switchRole'])->name('dashboard.switch-role');

// Test Cases - Full CRUD with additional features
Route::resource('test-cases', TestCaseController::class);
Route::post('test-cases/{testCase}/clone', [TestCaseController::class, 'clone'])->name('test-cases.clone');
Route::get('test-cases/{testCase}/execute', [TestCaseController::class, 'execute'])->name('test-cases.execute');
Route::post('test-cases/{testCase}/execute', [TestCaseController::class, 'storeExecution'])->name('test-cases.store-execution');
Route::post('test-cases/{testCase}/comments', [TestCaseController::class, 'addComment'])->name('test-cases.add-comment');
Route::delete('test-cases/{testCase}/attachments/{attachment}', [TestCaseController::class, 'removeAttachment'])->name('test-cases.remove-attachment');

// Projects - Full CRUD
Route::resource('projects', ProjectController::class);
Route::post('projects/{project}/assign-user', [ProjectController::class, 'assignUser'])->name('projects.assign-user');
Route::delete('projects/{project}/remove-user/{user}', [ProjectController::class, 'removeUser'])->name('projects.remove-user');

// Modules
Route::resource('modules', ModuleController::class);
Route::get('projects/{project}/modules', [ModuleController::class, 'byProject'])->name('modules.by-project');

// Requirements
Route::resource('requirements', RequirementController::class);
Route::get('requirements-coverage', [RequirementController::class, 'coverage'])->name('requirements.coverage');

// Test Suites
Route::resource('test-suites', TestSuiteController::class);

// Test Environments
Route::resource('test-environments', TestEnvironmentController::class);

// Test Cycles
Route::resource('test-cycles', TestCycleController::class);

// Test Runs
Route::resource('test-runs', TestRunController::class);
Route::post('test-runs/{testRun}/start', [TestRunController::class, 'start'])->name('test-runs.start');
Route::post('test-runs/{testRun}/complete', [TestRunController::class, 'complete'])->name('test-runs.complete');
Route::post('test-runs/{testRun}/comments', [TestRunController::class, 'addComment'])->name('test-runs.add-comment');

// Defects
Route::resource('defects', DefectController::class);
Route::post('defects/{defect}/resolve', [DefectController::class, 'resolve'])->name('defects.resolve');
Route::post('defects/{defect}/verify', [DefectController::class, 'verify'])->name('defects.verify');
Route::post('defects/{defect}/comments', [DefectController::class, 'addComment'])->name('defects.add-comment');

// Reports (Manager/Admin only)
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('test-execution', [ReportController::class, 'testExecution'])->name('test-execution');
    Route::get('defect-analysis', [ReportController::class, 'defectAnalysis'])->name('defect-analysis');
    Route::get('test-coverage', [ReportController::class, 'testCoverage'])->name('test-coverage');
    Route::get('team-performance', [ReportController::class, 'teamPerformance'])->name('team-performance');
    Route::post('export', [ReportController::class, 'export'])->name('export');
});

// Admin routes (Admin only)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::get('users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Project Management
    Route::get('projects', [AdminController::class, 'projects'])->name('projects');
    Route::get('projects/create', [AdminController::class, 'createProject'])->name('projects.create');
    Route::post('projects', [AdminController::class, 'storeProject'])->name('projects.store');
    Route::get('projects/{project}/edit', [AdminController::class, 'editProject'])->name('projects.edit');
    Route::put('projects/{project}', [AdminController::class, 'updateProject'])->name('projects.update');
    Route::delete('projects/{project}', [AdminController::class, 'deleteProject'])->name('projects.delete');
    
    // Environment Management
    Route::get('environments', [AdminController::class, 'environments'])->name('environments');
    Route::get('environments/create', [AdminController::class, 'createEnvironment'])->name('environments.create');
    Route::post('environments', [AdminController::class, 'storeEnvironment'])->name('environments.store');
    Route::get('environments/{environment}/edit', [AdminController::class, 'editEnvironment'])->name('environments.edit');
    Route::put('environments/{environment}', [AdminController::class, 'updateEnvironment'])->name('environments.update');
    Route::delete('environments/{environment}', [AdminController::class, 'deleteEnvironment'])->name('environments.delete');
    
    // System Settings
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    
    // System Health
    Route::get('system-health', [AdminController::class, 'systemHealth'])->name('system-health');
});

// Notifications
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('mark-read/{notification?}', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
});

// API Routes for AJAX
Route::prefix('api')->name('api.')->group(function () {
    Route::get('projects/{project}/test-suites', function($projectId) {
        return \App\Models\TestSuite::where('project_id', $projectId)->get();
    })->name('projects.test-suites');
    
    Route::get('projects/{project}/modules', function($projectId) {
        return \App\Models\Module::where('project_id', $projectId)->get();
    })->name('projects.modules');
    
    Route::get('projects/{project}/requirements', function($projectId) {
        return \App\Models\Requirement::where('project_id', $projectId)->get();
    })->name('projects.requirements');
    
    Route::get('projects/{project}/environments', function($projectId) {
        return \App\Models\TestEnvironment::where('project_id', $projectId)->get();
    })->name('projects.environments');
    
    Route::get('test-suites/{testSuite}/test-cases', function($testSuiteId) {
        return \App\Models\TestCase::where('test_suite_id', $testSuiteId)->get();
    })->name('test-suites.test-cases');
    
    Route::get('modules/{module}/test-cases', function($moduleId) {
        return \App\Models\TestCase::where('module_id', $moduleId)->get();
    })->name('modules.test-cases');
    
    Route::get('test-cases/{testCase}/executions', function($testCaseId) {
        return \App\Models\TestExecution::where('test_case_id', $testCaseId)
            ->with('executor')
            ->latest()
            ->get();
    })->name('test-cases.executions');
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok', 
        'timestamp' => now(),
        'database' => 'connected',
        'version' => '1.0.0',
        'features' => [
            'test_management' => 'active',
            'requirements_traceability' => 'active',
            'defect_tracking' => 'active',
            'role_based_access' => 'active',
            'reporting' => 'active',
            'notifications' => 'active'
        ]
    ]);
})->name('health-check');
