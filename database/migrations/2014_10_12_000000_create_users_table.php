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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('linkedin_id')->nullable();
            $table->longText('profile_pic')->nullable();
            $table->integer('connections_size')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('positions')->nullable();
            $table->string('specialties')->nullable();
            $table->string('summary')->nullable();
            $table->string('industry')->nullable();
            $table->string('current_share')->nullable();
            $table->text('headline')->nullable()->default(null);
            $table->string('location')->nullable();
            $table->boolean('jc_penny')->nullable()->default(null);
            $table->rememberToken();

            $table->string('website_link')->nullable()->default(null);
            $table->unsignedBigInteger('ibc_company_id')->nullable()->default(null);
            $table->string('grad_year', 10)->nullable()->default(null);
            $table->string('type', 20)->nullable()->default(null);

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
        Schema::dropIfExists('users');
    }
};
