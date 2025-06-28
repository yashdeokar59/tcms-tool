<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Defect extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'severity',
        'priority',
        'status',
        'project_id',
        'module_id',
        'test_case_id',
        'test_execution_id',
        'reported_by',
        'assigned_to',
        'steps_to_reproduce',
        'expected_behavior',
        'actual_behavior',
        'environment',
        'browser',
        'os',
        'resolution',
        'resolved_at',
        'verified_by',
        'verified_at',
        'tags'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'verified_at' => 'datetime',
        'environment' => 'array',
        'tags' => 'array'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function testCase(): BelongsTo
    {
        return $this->belongsTo(TestCase::class);
    }

    public function testExecution(): BelongsTo
    {
        return $this->belongsTo(TestExecution::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function isOverdue(): bool
    {
        // Define SLA based on priority
        $slaHours = match($this->priority) {
            'critical' => 4,
            'high' => 24,
            'medium' => 72,
            'low' => 168,
            default => 72
        };

        return $this->created_at->addHours($slaHours)->isPast() && 
               !in_array($this->status, ['resolved', 'closed']);
    }
}
