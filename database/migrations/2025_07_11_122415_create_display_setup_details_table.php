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
        Schema::create('display_setup_details', function (Blueprint $table) {
            $table->id();
            $table->integer('display_id');
            $table->integer('sequence');
            $table->integer('chart_show');
            $table->integer('data_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_setup_details');
    }
};
