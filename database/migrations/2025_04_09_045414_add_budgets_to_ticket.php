<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('bop_budget', 15, 2)->default(0);
            $table->decimal('thl_budget', 15, 2)->default(0);
            $table->decimal('rujukan_budget', 15, 2)->default(0);
            $table->decimal('jasamedis_budget', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'bop_budget',
                'thl_budget',
                'rujukan_budget',
                'jasamedis_budget',
            ]);
        });
    }
};

