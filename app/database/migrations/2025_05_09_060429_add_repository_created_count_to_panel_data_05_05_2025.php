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
            $table->after('repository_count', function (Blueprint $table) {
                $table->unsignedBigInteger('repository_created_count');
                $table->double('repository_created_count_log')->storedAs('LN(repository_created_count+1)');
            });
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
