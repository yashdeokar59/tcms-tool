<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_TESTER = 'tester';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'preferences'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'preferences' => 'array'
        ];
    }

    // Relationships
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users')->withPivot('role', 'permissions');
    }

    public function createdTestCases(): HasMany
    {
        return $this->hasMany(TestCase::class, 'created_by');
    }

    public function assignedTestCases(): HasMany
    {
        return $this->hasMany(TestCase::class, 'assigned_to');
    }

    public function testExecutions(): HasMany
    {
        return $this->hasMany(TestExecution::class, 'executed_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Helper methods
    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(self::ROLE_MANAGER);
    }

    public function isDeveloper(): bool
    {
        return $this->hasRole(self::ROLE_DEVELOPER);
    }

    public function isTester(): bool
    {
        return $this->hasRole(self::ROLE_TESTER);
    }

    public function canManageProject($projectId): bool
    {
        if ($this->isAdmin()) return true;
        
        return $this->projects()
            ->where('project_id', $projectId)
            ->wherePivot('role', 'manager')
            ->exists();
    }
}
