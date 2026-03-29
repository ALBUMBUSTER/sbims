<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('blotters', function (Blueprint $table) {
            $table->text('referred_reason')->nullable()->after('resolution');
            $table->string('referred_to', 100)->nullable()->after('referred_reason');
            $table->string('referred_to_other', 255)->nullable()->after('referred_to');
            $table->date('referred_date')->nullable()->after('referred_to_other');
        });
    }

    public function down()
    {
        Schema::table('blotters', function (Blueprint $table) {
            $table->dropColumn(['referred_reason', 'referred_to', 'referred_to_other', 'referred_date']);
        });
    }
};
