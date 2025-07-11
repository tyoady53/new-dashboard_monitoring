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
        Schema::create('dash_tat', function (Blueprint $table) {
            $table->timestamp('dttm');
            $table->string('cust_name',50);
            $table->string('cust_branch',50);
            $table->string('tat_type',20);
            $table->integer('total',false, false);
            $table->integer('total_not_finish',false, false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dash_tat');
    }
};
