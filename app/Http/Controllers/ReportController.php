<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestExecution;
use App\Models\Defect;
use App\Models\TestRun;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isManager() && !Auth::user()->isAdmin()) {
                abort(403, 'Access denied. Manager or Admin role required.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        
        return view('reports.index', compact('projects'));
    }

    public function testExecution(Request $request)
    {
        $projectId = $request->get('project_id');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $currentProject = $projectId ? $projects->find($projectId) : $projects->first();
        
        if (!$currentProject) {
            return redirect()->route('reports.index')
                ->with('error', 'Please select a project.');
        }

        // Get execution data
        $executions = TestExecution::whereHas('testCase', function($q) use ($currentProject) {
            $q->where('project_id', $currentProject->id);
        })
        ->whereBetween('executed_at', [$dateFrom, $dateTo])
        ->with(['testCase', 'executor'])
        ->get();

        // Calculate metrics
        $metrics = [
            'total_executions' => $executions->count(),
            'passed' => $executions->where('status', 'passed')->count(),
            'failed' => $executions->where('status', 'failed')->count(),
            'blocked' => $executions->where('status', 'blocked')->count(),
            'skipped' => $executions->where('status', 'skipped')->count(),
        ];

        $metrics['pass_rate'] = $metrics['total_executions'] > 0 
            ? round($metrics['passed'] / $metrics['total_executions'] * 100, 2) 
            : 0;

        // Daily execution trends
        $dailyTrends = $executions->groupBy(function($execution) {
            return $execution->executed_at->format('Y-m-d');
        })->map(function($dayExecutions) {
            return [
                'total' => $dayExecutions->count(),
                'passed' => $dayExecutions->where('status', 'passed')->count(),
                'failed' => $dayExecutions->where('status', 'failed')->count(),
                'blocked' => $dayExecutions->where('status', 'blocked')->count(),
            ];
        });

        // Execution by tester
        $testerStats = $executions->groupBy('executed_by')->map(function($testerExecutions) {
            $tester = $testerExecutions->first()->executor;
            return [
                'tester' => $tester->name,
                'total' => $testerExecutions->count(),
                'passed' => $testerExecutions->where('status', 'passed')->count(),
                'failed' => $testerExecutions->where('status', 'failed')->count(),
                'pass_rate' => $testerExecutions->count() > 0 
                    ? round($testerExecutions->where('status', 'passed')->count() / $testerExecutions->count() * 100, 2)
                    : 0,
            ];
        });

        return view('reports.test-execution', compact(
            'currentProject', 'projects', 'metrics', 'dailyTrends', 
            'testerStats', 'dateFrom', 'dateTo'
        ));
    }

    public function defectAnalysis(Request $request)
    {
        $projectId = $request->get('project_id');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $currentProject = $projectId ? $projects->find($projectId) : $projects->first();
        
        if (!$currentProject) {
            return redirect()->route('reports.index')
                ->with('error', 'Please select a project.');
        }

        // Get defect data
        $defects = $currentProject->defects()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['module', 'reporter', 'assignee'])
            ->get();

        // Calculate metrics
        $metrics = [
            'total_defects' => $defects->count(),
            'open' => $defects->where('status', 'open')->count(),
            'in_progress' => $defects->where('status', 'in_progress')->count(),
            'resolved' => $defects->where('status', 'resolved')->count(),
            'closed' => $defects->where('status', 'closed')->count(),
        ];

        // Defect trends
        $defectTrends = $defects->groupBy(function($defect) {
            return $defect->created_at->format('Y-m-d');
        })->map->count();

        // Defect by severity
        $severityDistribution = $defects->groupBy('severity')->map->count();

        // Defect by module
        $moduleDistribution = $defects->groupBy('module.name')->map->count();

        // Average resolution time
        $resolvedDefects = $defects->whereNotNull('resolved_at');
        $avgResolutionTime = $resolvedDefects->count() > 0 
            ? $resolvedDefects->avg(function($defect) {
                return $defect->created_at->diffInHours($defect->resolved_at);
            })
            : 0;

        // Defect density (defects per test case)
        $totalTestCases = $currentProject->testCases()->count();
        $defectDensity = $totalTestCases > 0 ? round($defects->count() / $totalTestCases, 2) : 0;

        return view('reports.defect-analysis', compact(
            'currentProject', 'projects', 'metrics', 'defectTrends',
            'severityDistribution', 'moduleDistribution', 'avgResolutionTime',
            'defectDensity', 'dateFrom', 'dateTo'
        ));
    }

    public function testCoverage(Request $request)
    {
        $projectId = $request->get('project_id');
        
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $currentProject = $projectId ? $projects->find($projectId) : $projects->first();
        
        if (!$currentProject) {
            return redirect()->route('reports.index')
                ->with('error', 'Please select a project.');
        }

        // Requirements coverage
        $requirementsCoverage = $currentProject->test_coverage;

        // Module coverage
        $modules = $currentProject->modules()->with(['testCases', 'requirements'])->get();
        $moduleCoverage = $modules->map(function($module) {
            $totalRequirements = $module->requirements->count();
            $coveredRequirements = $module->requirements()
                ->whereHas('testCases')
                ->count();
            
            return [
                'module' => $module->name,
                'total_requirements' => $totalRequirements,
                'covered_requirements' => $coveredRequirements,
                'coverage_percentage' => $totalRequirements > 0 
                    ? round($coveredRequirements / $totalRequirements * 100, 2)
                    : 0,
                'test_cases_count' => $module->testCases->count(),
            ];
        });

        // Test case execution coverage
        $totalTestCases = $currentProject->testCases()->count();
        $executedTestCases = $currentProject->testCases()
            ->whereHas('executions', function($q) {
                $q->where('executed_at', '>=', now()->subDays(30));
            })->count();

        $executionCoverage = [
            'total' => $totalTestCases,
            'executed' => $executedTestCases,
            'percentage' => $totalTestCases > 0 
                ? round($executedTestCases / $totalTestCases * 100, 2)
                : 0,
        ];

        return view('reports.test-coverage', compact(
            'currentProject', 'projects', 'requirementsCoverage',
            'moduleCoverage', 'executionCoverage'
        ));
    }

    public function teamPerformance(Request $request)
    {
        $projectId = $request->get('project_id');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : $user->projects;
        $currentProject = $projectId ? $projects->find($projectId) : $projects->first();
        
        if (!$currentProject) {
            return redirect()->route('reports.index')
                ->with('error', 'Please select a project.');
        }

        // Get team members
        $teamMembers = $currentProject->users()->with([
            'testExecutions' => function($q) use ($currentProject, $dateFrom, $dateTo) {
                $q->whereHas('testCase', function($sq) use ($currentProject) {
                    $sq->where('project_id', $currentProject->id);
                })->whereBetween('executed_at', [$dateFrom, $dateTo]);
            },
            'createdTestCases' => function($q) use ($currentProject) {
                $q->where('project_id', $currentProject->id);
            },
            'assignedDefects' => function($q) use ($currentProject, $dateFrom, $dateTo) {
                $q->where('project_id', $currentProject->id)
                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
            }
        ])->get();

        $performanceData = $teamMembers->map(function($member) {
            $executions = $member->testExecutions;
            $passRate = $executions->count() > 0 
                ? round($executions->where('status', 'passed')->count() / $executions->count() * 100, 2)
                : 0;

            return [
                'name' => $member->name,
                'role' => $member->role,
                'test_cases_created' => $member->createdTestCases->count(),
                'test_executions' => $executions->count(),
                'pass_rate' => $passRate,
                'defects_assigned' => $member->assignedDefects->count(),
                'defects_resolved' => $member->assignedDefects->where('status', 'resolved')->count(),
            ];
        });

        return view('reports.team-performance', compact(
            'currentProject', 'projects', 'performanceData', 'dateFrom', 'dateTo'
        ));
    }

    public function export(Request $request)
    {
        $reportType = $request->get('type');
        $format = $request->get('format', 'pdf');
        
        // Generate report based on type and format
        switch ($reportType) {
            case 'test-execution':
                return $this->exportTestExecution($request, $format);
            case 'defect-analysis':
                return $this->exportDefectAnalysis($request, $format);
            case 'test-coverage':
                return $this->exportTestCoverage($request, $format);
            case 'team-performance':
                return $this->exportTeamPerformance($request, $format);
            default:
                return back()->with('error', 'Invalid report type.');
        }
    }

    private function exportTestExecution(Request $request, string $format)
    {
        // Implementation for exporting test execution report
        // This would use libraries like DomPDF, PhpSpreadsheet, etc.
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    private function exportDefectAnalysis(Request $request, string $format)
    {
        // Implementation for exporting defect analysis report
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    private function exportTestCoverage(Request $request, string $format)
    {
        // Implementation for exporting test coverage report
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    private function exportTeamPerformance(Request $request, string $format)
    {
        // Implementation for exporting team performance report
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}
