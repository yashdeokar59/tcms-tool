<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TestCase;
use App\Models\Project;
use App\Models\TestSuite;
use App\Models\Module;
use App\Models\Requirement;
use App\Models\TestExecution;
use App\Models\Defect;
use App\Models\Attachment;
use App\Models\Comment;

class TestCaseController extends Controller
{
    public function index(Request $request)
    {
        $query = TestCase::with(['project', 'testSuite', 'module', 'creator', 'assignee']);
        
        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        $testCases = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get filter options
        $projects = Project::where('is_active', 1)->get();
        $statuses = ['draft', 'review', 'approved', 'active', 'deprecated'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        
        return view('test-cases.index', compact('testCases', 'projects', 'statuses', 'priorities'));
    }

    public function create()
    {
        $projects = Project::where('is_active', 1)->get();
        $testSuites = TestSuite::all();
        $modules = Module::where('is_active', 1)->get();
        $requirements = Requirement::all();
        $users = DB::select("SELECT id, name FROM users WHERE is_active = 1 AND role IN ('tester', 'developer')");
        
        return view('test-cases.create', compact('projects', 'testSuites', 'modules', 'requirements', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'test_suite_id' => 'nullable|exists:test_suites,id',
            'module_id' => 'nullable|exists:modules,id',
            'priority' => 'required|in:low,medium,high,critical',
            'type' => 'required|in:functional,integration,system,acceptance,performance,security',
            'complexity' => 'required|in:low,medium,high',
            'preconditions' => 'nullable|string',
            'test_steps' => 'required|string',
            'expected_result' => 'required|string',
            'postconditions' => 'nullable|string',
            'test_data' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_time' => 'nullable|integer|min:1',
            'tags' => 'nullable|string',
            'requirements' => 'nullable|array',
            'requirements.*' => 'exists:requirements,id'
        ]);

        $validated['created_by'] = session('user_id', 1);
        $validated['status'] = 'draft';

        $testCase = TestCase::create($validated);

        // Link requirements
        if (!empty($validated['requirements'])) {
            foreach ($validated['requirements'] as $requirementId) {
                DB::insert("INSERT INTO requirement_test_cases (requirement_id, test_case_id, created_at, updated_at) VALUES (?, ?, datetime('now'), datetime('now'))", 
                    [$requirementId, $testCase->id]);
            }
        }

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'public');
                
                Attachment::create([
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'attachable_type' => 'test_case',
                    'attachable_id' => $testCase->id,
                    'uploaded_by' => session('user_id', 1)
                ]);
            }
        }

        return redirect()->route('test-cases.index')->with('success', 'Test case created successfully!');
    }

    public function show($id)
    {
        $testCase = TestCase::with(['project', 'testSuite', 'module', 'creator', 'assignee'])->findOrFail($id);
        
        // Get linked requirements
        $requirements = DB::select("
            SELECT r.* FROM requirements r
            JOIN requirement_test_cases rtc ON r.id = rtc.requirement_id
            WHERE rtc.test_case_id = ?
        ", [$id]);
        
        // Get execution history
        $executions = TestExecution::with('executor')
            ->where('test_case_id', $id)
            ->orderBy('executed_at', 'desc')
            ->get();
        
        // Get attachments
        $attachments = Attachment::where('attachable_type', 'test_case')
            ->where('attachable_id', $id)
            ->get();
        
        // Get comments
        $comments = Comment::with('user')
            ->where('commentable_type', 'test_case')
            ->where('commentable_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('test-cases.show', compact('testCase', 'requirements', 'executions', 'attachments', 'comments'));
    }

    public function edit($id)
    {
        $testCase = TestCase::findOrFail($id);
        $projects = Project::where('is_active', 1)->get();
        $testSuites = TestSuite::all();
        $modules = Module::where('is_active', 1)->get();
        $requirements = Requirement::all();
        $users = DB::select("SELECT id, name FROM users WHERE is_active = 1 AND role IN ('tester', 'developer')");
        
        // Get linked requirements
        $linkedRequirements = DB::select("
            SELECT requirement_id FROM requirement_test_cases WHERE test_case_id = ?
        ", [$id]);
        $linkedRequirementIds = array_column($linkedRequirements, 'requirement_id');
        
        return view('test-cases.edit', compact('testCase', 'projects', 'testSuites', 'modules', 'requirements', 'users', 'linkedRequirementIds'));
    }

    public function update(Request $request, $id)
    {
        $testCase = TestCase::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'test_suite_id' => 'nullable|exists:test_suites,id',
            'module_id' => 'nullable|exists:modules,id',
            'priority' => 'required|in:low,medium,high,critical',
            'type' => 'required|in:functional,integration,system,acceptance,performance,security',
            'complexity' => 'required|in:low,medium,high',
            'status' => 'required|in:draft,review,approved,active,deprecated',
            'preconditions' => 'nullable|string',
            'test_steps' => 'required|string',
            'expected_result' => 'required|string',
            'postconditions' => 'nullable|string',
            'test_data' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_time' => 'nullable|integer|min:1',
            'tags' => 'nullable|string',
            'requirements' => 'nullable|array',
            'requirements.*' => 'exists:requirements,id'
        ]);

        $testCase->update($validated);

        // Update requirement links
        DB::delete("DELETE FROM requirement_test_cases WHERE test_case_id = ?", [$id]);
        if (!empty($validated['requirements'])) {
            foreach ($validated['requirements'] as $requirementId) {
                DB::insert("INSERT INTO requirement_test_cases (requirement_id, test_case_id, created_at, updated_at) VALUES (?, ?, datetime('now'), datetime('now'))", 
                    [$requirementId, $testCase->id]);
            }
        }

        return redirect()->route('test-cases.show', $id)->with('success', 'Test case updated successfully!');
    }

    public function destroy($id)
    {
        $testCase = TestCase::findOrFail($id);
        
        // Delete related data
        DB::delete("DELETE FROM requirement_test_cases WHERE test_case_id = ?", [$id]);
        DB::delete("DELETE FROM test_executions WHERE test_case_id = ?", [$id]);
        DB::delete("DELETE FROM attachments WHERE attachable_type = 'test_case' AND attachable_id = ?", [$id]);
        DB::delete("DELETE FROM comments WHERE commentable_type = 'test_case' AND commentable_id = ?", [$id]);
        
        $testCase->delete();
        
        return redirect()->route('test-cases.index')->with('success', 'Test case deleted successfully!');
    }

    public function execute($id)
    {
        $testCase = TestCase::with(['project', 'module'])->findOrFail($id);
        $environments = DB::select("SELECT * FROM test_environments WHERE project_id = ? AND is_active = 1", [$testCase->project_id]);
        
        return view('test-cases.execute', compact('testCase', 'environments'));
    }

    public function storeExecution(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:passed,failed,blocked,skipped',
            'actual_result' => 'nullable|string',
            'comments' => 'nullable|string',
            'environment_id' => 'nullable|exists:test_environments,id',
            'browser' => 'nullable|string',
            'os' => 'nullable|string',
            'build_version' => 'nullable|string',
            'execution_time' => 'nullable|integer|min:1'
        ]);

        $validated['test_case_id'] = $id;
        $validated['executed_by'] = session('user_id', 1);
        $validated['executed_at'] = now();

        $execution = TestExecution::create($validated);

        // Auto-create defect for failed tests
        if ($validated['status'] === 'failed') {
            $testCase = TestCase::findOrFail($id);
            
            Defect::create([
                'title' => 'Test Failure: ' . $testCase->title,
                'description' => 'Automated defect created from failed test execution',
                'severity' => 'medium',
                'priority' => 'medium',
                'status' => 'open',
                'project_id' => $testCase->project_id,
                'module_id' => $testCase->module_id,
                'test_case_id' => $id,
                'test_execution_id' => $execution->id,
                'reported_by' => session('user_id', 1),
                'steps_to_reproduce' => $testCase->test_steps,
                'expected_behavior' => $testCase->expected_result,
                'actual_behavior' => $validated['actual_result'],
                'environment' => $request->input('environment_id') ? 'Environment ID: ' . $request->input('environment_id') : null,
                'browser' => $validated['browser'],
                'os' => $validated['os']
            ]);
        }

        return redirect()->route('test-cases.show', $id)->with('success', 'Test execution recorded successfully!');
    }

    public function clone($id)
    {
        $originalTestCase = TestCase::findOrFail($id);
        
        $newTestCase = $originalTestCase->replicate();
        $newTestCase->title = 'Copy of ' . $originalTestCase->title;
        $newTestCase->status = 'draft';
        $newTestCase->created_by = session('user_id', 1);
        $newTestCase->parent_id = $id;
        $newTestCase->save();

        // Copy requirement links
        $requirements = DB::select("SELECT requirement_id FROM requirement_test_cases WHERE test_case_id = ?", [$id]);
        foreach ($requirements as $req) {
            DB::insert("INSERT INTO requirement_test_cases (requirement_id, test_case_id, created_at, updated_at) VALUES (?, ?, datetime('now'), datetime('now'))", 
                [$req->requirement_id, $newTestCase->id]);
        }

        return redirect()->route('test-cases.edit', $newTestCase->id)->with('success', 'Test case cloned successfully!');
    }

    public function addComment(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        Comment::create([
            'content' => $validated['content'],
            'commentable_type' => 'test_case',
            'commentable_id' => $id,
            'user_id' => session('user_id', 1)
        ]);

        return redirect()->route('test-cases.show', $id)->with('success', 'Comment added successfully!');
    }
}
