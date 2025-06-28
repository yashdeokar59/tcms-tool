<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestEnvironment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'url',
        'type',
        'status',
        'project_id',
        'configuration',
        'is_active'
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class, 'environment_id');
    }
}
