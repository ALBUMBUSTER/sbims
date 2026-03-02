<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barangay_info', function (Blueprint $table) {
            $table->id();
            $table->string('barangay_name', 100);
            $table->string('barangay_captain', 100)->nullable();
            $table->string('barangay_secretary', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_number', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('barangay_info');
    }
};
