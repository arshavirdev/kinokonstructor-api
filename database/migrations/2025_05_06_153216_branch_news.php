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
        Schema::create('branch_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('branch_id')->constrained('regional_branches')->onDelete('cascade');
            $table->jsonb('category')->default(DB::raw("'[]'::jsonb"));
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('branch_news');
    }
};
