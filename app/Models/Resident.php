<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Resident extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    protected $dates = ['deleted_at'];

    protected $casts = [
        'birthdate' => 'date',
        'is_voter' => 'boolean',
        'is_senior' => 'boolean',
        'is_pwd' => 'boolean',
        'is_4ps' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * Check for duplicate resident
     * @param array $data
     * @return Resident|null
     */
    public static function checkDuplicate($data)
    {
        $query = self::query()
            ->where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'])
            ->where('birthdate', $data['birthdate']);

        // Include middle name in duplicate check if provided
        if (!empty($data['middle_name'])) {
            $query->where('middle_name', $data['middle_name']);
        }

        return $query->first();
    }

    /**
     * Check for duplicate during import (case insensitive)
     */
    public static function checkDuplicateImport($data)
    {
        $query = self::query()
            ->whereRaw('LOWER(first_name) = ?', [strtolower($data['first_name'])])
            ->whereRaw('LOWER(last_name) = ?', [strtolower($data['last_name'])])
            ->where('birthdate', $data['birthdate']);

        if (!empty($data['middle_name'])) {
            $query->whereRaw('LOWER(middle_name) = ?', [strtolower($data['middle_name'])]);
        }

        return $query->first();
    }

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
     * Family relationships
     */
    public function familyRelationships()
    {
        return $this->hasMany(FamilyRelationship::class);
    }

    public function spouse()
    {
        return $this->hasOne(FamilyRelationship::class)
            ->where('relationship_type', 'spouse');
    }

    public function children()
    {
        return $this->hasMany(FamilyRelationship::class)
            ->where('relationship_type', 'child');
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
