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
        Schema::table('panel_data_02_05_2025', function (Blueprint $table) {
            $table->unsignedTinyInteger('treatment_cohort_g')->after('period')->storedAs(
                'CASE WHEN first_funding_date IS NOT NULL THEN ((year(first_funding_date) - 2018) * 12 + month(first_funding_date) - 3) ELSE 0 END'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panel_data_02_05_2025', function (Blueprint $table) {
            $table->dropColumn('treatment_cohort_g');
        });
    }
};
