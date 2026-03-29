<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->boolean('is_deceased')->default(false)->after('archived_reason');
            $table->date('death_date')->nullable()->after('is_deceased');
            $table->text('cause_of_death')->nullable()->after('death_date');
            $table->string('death_certificate_number', 100)->nullable()->after('cause_of_death');
        });
    }

    public function down()
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['is_deceased', 'death_date', 'cause_of_death', 'death_certificate_number']);
        });
    }
};
