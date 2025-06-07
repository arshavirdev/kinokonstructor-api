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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('short_description');
            $table->text('description');
            $table->jsonb('category')->default('[]');
            $table->foreignId('owner_id')->constrained('profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->jsonb('parameters')->default('[]');
            $table->jsonb(column: 'company')->default('{}');
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
        Schema::dropIfExists('resources');
    }
};
