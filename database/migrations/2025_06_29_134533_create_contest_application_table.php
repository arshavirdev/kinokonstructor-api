<?php

use App\Models\ContestApplication;
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
        Schema::create('contest_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('applicant_id')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('project_id')->nullable();
            $table->text('description')->default('');
            $table->enum('status', ContestApplication::STATUSES)->default(ContestApplication::STATUS_PENDING);
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
        Schema::dropIfExists('contest_applications');
    }
};
