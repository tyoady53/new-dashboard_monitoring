<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dash_visitation', function (Blueprint $table) {
            $table->timestamp('dttm');
            $table->string('cust_name',50);
            $table->string('cust_branch',50);
            $table->integer('visitation',false, false);  // (column name, autoIncrement = false, unsigned = false)
            $table->integer('test_not_finish',false, false);
            $table->integer('test_finish',false, false);
            $table->integer('spec_not_draw',false, false);
            $table->integer('spec_received',false, false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dash_visitation');
    }
};
