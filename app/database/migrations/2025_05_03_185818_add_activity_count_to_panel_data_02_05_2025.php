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
            $table->unsignedBigInteger('activity_count')->storedAs('pull_request_merged_count + pull_request_closed_count + issue_closed_count');
        });

        Schema::table('panel_data_02_05_2025', function (Blueprint $table) {
            $table->double('activity_count_log')->storedAs('CASE when activity_count = 0 THEN 0 ELSE LN(activity_count) END');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panel_data_02_05_2025', function (Blueprint $table) {
            $table->dropColumn('activity_count_log');
            $table->dropColumn('activity_count');
        });
    }
};
