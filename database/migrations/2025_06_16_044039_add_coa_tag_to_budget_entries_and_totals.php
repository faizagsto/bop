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
        Schema::table('ticket_budget_entries', function (Blueprint $table) {
            $table->foreignId('coa_tag_id')
                ->nullable()
                ->constrained('coa_tags')
                ->onDelete('restrict')
                ->after('budget_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_budget_entries', function (Blueprint $table) {
            $table->dropForeign(['coa_tag_id']);
            $table->dropColumn('coa_tag_id');
        });

    }
};
