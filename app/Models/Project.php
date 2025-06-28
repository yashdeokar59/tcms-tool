<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'created_by',
        'manager_id',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')->withPivot('role', 'permissions');
    }

    public function testSuites(): HasMany
    {
        return $this->hasMany(TestSuite::class);
    }

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class);
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class);
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    public function environments(): HasMany
    {
        return $this->hasMany(TestEnvironment::class);
    }

    public function testCycles(): HasMany
    {
        return $this->hasMany(TestCycle::class);
    }

    // Helper methods
    public function getTestCoverageAttribute(): array
    {
        $totalRequirements = $this->requirements()->count();
        $coveredRequirements = $this->requirements()
            ->whereHas('testCases')
            ->count();

        return [
            'total' => $totalRequirements,
            'covered' => $coveredRequirements,
            'percentage' => $totalRequirements > 0 ? round($coveredRequirements / $totalRequirements * 100, 2) : 0
        ];
    }

    public function getDefectTrendsAttribute(): array
    {
        return [
            'open' => $this->defects()->where('status', 'open')->count(),
            'in_progress' => $this->defects()->where('status', 'in_progress')->count(),
            'resolved' => $this->defects()->where('status', 'resolved')->count(),
            'closed' => $this->defects()->where('status', 'closed')->count(),
        ];
    }
}
