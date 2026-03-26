<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeactivateExpiredCaptains extends Command
{
    protected $signature = 'captains:deactivate-expired';
    protected $description = 'Deactivate captain accounts whose term has ended';

    public function handle()
    {
        $today = Carbon::today();

        $expiredCaptains = User::where('role_id', 2)
            ->where('is_active', true)
            ->where('term_end_date', '<=', $today)
            ->get();

        $count = 0;

        foreach ($expiredCaptains as $captain) {
            $captain->update(['is_active' => false]);

            ActivityLog::create([
                'user_id' => null,
                'action' => 'AUTO_DEACTIVATE',
                'description' => "Captain '{$captain->full_name}' account was automatically deactivated as term ended on {$captain->term_end_date}",
                'ip_address' => 'system',
                'user_agent' => 'scheduler'
            ]);

            NotificationHelper::toUser(
                $captain->id,
                'Account Deactivated - Term Ended',
                "Your term as Barangay Captain has ended on {$captain->term_end_date}. Your account has been deactivated. Please contact the administrator.",
                'warning',
                route('login')
            );

            NotificationHelper::toAdmins(
                'Captain Account Auto-Deactivated',
                "Captain '{$captain->full_name}' was automatically deactivated as their term ended on {$captain->term_end_date}.",
                'warning',
                route('admin.users.index')
            );

            $count++;
        }

        Log::info("Deactivated {$count} expired captain accounts.");
        $this->info("Deactivated {$count} expired captain accounts.");
    }
}
