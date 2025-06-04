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
        Schema::create('extraction_2_organizations_sponsor_fields', function (Blueprint $table) {
            $table->id();
            $table->string('gitHubId');
            $table->string('name');
            $table->string('login');
            $table->boolean('hasSponsorsListing');
            $table->json('sponsoring');
            $table->json('sponsors');
            $table->json('sponsorshipsAsMaintainer');
            $table->json('sponsorshipsAsSponsor');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extraction_1_organizations_sponsor_fields');
    }
};
