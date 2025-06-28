<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSuite extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'created_by'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class);
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class);
    }
}
