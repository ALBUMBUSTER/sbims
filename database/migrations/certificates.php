<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_id', 20)->unique();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->enum('certificate_type', ['Clearance', 'Indigency', 'Residency']);
            $table->text('purpose');
            $table->enum('status', ['Pending', 'Approved', 'Released', 'Rejected', 'Archived'])->default('Pending');
            $table->text('rejection_reason')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('released_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('issued_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};
