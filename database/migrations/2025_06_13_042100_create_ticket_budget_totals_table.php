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
       Schema::create('ticket_budget_totals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();
            $table->foreignId('budget_type_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('budget_total')
                ->default(0); // e.g. 500000

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_budget_totals');
    }
};
