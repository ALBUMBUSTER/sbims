<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'captain', 'secretary', 'clerk'])->after('email');
            }

            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->after('name');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }

            if (!Schema::hasColumn('users', 'last_login')) {
                $table->timestamp('last_login')->nullable()->after('updated_at');
            }

            // Make email nullable (if not already)
            $table->string('email')->nullable()->change();

            // Add index for faster login queries
            $table->index(['username', 'is_active'], 'users_username_is_active_index');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the index first
            $table->dropIndex('users_username_is_active_index');

            // Remove added columns
            $columns = ['username', 'role', 'full_name', 'is_active', 'last_login'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Make email not nullable again
            $table->string('email')->nullable(false)->change();
        });
    }
};
