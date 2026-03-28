<?php

namespace App\Console\Commands;

use App\Models\Blotter;
use App\Helpers\NotificationHelper;
use Illuminate\Console\Command;

class CheckOverdueHearings extends Command
{
    protected $signature = 'hearings:check-overdue';
    protected $description = 'Check for overdue hearings and send notifications';

    public function handle()
    {
        $overdueCases = Blotter::whereIn('status', ['Pending', 'Ongoing'])
            ->where('next_hearing_date', '<', now())
            ->where('cfa_issued', false)
            ->get();

        foreach ($overdueCases as $case) {
            $case->recordMissedHearing();

            NotificationHelper::toCaptains(
                'Overdue Hearing',
                'Case #' . $case->case_id . ' - Respondent missed hearing. ' . (3 - $case->hearing_count) . ' more before CFA.',
                'warning',
                route('captain.blotters.show', $case->id)
            );
        }

        $this->info('Checked ' . $overdueCases->count() . ' overdue cases.');
    }
}
