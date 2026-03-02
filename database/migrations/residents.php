<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('resident_id', 20)->unique();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->date('birthdate');
            $table->enum('gender', ['Male', 'Female']);
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Divorced']);
            $table->string('contact_number', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address');
            $table->string('purok', 50);
            $table->string('household_number', 20)->nullable();
            $table->boolean('is_voter')->default(false);
            $table->boolean('is_4ps')->default(false);
            $table->boolean('is_senior')->default(false);
            $table->boolean('is_pwd')->default(false);
            $table->string('pwd_id', 50)->nullable();
            $table->string('disability_type', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('residents');
    }
};
