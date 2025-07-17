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
        // In the migration file
            Schema::create('project_names', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // The project name
                $table->string('customer');      // Customer name
                $table->string('period');        // Period
                $table->string('pks_number');    // PKS number
                $table->timestamps();
            });

            Schema::table('tickets', function (Blueprint $table) {
                // Add a foreign key to the project_names table
                $table->foreignId('project_name_id')
                      ->nullable()
                      ->constrained('project_names')
                      ->onDelete('set null'); // Set to null if the project name is deleted
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_names');
    }
};
