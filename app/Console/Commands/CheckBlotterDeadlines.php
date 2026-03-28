<?php

namespace App\Console\Commands;

use App\Models\Blotter;
use App\Helpers\NotificationHelper;
use Illuminate\Console\Command;

class CheckBlotterDeadlines extends Command
{
    protected $signature = 'blotters:check-deadlines';
    protected $description = 'Check for expired blotter deadlines and auto-progress cases';

    public function handle()
    {
        // Check mediation deadlines (15 days) - move to conciliation
        $expiredMediation = Blotter::whereIn('status', ['Pending', 'Ongoing'])
            ->where('hearing_stage', 'mediation')
            ->where('deadline_date', '<', now())
            ->where('cfa_issued', false)
            ->get();

        foreach ($expiredMediation as $case) {
            // Move to conciliation stage
            $case->hearing_stage = 'conciliation';
            $case->deadline_date = now()->addDays(15);
            $case->save();

            NotificationHelper::toCaptains(
                'Case Moved to Conciliation',
                'Case #' . $case->case_id . ' has moved to conciliation stage due to unresolved mediation.',
                'info',
                route('captain.blotters.show', $case->id)
            );

            $this->info("Case {$case->case_id} moved to conciliation");
        }

        // Check conciliation deadlines (15 days) - issue CFA
        $expiredConciliation = Blotter::whereIn('status', ['Pending', 'Ongoing'])
            ->where('hearing_stage', 'conciliation')
            ->where('deadline_date', '<', now())
            ->where('cfa_issued', false)
            ->get();

        foreach ($expiredConciliation as $case) {
            // Issue CFA
            $case->issueCFA();

            NotificationHelper::toCaptains(
                'CFA Issued - Case Expired',
                'Case #' . $case->case_id . ' - 15-day conciliation period has ended. Certificate to File Action issued.',
                'warning',
                route('captain.blotters.show', $case->id)
            );
            
            $this->info("CFA issued for case {$case->case_id}");
        }

        $this->info("Checked " . $expiredMediation->count() . " mediation deadlines.");
        $this->info("Checked " . $expiredConciliation->count() . " conciliation deadlines.");
    }
}
