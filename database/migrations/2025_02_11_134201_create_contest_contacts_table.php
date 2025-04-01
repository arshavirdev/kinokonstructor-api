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
        Schema::create('contest_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Contest::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('website')->nullable();
            $table->string('social_media')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('button_name')->nullable();
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
        Schema::dropIfExists('contest_contacts');
    }
};
