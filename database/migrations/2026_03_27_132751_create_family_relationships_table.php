<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamilyRelationshipsTable extends Migration
{
    public function up()
    {
        Schema::create('family_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->onDelete('cascade');
            $table->foreignId('related_resident_id')->nullable()->constrained('residents')->onDelete('cascade');
            $table->string('relationship_type'); // spouse, child, parent, guardian
            $table->string('full_name')->nullable(); // for non-residents
            $table->date('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();

            $table->index(['resident_id', 'relationship_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('family_relationships');
    }
}
