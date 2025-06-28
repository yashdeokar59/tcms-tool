@extends('layouts.app')

@section('title', 'Dashboard - TestFlow Pro')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Welcome to TestFlow Pro - Your comprehensive test management solution</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-folder text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_projects'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-list-check text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Test Cases</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_test_cases'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-play text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Test Runs</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['active_test_runs'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-bug text-2xl text-red-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Open Defects</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['open_defects'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Execution Chart -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Test Execution Overview</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $testExecutionStats['passed'] }}</div>
                    <div class="text-sm text-gray-500">Passed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $testExecutionStats['failed'] }}</div>
                    <div class="text-sm text-gray-500">Failed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $testExecutionStats['blocked'] }}</div>
                    <div class="text-sm text-gray-500">Blocked</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ $testExecutionStats['not_executed'] }}</div>
                    <div class="text-sm text-gray-500">Not Executed</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Recent Projects -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Projects</h3>
                <div class="space-y-3">
                    @forelse($recentProjects as $project)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                                <p class="text-xs text-gray-500">{{ $project->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No projects yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Test Cases -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Test Cases</h3>
                <div class="space-y-3">
                    @forelse($recentTestCases as $testCase)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ Str::limit($testCase->title, 30) }}</p>
                                <p class="text-xs text-gray-500">{{ $testCase->project->name ?? 'N/A' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $testCase->priority === 'high' ? 'bg-red-100 text-red-800' : 
                                   ($testCase->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst($testCase->priority) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No test cases yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Defects -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Defects</h3>
                <div class="space-y-3">
                    @forelse($recentDefects as $defect)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ Str::limit($defect->title, 30) }}</p>
                                <p class="text-xs text-gray-500">{{ $defect->project->name ?? 'N/A' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $defect->severity === 'critical' ? 'bg-red-100 text-red-800' : 
                                   ($defect->severity === 'high' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($defect->severity) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No defects yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <a href="{{ route('projects.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                    <i class="fas fa-plus mr-2"></i>New Project
                </a>
                <a href="{{ route('test-cases.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i>New Test Case
                </a>
                <a href="{{ route('test-runs.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                    <i class="fas fa-play mr-2"></i>New Test Run
                </a>
                <a href="{{ route('defects.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                    <i class="fas fa-bug mr-2"></i>Report Defect
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
