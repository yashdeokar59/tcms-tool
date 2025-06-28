@extends('layouts.app')

@section('title', 'Tester Dashboard - TestFlow Pro')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-bug text-primary me-2"></i>Tester Dashboard
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('test-cases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Test Case
        </a>
        <a href="{{ route('test-cases.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-1"></i>All Test Cases
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_test_cases'] }}</div>
            <div class="stat-label">
                <i class="fas fa-list-check me-1"></i>Total Test Cases
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="stat-number">{{ $stats['total_executions'] }}</div>
            <div class="stat-label">
                <i class="fas fa-play me-1"></i>Test Executions
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
            <div class="stat-number">{{ $stats['total_defects'] }}</div>
            <div class="stat-label">
                <i class="fas fa-bug me-1"></i>Defects Found
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
            <div class="stat-number">{{ count($assigned_tests) }}</div>
            <div class="stat-label">
                <i class="fas fa-user-check me-1"></i>Assigned Tests
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Test Executions -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recent Test Executions
                </h5>
            </div>
            <div class="card-body">
                @if(count($recent_executions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Test Case</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Executed At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_executions as $execution)
                                <tr>
                                    <td>
                                        <strong>{{ $execution->title }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $execution->project_name }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'passed' => 'success',
                                                'failed' => 'danger',
                                                'blocked' => 'warning',
                                                'skipped' => 'secondary'
                                            ];
                                            $statusIcons = [
                                                'passed' => 'check-circle',
                                                'failed' => 'times-circle',
                                                'blocked' => 'exclamation-triangle',
                                                'skipped' => 'minus-circle'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$execution->status] ?? 'secondary' }}">
                                            <i class="fas fa-{{ $statusIcons[$execution->status] ?? 'question' }} me-1"></i>
                                            {{ ucfirst($execution->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($execution->executed_at)->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        <a href="{{ route('test-cases.show', ['id' => $execution->id ?? 1]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No test executions yet. Start executing test cases!</p>
                        <a href="{{ route('test-cases.index') }}" class="btn btn-primary">
                            <i class="fas fa-play me-1"></i>Execute Tests
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Assigned Test Cases -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-check me-2"></i>Assigned Test Cases
                </h5>
            </div>
            <div class="card-body">
                @if(count($assigned_tests) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($assigned_tests as $test)
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $test->title }}</h6>
                                    <p class="mb-1 text-muted small">{{ $test->project_name }}</p>
                                    <small class="text-muted">
                                        Priority: 
                                        <span class="badge bg-{{ $test->priority === 'high' ? 'danger' : ($test->priority === 'medium' ? 'warning' : 'success') }}">
                                            {{ ucfirst($test->priority) }}
                                        </span>
                                    </small>
                                </div>
                                <div class="ms-2">
                                    <a href="{{ route('test-cases.show', ['id' => $test->id ?? 1]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-tasks fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No assigned test cases</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('test-cases.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                <br>Create Test Case
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('test-cases.index') }}" class="btn btn-outline-success">
                                <i class="fas fa-play-circle fa-2x mb-2"></i>
                                <br>Execute Tests
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('defects.index') }}" class="btn btn-outline-danger">
                                <i class="fas fa-bug fa-2x mb-2"></i>
                                <br>Report Bug
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('requirements.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-link fa-2x mb-2"></i>
                                <br>Link Requirements
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
    
    // Add some interactive elements
    $(document).ready(function() {
        // Animate stat cards on load
        $('.stat-card').each(function(index) {
            $(this).delay(index * 100).animate({
                opacity: 1,
                transform: 'translateY(0)'
            }, 500);
        });
        
        // Add hover effects to action buttons
        $('.btn-outline-primary, .btn-outline-success, .btn-outline-danger, .btn-outline-info').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
    });
</script>
@endpush
