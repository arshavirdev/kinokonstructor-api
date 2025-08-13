<?php

use App\Models\Course;
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
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['region_id', 'study_format']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->jsonb('region_ids')->default('[]');
            $table->string('study_format')->default(Course::FULL_TIME);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['region_ids', 'study_format']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('region_id')->constrained('regions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('study_format', Course::STUDY_FORMATS)->default(Course::ONLINE);
        });
    }
};
