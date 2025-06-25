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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->text('description');
            $table->string('video_link')->nullable();
            $table->string('address')->nullable();

            $table->string('speaker_first_name');
            $table->string('speaker_last_name');
            $table->text('speaker_bio');

            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('lessons');
    }
};
