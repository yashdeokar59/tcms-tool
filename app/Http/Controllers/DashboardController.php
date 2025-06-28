<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestExecution;
use App\Models\Defect;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get user role from session or default to 'tester'
        $userRole = $request->session()->get('user_role', 'tester');
        $userId = $request->session()->get('user_id', 1);

        // Common statistics
        $stats = [
            'total_projects' => Project::where('is_active', 1)->count(),
            'total_test_cases' => TestCase::count(),
            'total_executions' => TestExecution::count(),
            'total_defects' => Defect::count(),
        ];

        // Role-specific data
        switch ($userRole) {
            case 'admin':
                return $this->adminDashboard($stats);
            case 'manager':
                return $this->managerDashboard($stats);
            case 'developer':
                return $this->developerDashboard($stats, $userId);
            default:
                return $this->testerDashboard($stats, $userId);
        }
    }

    private function adminDashboard($stats)
    {
        $additionalStats = [
            'total_users' => User::where('is_active', 1)->count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'system_health' => 'Good',
            'recent_activities' => $this->getRecentActivities(10)
        ];

        return view('dashboard.admin', array_merge($stats, $additionalStats));
    }

    private function managerDashboard($stats)
    {
        // Test execution progress
        $executionStats = DB::select("
            SELECT 
                status,
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM test_executions), 2) as percentage
            FROM test_executions 
            GROUP BY status
        ");

        // Defect trends (last 30 days)
        $defectTrends = DB::select("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM defects 
            WHERE created_at >= DATE('now', '-30 days')
            GROUP BY DATE(created_at)
            ORDER BY date
        ");

        // Team performance
        $teamPerformance = DB::select("
            SELECT 
                u.name,
                COUNT(te.id) as executions,
                SUM(CASE WHEN te.status = 'passed' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN te.status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM users u
            LEFT JOIN test_executions te ON u.id = te.executed_by
            WHERE u.role IN ('tester', 'developer')
            GROUP BY u.id, u.name
            ORDER BY executions DESC
            LIMIT 10
        ");

        return view('dashboard.manager', [
            'stats' => $stats,
            'execution_stats' => $executionStats,
            'defect_trends' => $defectTrends,
            'team_performance' => $teamPerformance
        ]);
    }

    private function developerDashboard($stats, $userId)
    {
        // Failed tests assigned to developer
        $failedTests = DB::select("
            SELECT 
                tc.title,
                tc.id,
                te.actual_result,
                te.executed_at,
                p.name as project_name
            FROM test_cases tc
            JOIN test_executions te ON tc.id = te.test_case_id
            JOIN projects p ON tc.project_id = p.id
            WHERE te.status = 'failed' 
            AND tc.assigned_to = ?
            ORDER BY te.executed_at DESC
            LIMIT 10
        ", [$userId]);

        // Bugs assigned to developer
        $assignedBugs = DB::select("
            SELECT 
                d.title,
                d.severity,
                d.status,
                d.created_at,
                p.name as project_name
            FROM defects d
            JOIN projects p ON d.project_id = p.id
            WHERE d.assigned_to = ?
            AND d.status NOT IN ('resolved', 'closed')
            ORDER BY d.created_at DESC
            LIMIT 10
        ", [$userId]);

        return view('dashboard.developer', [
            'stats' => $stats,
            'failed_tests' => $failedTests,
            'assigned_bugs' => $assignedBugs
        ]);
    }

    private function testerDashboard($stats, $userId)
    {
        // Recent test executions
        $recentExecutions = DB::select("
            SELECT 
                tc.title,
                te.status,
                te.executed_at,
                p.name as project_name
            FROM test_executions te
            JOIN test_cases tc ON te.test_case_id = tc.id
            JOIN projects p ON tc.project_id = p.id
            WHERE te.executed_by = ?
            ORDER BY te.executed_at DESC
            LIMIT 10
        ", [$userId]);

        // Assigned test cases
        $assignedTests = DB::select("
            SELECT 
                tc.title,
                tc.priority,
                tc.status,
                p.name as project_name
            FROM test_cases tc
            JOIN projects p ON tc.project_id = p.id
            WHERE tc.assigned_to = ?
            AND tc.status NOT IN ('completed', 'archived')
            ORDER BY tc.created_at DESC
            LIMIT 10
        ", [$userId]);

        return view('dashboard.tester', [
            'stats' => $stats,
            'recent_executions' => $recentExecutions,
            'assigned_tests' => $assignedTests
        ]);
    }

    private function getRecentActivities($limit = 10)
    {
        return DB::select("
            SELECT 
                'test_execution' as type,
                te.id,
                'Test executed: ' || tc.title as description,
                te.executed_at as created_at,
                u.name as user_name
            FROM test_executions te
            JOIN test_cases tc ON te.test_case_id = tc.id
            JOIN users u ON te.executed_by = u.id
            
            UNION ALL
            
            SELECT 
                'defect' as type,
                d.id,
                'Bug reported: ' || d.title as description,
                d.created_at,
                u.name as user_name
            FROM defects d
            JOIN users u ON d.reported_by = u.id
            
            ORDER BY created_at DESC
            LIMIT ?
        ", [$limit]);
    }

    public function switchRole(Request $request)
    {
        $role = $request->input('role');
        $validRoles = ['admin', 'manager', 'developer', 'tester'];
        
        if (in_array($role, $validRoles)) {
            $request->session()->put('user_role', $role);
            
            // Set appropriate user ID based on role
            $userIds = [
                'admin' => 1,
                'manager' => 2,
                'developer' => 3,
                'tester' => 4
            ];
            
            $request->session()->put('user_id', $userIds[$role] ?? 1);
        }
        
        return redirect()->route('dashboard');
    }
}
