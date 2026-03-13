<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blotter extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'case_id',
        'complainant_id',
        'respondent_name',
        'respondent_address',
        'incident_type',
        'incident_date',
        'incident_location',
        'description',
        'status',
        'resolution',
        'resolved_date',
        'handled_by',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'resolved_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the complainant resident
     */
    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainant_id');
    }

    /**
     * Get the user who handled the case
     */
    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Get the complainant name via relationship
     */
    public function getComplainantNameAttribute()
    {
        return $this->complainant ? $this->complainant->first_name . ' ' . $this->complainant->last_name : 'N/A';
    }

    /**
     * Get the complainant address via relationship
     */
    public function getComplainantAddressAttribute()
    {
        return $this->complainant ? $this->complainant->address . ', Purok ' . $this->complainant->purok : 'N/A';
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
        return $query->whereIn('status', ['Pending', 'Ongoing']);
    }

    /**
     * Scope a query to only include settled blotters
     */
    public function scopeSettled($query)
    {
        return $query->where('status', 'Settled');
    }
}
