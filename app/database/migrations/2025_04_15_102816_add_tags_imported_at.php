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
        Schema::table('repositories', function (Blueprint $table) {
            $table->dateTime('tags_imported_at')->nullable()->after('topics');
            $table->dateTime('repository_months_imported_at')->nullable()->after('tags_imported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repositories', function (Blueprint $table) {
            $table->dropColumn('tags_imported_at');
            $table->dropColumn('repository_months_imported_at');
        });
    }
};
