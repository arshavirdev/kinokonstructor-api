<?php

use App\Models\Region;
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
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->foreignId('applicant_id')->nullable()->constrained('profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('years_rating')->nullable();
            $table->foreignIdFor(Region::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('city')->nullable();
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
            $table->dropColumn(['start_date', 'end_date', 'applicant_id', 'years_rating', 'region_id', 'city']);
        });
    }
};
