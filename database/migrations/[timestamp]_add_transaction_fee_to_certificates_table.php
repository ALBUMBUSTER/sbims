<?php
// database/migrations/[timestamp]_add_transaction_fee_to_certificates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->decimal('transaction_fee', 10, 2)->nullable()->after('purpose')->comment('Transaction fee amount for the certificate');
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('transaction_fee');
        });
    }
};
