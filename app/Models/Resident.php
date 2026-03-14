<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Resident extends Model
{
    use HasFactory;
    use SoftDeletes; // Add this trait
    protected $fillable = [
        'resident_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birthdate',
        'civil_status',
        'address',
        'purok',
        'household_number',
        'contact_number',
        'email',
        'is_voter',
        'is_senior',
        'is_pwd',
        'pwd_id',
        'disability_type',
        'is_4ps',
        '4ps_id',
        'emergency_contact_name',
        'emergency_contact_number',
        'profile_photo',
        'archived_at',
    ];
    protected $dates = ['deleted_at']; // Add this if using older Laravel versions
    protected $casts = [
        'birthdate' => 'date',
        'is_voter' => 'boolean',
        'is_senior' => 'boolean',
        'is_pwd' => 'boolean',
        'is_4ps' => 'boolean',
        'archived_at' => 'datetime', // Add this
    ];

    /**
     * Get the user account associated with the resident
     */
    public function user()
    {
        return $this->hasOne(User::class, 'resident_id');
    }

    /**
     * Get the certificates for the resident
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Get the blotter cases where this resident is the complainant
     */
    public function complainantBlotters()
    {
        return $this->hasMany(Blotter::class, 'complainant_id');
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name . ($this->suffix ? ' ' . $this->suffix : ''));
    }

    /**
     * Get the age attribute
     */
    public function getAgeAttribute()
    {
        if ($this->birthdate) {
            return Carbon::parse($this->birthdate)->age;
        }
        return null;
    }

    /**
     * Scope a query to search residents
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('resident_id', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%");
        });
    }

    /* ==================== ARCHIVE SCOPES ==================== */

    /**
     * Scope to get only active (non-archived) residents
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope to get only archived residents
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

}
