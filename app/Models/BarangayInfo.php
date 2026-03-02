<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // ADD THIS LINE

class BarangayInfo extends Model
{
    protected $table = 'barangay_info';

    protected $fillable = [
        'barangay_name',
        'barangay_captain',
        'barangay_secretary',
        'address',
        'contact_number',
        'email',
        'logo_path'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return Storage::url($this->logo_path);
        }

        return null;
    }

    /**
     * Get complete address
     */
    public function getCompleteAddressAttribute()
    {
        return $this->address ?? 'Address not set';
    }
}
