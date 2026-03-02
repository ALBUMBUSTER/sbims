<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('backups', function (Blueprint $table) {
        $table->id();
        $table->string('filename');
        $table->string('path');
        $table->bigInteger('size')->default(0);
        $table->enum('type', ['database', 'full'])->default('database');
        $table->json('tables_backed_up')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });
}
};
