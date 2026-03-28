<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('blotters', function (Blueprint $table) {
            $table->enum('hearing_stage', ['mediation', 'conciliation'])->default('mediation')->after('status');
            $table->integer('hearing_count')->default(0)->after('hearing_stage');
            $table->date('last_hearing_date')->nullable()->after('hearing_count');
            $table->date('next_hearing_date')->nullable()->after('last_hearing_date');
            $table->boolean('cfa_issued')->default(false)->after('next_hearing_date');
            $table->date('cfa_issued_date')->nullable()->after('cfa_issued');
            $table->date('deadline_date')->nullable()->after('cfa_issued_date');
        });
    }

    public function down()
    {
        Schema::table('blotters', function (Blueprint $table) {
            $table->dropColumn([
                'hearing_stage',
                'hearing_count',
                'last_hearing_date',
                'next_hearing_date',
                'cfa_issued',
                'cfa_issued_date',
                'deadline_date'
            ]);
        });
    }
};
