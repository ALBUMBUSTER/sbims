<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blotter extends Model
{
    use SoftDeletes;

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

    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainant_id');
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Get all parties (complainants, respondents, witnesses)
     */
    public function parties()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id');
    }

    /**
     * Get only complainants
     */
    public function complainants()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id')->where('party_type', 'complainant');
    }

    /**
     * Get only respondents
     */
    public function respondents()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id')->where('party_type', 'respondent');
    }

    /**
     * Get only witnesses
     */
    public function witnesses()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id')->where('party_type', 'witness');
    }
}
