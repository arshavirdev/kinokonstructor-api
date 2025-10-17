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
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn(['region', 'country']);
        });

        Schema::table('contests', function (Blueprint $table) {
            $table->jsonb('region_ids')->default('[]');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn(['region_ids']);
        });

        Schema::table('contests', function (Blueprint $table) {
            $table->string('region')->nullable();
            $table->string('country')->nullable();
        });
    }
};
