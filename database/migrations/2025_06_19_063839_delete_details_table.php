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
        Schema::dropIfExists('jasamedis_details');
        Schema::dropIfExists('bop_details');
        Schema::dropIfExists('thl_details');
        Schema::dropIfExists('rujukan_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
