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
        Schema::create('profile_education', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Profile::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('institution');
            $table->string('speciality');
            $table->year('start');
            $table->year('end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profile_education');
    }
};
