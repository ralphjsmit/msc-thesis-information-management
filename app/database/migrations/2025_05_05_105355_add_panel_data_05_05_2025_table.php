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
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('panel_data_05_05_2025', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('period')->storedAs(
                '(year - 2018) * 12 + month - 3'
            );
            $table->unsignedTinyInteger('treatment_cohort_g')->storedAs(
                'CASE WHEN first_funding_date IS NOT NULL THEN ((year(first_funding_date) - 2018) * 12 + month(first_funding_date) - 3) ELSE 0 END'
            );
            $table->dateTime('first_funding_date')->nullable();
            $table->dateTime('sponsorship_listing_date')->nullable();
            $table->unsignedTinyInteger('sponsorship_listing_cohort_g')->storedAs(
                'CASE WHEN sponsorship_listing_date IS NOT NULL THEN ((year(sponsorship_listing_date) - 2018) * 12 + month(sponsorship_listing_date) - 3) ELSE 0 END'
            );
            $table->unsignedBigInteger('new_funding_count')->nullable();
            $table->unsignedBigInteger('new_funding_count_log')->storedAs('LN(new_funding_count+1)');
            $table->unsignedBigInteger('new_funding_onetime_count')->nullable();
            $table->unsignedBigInteger('new_funding_onetime_count_log')->storedAs('LN(new_funding_onetime_count+1)');
            $table->unsignedBigInteger('new_funding_recurring_count')->nullable();
            $table->unsignedBigInteger('new_funding_recurring_count_log')->storedAs('LN(new_funding_recurring_count+1)');
            $table->unsignedBigInteger('tag_count');
            $table->double('tag_count_log')->storedAs('LN(tag_count+1)');
            $table->unsignedBigInteger('stargazer_count');
            $table->double('stargazer_count_log')->storedAs('LN(stargazer_count+1)');
            $table->unsignedBigInteger('watcher_count');
            $table->double('watcher_count_log')->storedAs('LN(watcher_count+1)');
            $table->unsignedBigInteger('fork_count');
            $table->double('fork_count_log')->storedAs('LN(fork_count+1)');
            $table->unsignedBigInteger('repository_total_count');
            $table->double('repository_total_count_log')->storedAs('LN(repository_total_count+1)');
            $table->unsignedBigInteger('repository_count');
            $table->double('repository_count_log')->storedAs('LN(repository_count+1)');
            $table->unsignedBigInteger('issue_created_count');
            $table->unsignedBigInteger('issue_closed_count');
            $table->unsignedBigInteger('pull_request_created_count');
            $table->unsignedBigInteger('pull_request_merged_count');
            $table->unsignedBigInteger('pull_request_closed_count');
            $table->unsignedBigInteger('activity_count')->storedAs('pull_request_merged_count + pull_request_closed_count + issue_closed_count');
            $table->double('activity_count_log')->storedAs('LN(activity_count+1)');
            //            $table->unsignedBigInteger('avg_activity_pre_treatment');
            //            $table->unsignedBigInteger('activity_relative_to_pre_treatment')->storedAs('CASE WHEN avg_activity_pre_treatment = 0 THEN 0 ELSE activity_count / avg_activity_pre_treatment END');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
