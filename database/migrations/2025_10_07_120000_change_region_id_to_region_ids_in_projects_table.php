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
         Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['region_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
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
       Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['region_ids']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('region_id')->constrained('regions')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
