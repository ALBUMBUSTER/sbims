<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('case_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blotter_id')->constrained('blotters')->onDelete('cascade');
            $table->enum('party_type', ['complainant', 'respondent', 'witness']);
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->unsignedBigInteger('resident_id')->nullable();
            $table->text('additional_info')->nullable();
            $table->timestamps();

            // Optional: add foreign key for resident_id
            $table->foreign('resident_id')->references('id')->on('residents')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('case_parties');
    }
};
