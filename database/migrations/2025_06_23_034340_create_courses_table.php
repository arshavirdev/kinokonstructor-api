<?php

use App\Models\Course;
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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->dateTime('start_date');
            $table->dateTime('application_start_date');
            $table->dateTime('application_end_date');
            $table->text('duration')->nullable();
            $table->text('price')->nullable();
            $table->enum('study_format', Course::STUDY_FORMATS)->default(Course::ONLINE);
            $table->jsonb('teachers')->default('[]');
            $table->foreignId('region_id')->constrained('regions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('is_archived')->default(false);
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
        Schema::dropIfExists('courses');
    }
};
