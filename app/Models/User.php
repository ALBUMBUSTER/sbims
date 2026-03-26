<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

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
        'last_login',
        'security_question',
        'security_answer',
        'term_end_date', // ADD THIS
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'security_answer',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'role_id' => 'integer',
        'term_end_date' => 'date', // ADD THIS
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->name) && !empty($user->full_name)) {
                $user->name = $user->full_name;
            }
        });

        static::updating(function ($user) {
            if (empty($user->name) && !empty($user->full_name)) {
                $user->name = $user->full_name;
            }
        });
    }

    public function setSecurityAnswerAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['security_answer'] = Hash::make($value);
        }
    }

    public function verifySecurityAnswer($answer)
    {
        return Hash::check($answer, $this->security_answer);
    }

    public function username()
    {
        return 'username';
    }

    public function getRoleAttribute()
    {
        return $this->getRoleName($this->role_id);
    }

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

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

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->where('is_read', false);
    }

    public function readNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->where('is_read', true);
    }

    /**
     * Check if captain's term is ending soon (within 30 days)
     */
    public function isTermEndingSoon()
    {
        if ($this->role_id != 2 || !$this->term_end_date) {
            return false;
        }
        $daysLeft = now()->diffInDays($this->term_end_date, false);
        return $daysLeft <= 30 && $daysLeft > 0;
    }

    /**
 * Get term end date formatted for display
 */
public function getTermEndDateFormatted()
{
    if (!$this->term_end_date) {
        return null;
    }
    return \Carbon\Carbon::parse($this->term_end_date)->format('F d, Y');
}
    /**
     * Get days left until term ends
     */
    public function getDaysLeftInTerm()
    {
        if ($this->role_id != 2 || !$this->term_end_date) {
            return null;
        }
        return now()->diffInDays($this->term_end_date, false);
    }
    /**
 * Calculate term end date for captain (4 years from given date)
 */
public static function calculateCaptainTermEndDate($startDate = null)
{
    $start = $startDate ? \Carbon\Carbon::parse($startDate) : now();
    return $start->addYears(4)->format('Y-m-d');
}
}
