<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'name',
        'full_name',
        'role_id',
        'is_active',
        'last_login'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'role_id' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // If name is empty but full_name is provided, set name from full_name
            if (empty($user->name) && !empty($user->full_name)) {
                $user->name = $user->full_name;
            }
        });

        static::updating(function ($user) {
            // If name is empty but full_name is provided, set name from full_name
            if (empty($user->name) && !empty($user->full_name)) {
                $user->name = $user->full_name;
            }
        });
    }
    // Use username for authentication instead of email
    public function username()
    {
        return 'username';
    }

    // Accessor to get role name from role_id
    public function getRoleAttribute()
    {
        return $this->getRoleName($this->role_id);
    }

    // Helper method to convert role_id to role name
    protected function getRoleName($roleId)
    {
        $roleMap = [
            1 => 'admin',
            2 => 'captain',
            3 => 'secretary',
            4 => 'clerk'
        ];

        return $roleMap[$roleId] ?? 'resident';
    }

    // Scope for active users
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Check if user has specific role
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    // Check if user has any of the given roles
    public function hasAnyRole($roles)
    {
        return in_array($this->role, (array)$roles);
    }

    public function getRoleNameAttribute()
    {
        $names = [
            1 => 'Admin',
            2 => 'Captain',
            3 => 'Secretary',
            4 => 'Clerk'
        ];

        return $names[$this->role_id] ?? 'Unknown';
    }

    /**
     * Get all notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Get unread notifications for this user
     */
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->where('is_read', false);
    }

    /**
     * Get read notifications for this user
     */
    public function readNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->where('is_read', true);
    }
}
