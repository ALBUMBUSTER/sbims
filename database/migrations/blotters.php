<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('blotters', function (Blueprint $table) {
            $table->id();
            $table->string('case_id', 20)->unique();
            $table->foreignId('complainant_id')->constrained('residents')->onDelete('cascade');
            $table->string('respondent_name', 100);
            $table->text('respondent_address')->nullable();
            $table->string('incident_type', 100);
            $table->dateTime('incident_date');
            $table->text('incident_location');
            $table->text('description');
            $table->enum('status', ['Pending', 'Ongoing', 'Settled', 'Referred'])->default('Pending');
            $table->text('resolution')->nullable();
            $table->dateTime('resolved_date')->nullable();
            $table->foreignId('handled_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blotters');
    }
};
