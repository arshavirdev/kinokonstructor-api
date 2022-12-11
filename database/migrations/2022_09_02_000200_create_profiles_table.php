<?php

use App\Models\User;
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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['draft', 'moderation', 'accepted', 'rejected'])->default('draft');
            $table->boolean('is_verified')->default(false);
            $table->foreignIdFor(User::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('firstname');
            $table->string('lastname');
            $table->string('middlename')->nullable();

            $table->enum('gender', ['m', 'f']);
            $table->string('city');
            $table->date('birthday');

            $table->string('phone')->unique();

            $table->boolean('is_org');
            $table->string('org_reg_id')->nullable();
            $table->string('org_name')->nullable();
            $table->string('org_position')->nullable();

            $table->boolean('is_entrepreneur');
            $table->string('entrepreneur_reg_id')->nullable();

            $table->jsonb('regions')->default('[]');

            $table->longText('portfolio')->nullable();
            $table->longText('mass_media_mentions')->nullable();

            $table->string('socials_vk')->nullable();
            $table->string('socials_tg')->nullable();
            $table->string('socials_ok')->nullable();

            $table->jsonb('privacy_hide')->default('[]');

            $table->timestamps();
        });
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement("CREATE INDEX index_profiles_fullname ON profiles using gin ( (COALESCE(lastname, '') || ' ' || COALESCE(firstname, '') || ' ' || COALESCE(middlename, '')) gin_trgm_ops);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP INDEX index_profiles_fullname;');
        Schema::dropIfExists('profiles');
    }
};
