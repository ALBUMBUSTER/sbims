<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AddSecurityQuestionsToUsers extends Command
{
    protected $signature = 'users:add-security-questions';
    protected $description = 'Add default security questions to existing users';

    public function handle()
    {
        $users = User::whereNull('security_question')->get();

        if ($users->isEmpty()) {
            $this->info('All users already have security questions set.');
            return 0;
        }

        $this->info("Found " . $users->count() . " users without security questions.");

        foreach ($users as $user) {
            // Set a default security question based on username or role
            $defaultQuestion = "What is your mother's maiden name?";
            $defaultAnswer = "changeme_" . $user->id; // Unique default answer

            $user->security_question = $defaultQuestion;
            $user->security_answer = Hash::make($defaultAnswer);
            $user->save();

            $this->line("Added security question to user: {$user->username}");
        }

        $this->info('All users have been updated with default security questions.');
        $this->warn('IMPORTANT: Users should update their security answers after first login!');

        return 0;
    }
}
