<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\TestEnvironment;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                abort(403, 'Access denied. Admin role required.');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('is_active', true)->count(),
        ];

        $recentUsers = User::latest()->limit(5)->get();
        $recentProjects = Project::latest()->limit(5)->with('creator')->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentProjects'));
    }

    // User Management
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,developer,tester',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.users')
            ->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,manager,developer,tester',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }

    // Project Management
    public function projects(Request $request)
    {
        $query = Project::with(['creator', 'manager']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->paginate(20);

        return view('admin.projects.index', compact('projects'));
    }

    public function createProject()
    {
        $users = User::where('is_active', true)->get();
        return view('admin.projects.create', compact('users'));
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);

        Project::create($validated);

        return redirect()->route('admin.projects')
            ->with('success', 'Project created successfully.');
    }

    public function editProject(Project $project)
    {
        $users = User::where('is_active', true)->get();
        return view('admin.projects.edit', compact('project', 'users'));
    }

    public function updateProject(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $project->update($validated);

        return redirect()->route('admin.projects')
            ->with('success', 'Project updated successfully.');
    }

    public function deleteProject(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects')
            ->with('success', 'Project deleted successfully.');
    }

    // Environment Management
    public function environments(Request $request)
    {
        $query = TestEnvironment::with('project');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $environments = $query->paginate(20);
        $projects = Project::where('is_active', true)->get();

        return view('admin.environments.index', compact('environments', 'projects'));
    }

    public function createEnvironment()
    {
        $projects = Project::where('is_active', true)->get();
        return view('admin.environments.create', compact('projects'));
    }

    public function storeEnvironment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'type' => 'required|in:development,testing,staging,production',
            'status' => 'required|in:active,inactive,maintenance',
            'project_id' => 'required|exists:projects,id',
            'configuration' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        TestEnvironment::create($validated);

        return redirect()->route('admin.environments')
            ->with('success', 'Environment created successfully.');
    }

    public function editEnvironment(TestEnvironment $environment)
    {
        $projects = Project::where('is_active', true)->get();
        return view('admin.environments.edit', compact('environment', 'projects'));
    }

    public function updateEnvironment(Request $request, TestEnvironment $environment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'type' => 'required|in:development,testing,staging,production',
            'status' => 'required|in:active,inactive,maintenance',
            'configuration' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $environment->update($validated);

        return redirect()->route('admin.environments')
            ->with('success', 'Environment updated successfully.');
    }

    public function deleteEnvironment(TestEnvironment $environment)
    {
        $environment->delete();

        return redirect()->route('admin.environments')
            ->with('success', 'Environment deleted successfully.');
    }

    // System Settings
    public function settings()
    {
        // This would load system-wide settings from a config table or file
        $settings = [
            'app_name' => config('app.name'),
            'default_timezone' => config('app.timezone'),
            'max_file_size' => '10MB',
            'allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'default_timezone' => 'required|string',
            'max_file_size' => 'required|string',
            'allowed_file_types' => 'required|string',
        ]);

        // Save settings to database or config file
        // Implementation depends on how you want to store settings

        return back()->with('success', 'Settings updated successfully.');
    }

    // System Monitoring
    public function systemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
        ];

        $usage = [
            'users_count' => User::count(),
            'projects_count' => Project::count(),
            'test_cases_count' => \App\Models\TestCase::count(),
            'defects_count' => \App\Models\Defect::count(),
        ];

        return view('admin.system-health', compact('health', 'usage'));
    }

    private function checkDatabaseHealth(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function checkStorageHealth(): array
    {
        try {
            $diskSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercentage = round((($totalSpace - $diskSpace) / $totalSpace) * 100, 2);
            
            return [
                'status' => $usedPercentage > 90 ? 'warning' : 'healthy',
                'message' => "Storage usage: {$usedPercentage}%",
                'free_space' => $this->formatBytes($diskSpace),
                'total_space' => $this->formatBytes($totalSpace),
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Storage check failed: ' . $e->getMessage()];
        }
    }

    private function checkCacheHealth(): array
    {
        try {
            cache()->put('health_check', 'test', 60);
            $value = cache()->get('health_check');
            
            return [
                'status' => $value === 'test' ? 'healthy' : 'error',
                'message' => $value === 'test' ? 'Cache is working' : 'Cache test failed',
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache check failed: ' . $e->getMessage()];
        }
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
