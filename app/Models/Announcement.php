<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'posted_by',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Scope for active announcements
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
