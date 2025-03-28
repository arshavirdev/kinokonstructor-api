<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->nullable();
            $table->string('years_held')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->text('description')->nullable();
            $table->text('conditions')->nullable();
            $table->json('deadlines')->default('[]');
            $table->text('prizes')->nullable();
            $table->text('jury')->nullable();
            $table->text('organizers')->nullable();
            $table->string('online_application')->nullable();
            $table->string('video_link')->nullable();
            $table->boolean('is_archived')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contests');
    }
};
