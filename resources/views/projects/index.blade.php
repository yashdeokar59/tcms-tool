@extends('layouts.app')

@section('title', 'Projects - TestFlow Pro')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Projects</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your test projects and organize your testing efforts</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
            <i class="fas fa-plus mr-2"></i>New Project
        </a>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($projects as $project)
            <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-200">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ $project->name }}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                    
                    @if($project->description)
                        <p class="text-sm text-gray-600 mb-4">{{ Str::limit($project->description, 100) }}</p>
                    @endif

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900">{{ $project->testCases->count() }}</div>
                            <div class="text-xs text-gray-500">Test Cases</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900">{{ $project->testRuns->count() }}</div>
                            <div class="text-xs text-gray-500">Test Runs</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <span>Created by {{ $project->creator->name ?? 'Unknown' }}</span>
                        <span>{{ $project->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('projects.show', $project) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No projects yet</h3>
                    <p class="text-gray-600 mb-4">Get started by creating your first project</p>
                    <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                        <i class="fas fa-plus mr-2"></i>Create Project
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection
