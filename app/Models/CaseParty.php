<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseParty extends Model
{
    protected $fillable = [
        'blotter_id',
        'party_type',
        'name',
        'address',
        'contact_number',
        'resident_id',
        'additional_info'
    ];

    protected $casts = [
        'party_type' => 'string',
    ];

    public function blotter()
    {
        return $this->belongsTo(Blotter::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
