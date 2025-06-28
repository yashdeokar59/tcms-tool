@extends('layouts.app')

@section('title', 'Manager Dashboard - TestFlow Pro')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-chart-line text-primary me-2"></i>Manager Dashboard
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-primary">
            <i class="fas fa-chart-bar me-1"></i>View Reports
        </a>
        <a href="{{ route('projects.create') }}" class="btn btn-outline-primary">
            <i class="fas fa-plus me-1"></i>New Project
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_projects'] }}</div>
            <div class="stat-label">
                <i class="fas fa-folder me-1"></i>Active Projects
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="stat-number">{{ $stats['total_test_cases'] }}</div>
            <div class="stat-label">
                <i class="fas fa-list-check me-1"></i>Test Cases
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8, #6f42c1);">
            <div class="stat-number">{{ $stats['total_executions'] }}</div>
            <div class="stat-label">
                <i class="fas fa-play me-1"></i>Executions
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
            <div class="stat-number">{{ $stats['total_defects'] }}</div>
            <div class="stat-label">
                <i class="fas fa-bug me-1"></i>Open Defects
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Test Execution Progress -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Test Execution Progress
                </h5>
            </div>
            <div class="card-body">
                <canvas id="executionChart" width="400" height="200"></canvas>
                
                @if(count($execution_stats) > 0)
                <div class="mt-3">
                    <div class="row text-center">
                        @foreach($execution_stats as $stat)
                        <div class="col-6 col-md-3 mb-2">
                            <div class="p-2 rounded" style="background: rgba(102, 126, 234, 0.1);">
                                <div class="fw-bold">{{ $stat->count }}</div>
                                <small class="text-muted">{{ ucfirst($stat->status) }}</small>
                                <div class="small text-primary">{{ $stat->percentage }}%</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No execution data available</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Defect Trends -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>Defect Trends (30 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="defectTrendChart" width="400" height="200"></canvas>
                
                @if(count($defect_trends) > 0)
                <div class="mt-3">
                    <div class="d-flex justify-content-between text-center">
                        <div>
                            <div class="fw-bold text-danger">{{ array_sum(array_column($defect_trends, 'count')) }}</div>
                            <small class="text-muted">Total Defects</small>
                        </div>
                        <div>
                            <div class="fw-bold text-warning">{{ round(array_sum(array_column($defect_trends, 'count')) / 30, 1) }}</div>
                            <small class="text-muted">Daily Average</small>
                        </div>
                        <div>
                            <div class="fw-bold text-info">{{ max(array_column($defect_trends, 'count')) }}</div>
                            <small class="text-muted">Peak Day</small>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No defect trend data available</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Team Performance -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Team Performance
                </h5>
            </div>
            <div class="card-body">
                @if(count($team_performance) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Team Member</th>
                                <th>Total Executions</th>
                                <th>Passed</th>
                                <th>Failed</th>
                                <th>Success Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($team_performance as $member)
                            @php
                                $successRate = $member->executions > 0 ? round(($member->passed / $member->executions) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <strong>{{ $member->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $member->executions }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $member->passed }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">{{ $member->failed }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $successRate >= 80 ? 'success' : ($successRate >= 60 ? 'warning' : 'danger') }}">
                                        {{ $successRate }}%
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $successRate >= 80 ? 'success' : ($successRate >= 60 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $successRate }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No team performance data available</p>
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
                    <i class="fas fa-bolt me-2"></i>Management Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('projects.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-folder-plus fa-2x mb-2"></i>
                                <br>New Project
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('test-runs.create') }}" class="btn btn-outline-success">
                                <i class="fas fa-play-circle fa-2x mb-2"></i>
                                <br>Start Test Run
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <br>View Reports
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('test-cases.index') }}" class="btn btn-outline-warning">
                                <i class="fas fa-tasks fa-2x mb-2"></i>
                                <br>Assign Tasks
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('defects.index') }}" class="btn btn-outline-danger">
                                <i class="fas fa-bug fa-2x mb-2"></i>
                                <br>Review Defects
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('requirements.coverage') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                <br>Coverage Report
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
$(document).ready(function() {
    // Execution Progress Chart
    @if(count($execution_stats) > 0)
    const executionCtx = document.getElementById('executionChart').getContext('2d');
    new Chart(executionCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($execution_stats as $stat)
                '{{ ucfirst($stat->status) }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($execution_stats as $stat)
                    {{ $stat->count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#28a745', // passed - green
                    '#dc3545', // failed - red
                    '#ffc107', // blocked - yellow
                    '#6c757d'  // skipped - gray
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    @endif

    // Defect Trends Chart
    @if(count($defect_trends) > 0)
    const defectCtx = document.getElementById('defectTrendChart').getContext('2d');
    new Chart(defectCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($defect_trends as $trend)
                '{{ date("M j", strtotime($trend->date)) }}',
                @endforeach
            ],
            datasets: [{
                label: 'Defects',
                data: [
                    @foreach($defect_trends as $trend)
                    {{ $trend->count }},
                    @endforeach
                ],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif

    // Auto-refresh dashboard every 10 minutes
    setTimeout(function() {
        location.reload();
    }, 600000);
});
</script>
@endpush
