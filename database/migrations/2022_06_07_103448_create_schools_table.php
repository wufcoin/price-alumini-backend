<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('high_school')->nullable();
            $table->tinyText('color_1')->nullable();
            $table->tinyText('color_2')->nullable();
            $table->tinyText('logo_1')->nullable();
            $table->tinyText('logo_2')->nullable();
            $table->tinyText('slogan')->nullable();
            $table->tinyText('acronym')->nullable();
            $table->tinyText('banner')->nullable();
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
        Schema::dropIfExists('schools');
    }
}
