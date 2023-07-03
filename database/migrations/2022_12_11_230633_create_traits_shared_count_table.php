<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraitsSharedCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('traits_shared_count', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_id_other_user');
            $table->integer('connection_size')->nullable();
            $table->unsignedBigInteger('countries_count');
            $table->unsignedBigInteger('graduation_count');
            $table->unsignedBigInteger('schools_count');
            $table->unsignedBigInteger('military_branches_count');
            $table->unsignedBigInteger('military_codes_count');
            $table->unsignedBigInteger('associations_count');
            $table->unsignedBigInteger('hobbies_count');
            $table->unsignedBigInteger('industries_count');
            $table->unsignedBigInteger('total_traits_shared_count');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::table('traits_shared_count', function (Blueprint $table) {
            Schema::dropIfExists('traits_shared_count');
        });
    }
}
