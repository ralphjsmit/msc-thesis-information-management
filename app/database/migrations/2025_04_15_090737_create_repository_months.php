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
        Schema::create('repository_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repository_id')->constrained();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedBigInteger('issue_created_count');
            $table->unsignedBigInteger('issue_closed_count');
            $table->unsignedBigInteger('pull_request_created_count');
            $table->unsignedBigInteger('pull_request_merged_count');
            $table->unsignedBigInteger('pull_request_closed_count');
            //            $table->unsignedBigInteger('release_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repository_months');
    }
};
