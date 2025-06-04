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

        Schema::create('panel_data_02_05_2025', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->dateTime('first_funding_date')->nullable();
            $table->boolean('is_treated')->storedAs('first_funding_date IS NOT NULL');
            if ($driver === 'mysql') {
                $table->boolean('is_post_event')->storedAs(
                    '
                first_funding_date IS NOT NULL AND 
                (year > YEAR(first_funding_date) OR 
                (year = YEAR(first_funding_date) AND month >= MONTH(first_funding_date)))
            '
                );
            } else {
                $table->boolean('is_post_event')->storedAs(
                    "
                    first_funding_date IS NOT NULL AND 
                    (
                        year > CAST(STRFTIME('%Y', first_funding_date) AS INTEGER) OR 
                        (year = CAST(STRFTIME('%Y', first_funding_date) AS INTEGER) AND 
                         month >= CAST(STRFTIME('%m', first_funding_date) AS INTEGER))
                    )
                "
                );
            }
            $table->unsignedBigInteger('tag_count');
            $table->unsignedBigInteger('stargazer_count');
            $table->unsignedBigInteger('watcher_count');
            $table->unsignedBigInteger('issue_created_count');
            $table->unsignedBigInteger('issue_closed_count');
            $table->unsignedBigInteger('pull_request_created_count');
            $table->unsignedBigInteger('pull_request_merged_count');
            $table->unsignedBigInteger('pull_request_closed_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_data_02_05_2025');
    }
};
