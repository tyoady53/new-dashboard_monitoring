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
        Schema::create('dash_nilai_kritis', function (Blueprint $table) {
            $table->timestamp('dttm');
            $table->string('cust_name',50);
            $table->string('cust_branch',50);
            $table->string('lab_no',50);
            $table->string('patient_name',50);
            $table->string('test_name',50);
            $table->string('results',5);
            $table->string('flag',10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dash_nilai_kritis');
    }
};
