<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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
        'hearing_stage',
        'hearing_count',
        'last_hearing_date',
        'next_hearing_date',
        'cfa_issued',
        'cfa_issued_date',
        'deadline_date',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'resolved_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_hearing_date' => 'date',
        'next_hearing_date' => 'date',
        'cfa_issued_date' => 'date',
        'deadline_date' => 'date',
        'cfa_issued' => 'boolean',
    ];

    // Relationships
    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainant_id');
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function parties()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id');
    }

    public function complainants()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id')->where('party_type', 'complainant');
    }

    public function respondents()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id')->where('party_type', 'respondent');
    }

    public function witnesses()
    {
        return $this->hasMany(CaseParty::class, 'blotter_id')->where('party_type', 'witness');
    }

    // ========== HEARING TRACKING METHODS ==========

    /**
     * Start the mediation process (15-day deadline)
     */
    public function startMediation()
    {
        $this->hearing_stage = 'mediation';
        $this->deadline_date = now()->addDays(15);
        $this->hearing_count = 0;
        $this->cfa_issued = false;
        $this->next_hearing_date = null;
        $this->save();
    }

    /**
     * Check if hearing is overdue
     */
    public function isHearingOverdue()
    {
        if (!$this->next_hearing_date) return false;
        return now()->gt($this->next_hearing_date);
    }

    /**
     * Get days until deadline
     */
    public function getDaysUntilDeadline()
    {
        if (!$this->deadline_date) return null;
        return now()->diffInDays($this->deadline_date, false);
    }

    /**
     * Record a missed hearing
     */
    public function recordMissedHearing()
    {
        $this->hearing_count++;
        $this->last_hearing_date = now();
        $this->save();

        // Check if 3 strikes - issue CFA
        if ($this->hearing_count >= 3 && !$this->cfa_issued) {
            $this->issueCFA();
        }
    }

    /**
     * Issue Certificate to File Action (CFA)
     */
    public function issueCFA()
    {
        $this->cfa_issued = true;
        $this->cfa_issued_date = now();
        $this->status = 'Referred';
        $this->deadline_date = null;
        $this->save();
    }

    /**
     * Schedule next hearing
     */
    public function scheduleNextHearing($daysFromNow = 7)
    {
        $this->next_hearing_date = now()->addDays($daysFromNow);
        $this->save();
    }

    /**
     * Record attendance at hearing
     */
    public function recordAttendance($respondentAttended = true, $complainantAttended = true)
    {
        if ($respondentAttended && $complainantAttended) {
            // Both attended - move to next stage or resolve
            $this->hearing_count = 0;
            $this->next_hearing_date = null;

            if ($this->hearing_stage == 'mediation') {
                // Move to conciliation stage - set 15-day deadline
                $this->hearing_stage = 'conciliation';
                $this->deadline_date = now()->addDays(15);
            } else {
                // Conciliation stage succeeded - case settled
                $this->status = 'Settled';
                $this->resolved_date = now();
                $this->deadline_date = null;
            }
            $this->save();
            return true;
        } elseif (!$respondentAttended) {
            $this->recordMissedHearing();
            return false;
        }
        return false;
    }

    /**
     * Extend conciliation deadline (additional 15 days)
     */
    public function extendConciliation()
    {
        if ($this->hearing_stage == 'conciliation' && !$this->cfa_issued) {
            $this->deadline_date = now()->addDays(15);
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Get the stage description for display
     */
    public function getStageDescription()
    {
        if ($this->hearing_stage == 'mediation') {
            return 'Mediation (15 days to settle)';
        }
        return 'Conciliation (15 days to resolve)';
    }

    /**
     * Check if deadline is approaching (within 3 days)
     */
    public function isDeadlineApproaching()
    {
        $daysLeft = $this->getDaysUntilDeadline();
        return $daysLeft !== null && $daysLeft <= 3 && $daysLeft > 0;
    }

    /**
     * Check if deadline has expired
     */
    public function isDeadlineExpired()
    {
        $daysLeft = $this->getDaysUntilDeadline();
        return $daysLeft !== null && $daysLeft <= 0;
    }
}
