<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // First, remove the old role column
            $table->dropColumn('role');

            // Add new role_id column
            $table->foreignId('role_id')->after('password')->nullable()->constrained('roles');

            // Update the index
            $table->dropIndex(['username', 'is_active']);
            $table->index(['username', 'is_active', 'role_id']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');

            // Restore old role column
            $table->enum('role', ['admin', 'captain', 'secretary', 'resident'])->default('resident');

            // Restore index
            $table->dropIndex(['username', 'is_active', 'role_id']);
            $table->index(['username', 'is_active']);
        });
    }
};
