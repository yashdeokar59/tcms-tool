<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TestRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'test_suite_id',
        'cycle_id',
        'environment_id',
        'status',
        'started_at',
        'completed_at',
        'created_by',
        'assigned_to',
        'build_version',
        'configuration'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'configuration' => 'array'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function testSuite(): BelongsTo
    {
        return $this->belongsTo(TestSuite::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(TestCycle::class, 'cycle_id');
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(TestEnvironment::class, 'environment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(TestExecution::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Helper methods
    public function getProgressAttribute(): array
    {
        $total = $this->executions()->count();
        $passed = $this->executions()->where('status', 'passed')->count();
        $failed = $this->executions()->where('status', 'failed')->count();
        $blocked = $this->executions()->where('status', 'blocked')->count();
        $pending = $total - $passed - $failed - $blocked;

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'blocked' => $blocked,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($passed + $failed + $blocked) / $total * 100, 2) : 0
        ];
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->completed_at->diffInMinutes($this->started_at);
        }
        return null;
    }
}
