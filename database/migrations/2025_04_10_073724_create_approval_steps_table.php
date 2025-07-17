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
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('step_order'); // e.g., 1, 2, 3...
            $table->string('role'); // e.g., 'requester', 'bm', 'manager'
            $table->string('area')->nullable(); // 'branch' or 'main'
            $table->string('unit')->nullable(); // optional: 'finance', 'sales', etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};
