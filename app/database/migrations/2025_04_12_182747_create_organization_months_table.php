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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('git_hub_id')->unique();
            $table->string('login')->unique();
            $table->boolean('has_sponsors_listing')->nullable();
            $table->json('sponsors')->nullable();
            $table->json('sponsorships_as_maintainer')->nullable();
            $table->timestamp('sponsors_imported_at')->nullable();
            $table->timestamp('repositories_imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_months', function (Blueprint $table) {
            $table->id();
            $table->string('git_hub_id');
            $table->string('login');
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->boolean('is_treated');
            $table->boolean('is_post_event');
            $table->date('first_funding_date')->nullable();

            /** The following columns are auto-calculated based on other records: */
            $table->unsignedBigInteger('issue_created_count');
            $table->unsignedBigInteger('issue_closed_count');
            $table->unsignedBigInteger('pull_request_created_count');
            $table->unsignedBigInteger('pull_request_merged_count');
            $table->unsignedBigInteger('pull_request_closed_count');
            $table->unsignedBigInteger('tag_count');

            $table->timestamps();
        });

        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->string('git_hub_organization_id');
            $table->foreign('git_hub_organization_id')->references('git_hub_id')->on('organizations');
            $table->string('git_hub_id');
            $table->string('name');
            $table->unsignedBigInteger('stargazer_count');
            $table->unsignedBigInteger('watcher_count');
            $table->json('languages');
            $table->boolean('has_issues_enabled');
            $table->boolean('has_projects_enabled');
            $table->boolean('has_wiki_enabled');
            $table->boolean('has_discussions_enabled');
            $table->unsignedBigInteger('fork_count');
            $table->boolean('is_archived');
            $table->boolean('is_disabled');
            $table->json('topics');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('organization_months');
        Schema::dropIfExists('repositories');
    }
};
