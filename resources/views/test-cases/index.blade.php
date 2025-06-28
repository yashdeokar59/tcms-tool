@extends('layouts.app')

@section('title', 'Test Cases - TestFlow Pro')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-list-check text-primary me-2"></i>Test Cases
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('test-cases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Test Case
        </a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter me-1"></i>Filters
        </button>
    </div>
</div>

<!-- Filters Bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('test-cases.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $priority)
                    <option value="{{ $priority }}" {{ request('priority') == $priority ? 'selected' : '' }}>
                        {{ ucfirst($priority) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search test cases..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('test-cases.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Test Cases Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Test Cases ({{ $testCases->total() }})
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                    <i class="fas fa-check-square me-1"></i>Select All
                </button>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="bulkExecute()">
                            <i class="fas fa-play me-2"></i>Bulk Execute
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="bulkExport()">
                            <i class="fas fa-download me-2"></i>Export Selected
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="bulkDelete()">
                            <i class="fas fa-trash me-2"></i>Delete Selected
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($testCases->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll()">
                        </th>
                        <th>Test Case</th>
                        <th>Project</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Last Execution</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($testCases as $testCase)
                    <tr>
                        <td>
                            <input type="checkbox" class="test-case-checkbox" value="{{ $testCase->id }}">
                        </td>
                        <td>
                            <div>
                                <strong>{{ $testCase->title }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($testCase->description, 60) }}</small>
                                @if($testCase->tags)
                                <div class="mt-1">
                                    @foreach(explode(',', $testCase->tags) as $tag)
                                    <span class="badge bg-light text-dark me-1">{{ trim($tag) }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $testCase->project->name ?? 'N/A' }}</span>
                            @if($testCase->module)
                            <br><small class="text-muted">{{ $testCase->module->name }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $priorityColors = [
                                    'low' => 'success',
                                    'medium' => 'warning',
                                    'high' => 'danger',
                                    'critical' => 'dark'
                                ];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$testCase->priority] ?? 'secondary' }}">
                                {{ ucfirst($testCase->priority) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'review' => 'warning',
                                    'approved' => 'success',
                                    'active' => 'primary',
                                    'deprecated' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$testCase->status] ?? 'secondary' }}">
                                {{ ucfirst($testCase->status) }}
                            </span>
                        </td>
                        <td>
                            @if($testCase->assignee)
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user text-white small"></i>
                                    </div>
                                    <small>{{ $testCase->assignee->name }}</small>
                                </div>
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $lastExecution = \App\Models\TestExecution::where('test_case_id', $testCase->id)
                                    ->orderBy('executed_at', 'desc')->first();
                            @endphp
                            @if($lastExecution)
                                <div>
                                    <span class="badge bg-{{ $lastExecution->status === 'passed' ? 'success' : ($lastExecution->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($lastExecution->status) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($lastExecution->executed_at)->diffForHumans() }}
                                    </small>
                                </div>
                            @else
                                <span class="text-muted">Never executed</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('test-cases.show', $testCase->id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('test-cases.execute', $testCase->id) }}" 
                                   class="btn btn-sm btn-outline-success" title="Execute">
                                    <i class="fas fa-play"></i>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('test-cases.edit', $testCase->id) }}">
                                            <i class="fas fa-edit me-2"></i>Edit
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('test-cases.clone', $testCase->id) }}">
                                            <i class="fas fa-copy me-2"></i>Clone
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" 
                                               onclick="deleteTestCase({{ $testCase->id }})">
                                            <i class="fas fa-trash me-2"></i>Delete
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer">
            {{ $testCases->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No Test Cases Found</h5>
            <p class="text-muted">Create your first test case to get started with testing.</p>
            <a href="{{ route('test-cases.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create Test Case
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this test case? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAll() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.test-case-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.test-case-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
}

function deleteTestCase(id) {
    const form = document.getElementById('deleteForm');
    form.action = `/test-cases/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function bulkExecute() {
    const selected = getSelectedTestCases();
    if (selected.length === 0) {
        alert('Please select test cases to execute.');
        return;
    }
    
    // Implement bulk execution logic
    console.log('Bulk execute:', selected);
    alert('Bulk execution feature will be implemented.');
}

function bulkExport() {
    const selected = getSelectedTestCases();
    if (selected.length === 0) {
        alert('Please select test cases to export.');
        return;
    }
    
    // Implement bulk export logic
    console.log('Bulk export:', selected);
    alert('Bulk export feature will be implemented.');
}

function bulkDelete() {
    const selected = getSelectedTestCases();
    if (selected.length === 0) {
        alert('Please select test cases to delete.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selected.length} test case(s)?`)) {
        // Implement bulk delete logic
        console.log('Bulk delete:', selected);
        alert('Bulk delete feature will be implemented.');
    }
}

function getSelectedTestCases() {
    const checkboxes = document.querySelectorAll('.test-case-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Auto-refresh every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
</script>
@endpush
