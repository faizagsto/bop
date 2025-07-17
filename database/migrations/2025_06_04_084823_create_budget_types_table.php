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
       Schema::create('budget_types', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // "BOP", "THL", etc.
        $table->string('code')->unique(); // "bop", "thl", etc. (for internal references)
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_types');
    }
};
