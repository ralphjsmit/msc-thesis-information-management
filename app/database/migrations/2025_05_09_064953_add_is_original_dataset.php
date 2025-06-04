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
            $table->boolean('is_original_analysis')->default(false);
        });

        DB::table('panel_data_05_05_2025')->update(['is_original_analysis' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
