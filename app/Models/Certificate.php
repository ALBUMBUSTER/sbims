<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'certificate_id',
        'resident_id',
        'certificate_type',
        'purpose',
        'transaction_fee',
        'status',
        'rejection_reason',
        'rejected_at',
        'released_at',
        'issued_by',
        'approved_by',
        'approved_at',
        'issued_date',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'released_at' => 'datetime',
        'issued_date' => 'datetime',
        'transaction_fee' => 'decimal:2',
    ];

    /**
     * Get the resident that owns the certificate
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    /**
     * Get the user who approved the certificate
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who issued the certificate
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the certificate number attribute
     */
    public function getCertificateNumberAttribute()
    {
        return $this->certificate_id;
    }

    /**
     * Scope a query to only include pending certificates
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include approved certificates
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    /**
     * Scope a query to only include rejected certificates
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    /**
     * Scope a query to only include released certificates
     */
    public function scopeReleased($query)
    {
        return $query->where('status', 'Released');
    }
}
