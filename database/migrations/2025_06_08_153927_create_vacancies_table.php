<?php

use App\Models\Vacancy;
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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->enum('format', Vacancy::FORMATS)->default(Vacancy::OFFICE);
            $table->enum('employment_type', Vacancy::EMPLOYMENT_TYPES)->default(Vacancy::FULL_TIME);
            $table->integer('position_id');
            $table->integer('department_id')->nullable();
            $table->text('description');
            $table->jsonb( 'company')->default(DB::raw("'{}'::jsonb"));
            $table->decimal('salary', 10, 2)->nullable();
            $table->integer('experience')->nullable();
            $table->boolean('is_experience_required')->default(true);
            $table->foreignId('region_id')->constrained('regions')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::dropIfExists('vacancies');
    }
};
