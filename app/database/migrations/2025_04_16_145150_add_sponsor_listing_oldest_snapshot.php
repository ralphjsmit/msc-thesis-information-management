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
        Schema::table('organizations', function (Blueprint $table) {
            $table->dateTime('wayback_machine_oldest_snapshot')->nullable()->after('sponsorships_as_maintainer');
            $table->dateTime('wayback_machine_imported_at')->nullable()->after('repositories_imported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('wayback_machine_oldest_snapshot');
        });
    }
};
