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

        Schema::table('coa_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('budget_type_id')->nullable()->after('name');
            $table->foreign('budget_type_id')->references('id')->on('budget_types')->onDelete('cascade');
        });

        Schema::table('coa_tags', function (Blueprint $table) {
            $table->dropColumn('budget_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
