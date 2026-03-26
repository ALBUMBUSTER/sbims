<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, check if columns exist before trying to modify
        if (Schema::hasTable('blotters')) {

            // Make complainant_id nullable (since we'll use case_parties table)
            if (Schema::hasColumn('blotters', 'complainant_id')) {
                Schema::table('blotters', function (Blueprint $table) {
                    $table->unsignedBigInteger('complainant_id')->nullable()->change();
                });
            }

            // Make respondent_name nullable
            if (Schema::hasColumn('blotters', 'respondent_name')) {
                Schema::table('blotters', function (Blueprint $table) {
                    $table->string('respondent_name')->nullable()->change();
                });
            }

            // Make respondent_address nullable
            if (Schema::hasColumn('blotters', 'respondent_address')) {
                Schema::table('blotters', function (Blueprint $table) {
                    $table->text('respondent_address')->nullable()->change();
                });
            }
        }
    }

    public function down()
    {
        // Revert changes
        if (Schema::hasTable('blotters')) {

            if (Schema::hasColumn('blotters', 'complainant_id')) {
                Schema::table('blotters', function (Blueprint $table) {
                    $table->unsignedBigInteger('complainant_id')->nullable(false)->change();
                });
            }

            if (Schema::hasColumn('blotters', 'respondent_name')) {
                Schema::table('blotters', function (Blueprint $table) {
                    $table->string('respondent_name')->nullable(false)->change();
                });
            }

            if (Schema::hasColumn('blotters', 'respondent_address')) {
                Schema::table('blotters', function (Blueprint $table) {
                    $table->text('respondent_address')->nullable(false)->change();
                });
            }
        }
    }
};
