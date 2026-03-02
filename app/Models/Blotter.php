<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blotter extends Model
{
    use HasFactory;

    protected $fillable = [
        'blotter_number',
        'complainant_id',
        'complainant_name',
        'complainant_address',
        'complainant_contact',
        'respondent_name',
        'respondent_address',
        'incident_type',
        'incident_date',
        'incident_location',
        'complaint_details',
        'witnesses',
        'evidence',
        'status',
        'resolution',
        'settlement_date',
        'settled_by',
        'remarks',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'settlement_date' => 'date',
    ];

    /**
     * Get the complainant resident
     */
    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainant_id');
    }

    /**
     * Get the user who settled the case
     */
    public function settledBy()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    /**
     * Get the complainant name attribute
     */
    public function getComplainantNameAttribute()
    {
        return $this->complainant ? $this->complainant->full_name : $this->attributes['complainant_name'] ?? 'N/A';
    }

    /**
     * Scope a query to only include pending blotters
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include active blotters (not settled/closed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['Pending', 'Investigating', 'Hearings']);
    }

    /**
     * Scope a query to only include settled blotters
     */
    public function scopeSettled($query)
    {
        return $query->where('status', 'Settled');
    }
}
