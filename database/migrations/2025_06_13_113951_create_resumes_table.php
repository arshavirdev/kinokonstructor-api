<?php

use App\Models\Resume;
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
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->decimal('salary_expectation', 10, 2)->nullable();
            $table->enum('work_format', Resume::FORMATS)->default(Resume::WORK_FORMAT_ANY);
            $table->string('location')->nullable();
            $table->string('experience_years')->nullable();
            $table->text('experience_description')->nullable();
            $table->text('bio')->nullable();
            $table->foreignId('position_id')->constrained('occupations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('profiles')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::dropIfExists('resumes');
    }
};
