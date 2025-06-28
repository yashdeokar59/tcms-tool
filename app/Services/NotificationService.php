<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\TestExecution;
use App\Models\Defect;
use App\Models\TestRun;

class NotificationService
{
    public function notifyTestFailure(TestExecution $execution)
    {
        $testCase = $execution->testCase;
        $project = $testCase->project;
        
        // Notify developers assigned to the module
        if ($testCase->module) {
            $developers = $project->users()
                ->where('role', User::ROLE_DEVELOPER)
                ->get();
                
            foreach ($developers as $developer) {
                $this->createNotification($developer, [
                    'title' => 'Test Case Failed',
                    'message' => "Test case '{$testCase->title}' failed in module '{$testCase->module->name}'",
                    'type' => 'error',
                    'data' => [
                        'test_case_id' => $testCase->id,
                        'execution_id' => $execution->id,
                        'project_id' => $project->id,
                    ],
                    'action_url' => route('test-cases.show', $testCase),
                ]);
            }
        }
        
        // Notify project manager
        if ($project->manager) {
            $this->createNotification($project->manager, [
                'title' => 'Test Failure Alert',
                'message' => "Test case '{$testCase->title}' failed in project '{$project->name}'",
                'type' => 'warning',
                'data' => [
                    'test_case_id' => $testCase->id,
                    'execution_id' => $execution->id,
                    'project_id' => $project->id,
                ],
                'action_url' => route('test-cases.show', $testCase),
            ]);
        }
    }

    public function notifyDefectAssignment(Defect $defect)
    {
        if ($defect->assignee) {
            $this->createNotification($defect->assignee, [
                'title' => 'New Defect Assigned',
                'message' => "You have been assigned defect: '{$defect->title}'",
                'type' => 'info',
                'data' => [
                    'defect_id' => $defect->id,
                    'project_id' => $defect->project_id,
                ],
                'action_url' => route('defects.show', $defect),
            ]);
        }
    }

    public function notifyDefectResolution(Defect $defect)
    {
        // Notify reporter
        if ($defect->reporter) {
            $this->createNotification($defect->reporter, [
                'title' => 'Defect Resolved',
                'message' => "Defect '{$defect->title}' has been resolved",
                'type' => 'success',
                'data' => [
                    'defect_id' => $defect->id,
                    'project_id' => $defect->project_id,
                ],
                'action_url' => route('defects.show', $defect),
            ]);
        }

        // Notify project manager
        if ($defect->project->manager) {
            $this->createNotification($defect->project->manager, [
                'title' => 'Defect Resolved',
                'message' => "Defect '{$defect->title}' has been resolved in project '{$defect->project->name}'",
                'type' => 'success',
                'data' => [
                    'defect_id' => $defect->id,
                    'project_id' => $defect->project_id,
                ],
                'action_url' => route('defects.show', $defect),
            ]);
        }
    }

    public function notifyTestRunCompletion(TestRun $testRun)
    {
        $progress = $testRun->progress;
        
        // Notify assigned tester
        if ($testRun->assignee) {
            $this->createNotification($testRun->assignee, [
                'title' => 'Test Run Completed',
                'message' => "Test run '{$testRun->name}' has been completed with {$progress['passed']} passed, {$progress['failed']} failed",
                'type' => $progress['failed'] > 0 ? 'warning' : 'success',
                'data' => [
                    'test_run_id' => $testRun->id,
                    'project_id' => $testRun->project_id,
                    'progress' => $progress,
                ],
                'action_url' => route('test-runs.show', $testRun),
            ]);
        }

        // Notify project manager
        if ($testRun->project->manager) {
            $this->createNotification($testRun->project->manager, [
                'title' => 'Test Run Completed',
                'message' => "Test run '{$testRun->name}' completed in project '{$testRun->project->name}'",
                'type' => 'info',
                'data' => [
                    'test_run_id' => $testRun->id,
                    'project_id' => $testRun->project_id,
                    'progress' => $progress,
                ],
                'action_url' => route('test-runs.show', $testRun),
            ]);
        }
    }

    public function notifyUpcomingDeadline($testCycle)
    {
        $daysUntilDeadline = now()->diffInDays($testCycle->end_date);
        
        // Notify assigned team members
        if ($testCycle->assignee) {
            $this->createNotification($testCycle->assignee, [
                'title' => 'Upcoming Deadline',
                'message' => "Test cycle '{$testCycle->name}' deadline is in {$daysUntilDeadline} days",
                'type' => $daysUntilDeadline <= 2 ? 'error' : 'warning',
                'data' => [
                    'test_cycle_id' => $testCycle->id,
                    'project_id' => $testCycle->project_id,
                    'days_until_deadline' => $daysUntilDeadline,
                ],
                'action_url' => route('test-cycles.show', $testCycle),
            ]);
        }

        // Notify project manager
        if ($testCycle->project->manager) {
            $this->createNotification($testCycle->project->manager, [
                'title' => 'Test Cycle Deadline Approaching',
                'message' => "Test cycle '{$testCycle->name}' deadline is approaching ({$daysUntilDeadline} days)",
                'type' => 'warning',
                'data' => [
                    'test_cycle_id' => $testCycle->id,
                    'project_id' => $testCycle->project_id,
                    'days_until_deadline' => $daysUntilDeadline,
                ],
                'action_url' => route('test-cycles.show', $testCycle),
            ]);
        }
    }

    public function notifyTestCaseAssignment($testCase, $previousAssignee = null)
    {
        if ($testCase->assignee && $testCase->assignee->id !== $previousAssignee?->id) {
            $this->createNotification($testCase->assignee, [
                'title' => 'Test Case Assigned',
                'message' => "You have been assigned test case: '{$testCase->title}'",
                'type' => 'info',
                'data' => [
                    'test_case_id' => $testCase->id,
                    'project_id' => $testCase->project_id,
                ],
                'action_url' => route('test-cases.show', $testCase),
            ]);
        }
    }

    public function getUserNotifications(User $user, $limit = 10)
    {
        return $user->notifications()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(User $user, $notificationId = null)
    {
        if ($notificationId) {
            $user->notifications()
                ->where('id', $notificationId)
                ->update(['read_at' => now()]);
        } else {
            $user->notifications()
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    private function createNotification(User $user, array $data)
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'],
            'data' => $data['data'] ?? null,
            'action_url' => $data['action_url'] ?? null,
        ]);
    }
}
