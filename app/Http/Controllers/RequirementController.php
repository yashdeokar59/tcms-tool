<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use App\Models\Project;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequirementController extends Controller
{
    public function index(Request $request)
    {
        $query = Requirement::with(['project', 'module', 'creator', 'assignee', 'testCases']);
        
        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $requirements = $query->paginate(20);
        
        // Get filter options
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $modules = Module::all();
        $users = User::where('is_active', true)->get();
        
        return view('requirements.index', compact('requirements', 'projects', 'modules', 'users'));
    }

    public function create()
    {
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $modules = Module::all();
        $users = User::where('is_active', true)->get();
        
        return view('requirements.create', compact('projects', 'modules', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:functional,non_functional,business,technical',
            'priority' => 'required|in:critical,high,medium,low',
            'project_id' => 'required|exists:projects,id',
            'module_id' => 'nullable|exists:modules,id',
            'assigned_to' => 'nullable|exists:users,id',
            'acceptance_criteria' => 'nullable|array',
            'business_value' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'draft';

        $requirement = Requirement::create($validated);

        return redirect()->route('requirements.show', $requirement)
            ->with('success', 'Requirement created successfully.');
    }

    public function show(Requirement $requirement)
    {
        $requirement->load([
            'project', 'module', 'creator', 'assignee',
            'testCases.creator', 'testCases.assignee'
        ]);

        return view('requirements.show', compact('requirement'));
    }

    public function edit(Requirement $requirement)
    {
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $modules = Module::where('project_id', $requirement->project_id)->get();
        $users = User::where('is_active', true)->get();
        
        return view('requirements.edit', compact('requirement', 'projects', 'modules', 'users'));
    }

    public function update(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:functional,non_functional,business,technical',
            'priority' => 'required|in:critical,high,medium,low',
            'status' => 'required|in:draft,review,approved,implemented,tested',
            'module_id' => 'nullable|exists:modules,id',
            'assigned_to' => 'nullable|exists:users,id',
            'acceptance_criteria' => 'nullable|array',
            'business_value' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        $requirement->update($validated);

        return redirect()->route('requirements.show', $requirement)
            ->with('success', 'Requirement updated successfully.');
    }

    public function destroy(Requirement $requirement)
    {
        $requirement->delete();

        return redirect()->route('requirements.index')
            ->with('success', 'Requirement deleted successfully.');
    }

    public function coverage(Request $request)
    {
        $projectId = $request->get('project_id');
        $user = Auth::user();
        
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $currentProject = $projectId ? $projects->find($projectId) : $projects->first();
        
        if (!$currentProject) {
            return redirect()->route('requirements.index')
                ->with('error', 'Please select a project.');
        }

        $requirements = $currentProject->requirements()
            ->with(['testCases', 'module'])
            ->get();

        $coverageData = $requirements->map(function($requirement) {
            return [
                'requirement' => $requirement,
                'test_cases_count' => $requirement->testCases->count(),
                'coverage_percentage' => $requirement->testCases->count() > 0 ? 100 : 0,
                'test_cases' => $requirement->testCases,
            ];
        });

        $overallCoverage = $currentProject->test_coverage;

        return view('requirements.coverage', compact('coverageData', 'overallCoverage', 'currentProject', 'projects'));
    }
}
