<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TestCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'preconditions',
        'test_steps',
        'expected_result',
        'priority',
        'type',
        'status',
        'test_suite_id',
        'project_id',
        'module_id',
        'created_by',
        'assigned_to',
        'tags',
        'test_data',
        'postconditions',
        'automation_status',
        'estimated_time',
        'complexity',
        'is_template'
    ];

    protected $casts = [
        'test_steps' => 'array',
        'tags' => 'array',
        'test_data' => 'array',
        'is_template' => 'boolean'
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function testSuite(): BelongsTo
    {
        return $this->belongsTo(TestSuite::class);
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

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }

    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(Requirement::class, 'requirement_test_cases');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TestCase::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(TestCase::class, 'parent_id');
    }

    // Helper methods
    public function clone(): TestCase
    {
        $clone = $this->replicate();
        $clone->title = $this->title . ' (Copy)';
        $clone->status = 'draft';
        $clone->save();

        // Clone attachments
        foreach ($this->attachments as $attachment) {
            $clonedAttachment = $attachment->replicate();
            $clone->attachments()->save($clonedAttachment);
        }

        return $clone;
    }

    public function getLastExecutionAttribute()
    {
        return $this->executions()->latest()->first();
    }

    public function getExecutionStatusAttribute(): string
    {
        $lastExecution = $this->getLastExecutionAttribute();
        return $lastExecution ? $lastExecution->status : 'not_executed';
    }
}
