<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $fromDate, $toDate)
    {
        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query;
    }

    /**
     * Scope for filtering by user
     */
    public function scopeFilterByUser($query, $userId)
    {
        if ($userId && $userId !== 'all') {
            return $query->where('user_id', $userId);
        }

        return $query;
    }

    /**
     * Scope for filtering by action type
     */
    public function scopeFilterByAction($query, $action)
    {
        if ($action && $action !== 'all') {
            return $query->where('action', $action);
        }

        return $query;
    }

    /**
     * Get truncated user agent for display
     */
    public function getShortUserAgentAttribute()
    {
        if (strlen($this->user_agent) > 50) {
            return substr($this->user_agent, 0, 50) . '...';
        }

        return $this->user_agent;
    }
}
