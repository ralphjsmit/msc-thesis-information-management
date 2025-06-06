<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id');
            $table->text('content')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedTinyInteger('rating')->default(0);
            $table->json('tags')->nullable();
            $table->string('title');
            $table->json('json')->nullable();
            $table->json('json_array_of_objects')->nullable();
            $table->string('string_backed_enum')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
