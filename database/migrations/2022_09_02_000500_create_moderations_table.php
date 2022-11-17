<?php

use App\Traits\Moderation\Status;
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
        Schema::create('moderations', function (Blueprint $table) {
            $table->id();
            $table->morphs('moderatable');

            $table->enum('status', [Status::PENDING, Status::REJECTED, Status::ACCEPTED]);
            $table->string('comment')->nullable();
            $table->json('data')->nullable();

            $table->boolean('is_last')->default(true);

            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::dropIfExists('moderations');
    }
};
