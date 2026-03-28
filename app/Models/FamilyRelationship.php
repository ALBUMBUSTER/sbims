<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'related_resident_id',
        'relationship_type',
        'full_name',
        'birthdate',
        'gender',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function relatedResident()
    {
        return $this->belongsTo(Resident::class, 'related_resident_id');
    }
}
