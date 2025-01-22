<?php

use App\Traits\Moderation\Status;
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
        DB::statement(<<<SQL
            ALTER TABLE profiles
                ALTER COLUMN user_id    DROP NOT NULL,
                ALTER COLUMN phone      DROP NOT NULL,
                ALTER COLUMN city       DROP NOT NULL,
                ALTER COLUMN birthday   DROP NOT NULL,
                ALTER COLUMN is_org     SET DEFAULT false,
                ALTER COLUMN is_entrepreneur    SET DEFAULT false;
        SQL);
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('member_id')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(<<<SQL
            ALTER TABLE profiles
                ALTER COLUMN user_id    SET NOT NULL,
                ALTER COLUMN phone      SET NOT NULL,
                ALTER COLUMN city       SET NOT NULL,
                ALTER COLUMN birthday   SET NOT NULL,
                ALTER COLUMN is_org     DROP DEFAULT,
                ALTER COLUMN is_entrepreneur    DROP DEFAULT;
        SQL);
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('member_id');
        });
    }
};
