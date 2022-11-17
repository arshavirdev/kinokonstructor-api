<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('status', ['draft', 'moderation', 'rejected', 'accepted'])->default('draft');
            $table->string('title');
            $table->enum('format', ['movie', 'series']);
            $table->enum('genre_type', ['documentary', 'fictional']);
            $table->unsignedInteger('chronography');
            $table->unsignedInteger('series_count')->default(1);
            $table->json('genres')->default('[]');
            $table->text('logline')->nullable();
            $table->text('synopsis')->nullable();
            $table->text('relevance')->nullable();
            $table->text('additional')->nullable();
            $table->string('audio_reference')->nullable();

            $table->string('budget')->nullable();
            $table->string('co_financing')->nullable();

            $table->json('custom_members')->default('[]');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
