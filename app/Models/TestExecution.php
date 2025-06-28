<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TestExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_run_id',
        'test_case_id',
        'status',
        'actual_result',
        'comments',
        'executed_at',
        'executed_by',
        'execution_time',
        'browser',
        'os',
        'build_version',
        'environment_data'
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'environment_data' => 'array'
    ];

    public function testRun(): BelongsTo
    {
        return $this->belongsTo(TestRun::class);
    }

    public function testCase(): BelongsTo
    {
        return $this->belongsTo(TestCase::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Helper methods
    public function createDefectFromFailure(array $defectData = []): Defect
    {
        $defaultData = [
            'title' => 'Test Case Failed: ' . $this->testCase->title,
            'description' => $this->actual_result,
            'severity' => 'medium',
            'priority' => 'medium',
            'status' => 'open',
            'project_id' => $this->testCase->project_id,
            'test_case_id' => $this->test_case_id,
            'test_execution_id' => $this->id,
            'reported_by' => $this->executed_by,
            'steps_to_reproduce' => json_encode($this->testCase->test_steps),
            'environment' => $this->environment_data
        ];

        return Defect::create(array_merge($defaultData, $defectData));
    }
}
