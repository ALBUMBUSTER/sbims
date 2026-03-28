<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add unique constraint to prevent duplicate entries at database level
        Schema::table('residents', function (Blueprint $table) {
            // Add unique constraint on combination of first_name, last_name, middle_name, birthdate
            $table->unique(['first_name', 'last_name', 'birthdate'], 'unique_resident_name_birthdate');
        });
    }

    public function down()
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropUnique('unique_resident_name_birthdate');
        });
    }
};
