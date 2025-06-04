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
            $table->unsignedTinyInteger('period')->after('month')->storedAs(
                '(year - 2018) * 12 + month - 3'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panel_data_02_05_2025', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};
