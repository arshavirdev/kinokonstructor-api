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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('short_description');
            $table->jsonb('category')->default('[]');
            $table->string('format');
            $table->text('description');
            $table->string('location')->nullable();
            $table->jsonb('parameters')->default('[]');
            $table->jsonb('company')->default('{}');
            $table->dateTime('date')->default('now()');
            $table->boolean('is_archived')->default(false);
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
        Schema::dropIfExists('events');
    }
};
