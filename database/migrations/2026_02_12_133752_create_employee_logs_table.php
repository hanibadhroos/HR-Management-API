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
        Schema::create('employee_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // $table->uuid('employee_id');
            $table->uuid('employee_id')->nullable();


            $table->string('action'); // created, updated, deleted
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees')
                    ->nullOnDelete();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_logs');
    }
};
