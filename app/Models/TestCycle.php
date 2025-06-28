<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'assigned_to',
        'build_version',
        'environment_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(TestEnvironment::class, 'environment_id');
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class, 'cycle_id');
    }

    public function getProgressAttribute(): array
    {
        $total = $this->testRuns()->count();
        $passed = $this->testRuns()->where('status', 'passed')->count();
        $failed = $this->testRuns()->where('status', 'failed')->count();
        $blocked = $this->testRuns()->where('status', 'blocked')->count();
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
}
