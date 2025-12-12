<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        // Migrate existing string "role" → jsonb array "roles"
        DB::statement("
            UPDATE users
            SET roles = jsonb_build_array(role)
            WHERE role IS NOT NULL
        ");

        // Remove the old column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate the old column for rollback
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable();
        });

        // Convert back for rollback
        DB::statement("
            UPDATE users
            SET role = roles->>0
            WHERE roles IS NOT NULL
        ");
    }
};
