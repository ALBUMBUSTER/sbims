<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('backup_settings')->insert([
            ['key' => 'schedule_type', 'value' => 'daily', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'backup_time', 'value' => '02:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'retention_days', 'value' => '30', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'last_backup_run', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'next_backup_run', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'backup_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};
