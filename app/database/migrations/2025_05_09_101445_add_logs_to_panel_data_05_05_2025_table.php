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
        Schema::table('panel_data_05_05_2025', function (Blueprint $table) {
            foreach (['issue_created_count', 'issue_closed_count', 'pull_request_created_count', 'pull_request_merged_count', 'pull_request_closed_count'] as $column) {
                $table->double("{$column}_log")->storedAs("LN({$column}+1)")->after($column);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panel_data_05_05_2025', function (Blueprint $table) {
            //
        });
    }
};
