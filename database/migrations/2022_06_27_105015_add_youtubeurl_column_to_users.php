<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYoutubeurlColumnToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('youtube_video_url')->nullable();
            $table->longText('how_help_others')->nullable();
            $table->longText('how_help_looking_for')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('youtube_video_url');
            $table->dropColumn('how_help_others');
            $table->dropColumn('how_looking_for');
        });
    }
}
