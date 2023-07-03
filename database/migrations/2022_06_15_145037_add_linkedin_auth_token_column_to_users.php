<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkedinAuthTokenColumnToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText('linkedin_auth_token')->nullable();
            $table->bigInteger('linkedin_auth_token_expire_at')->nullable();
            $table->longText('linkedin_auth_refresh_token')->nullable();
            $table->bigInteger('linkedin_auth_refresh_token_expire_at')->nullable();
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
            $table->dropColumn('linkedin_auth_token');
            $table->dropColumn('linkedin_auth_token_expire_at');
            $table->dropColumn('linkedin_auth_refresh_token');
            $table->dropColumn('linkedin_auth_refresh_token_expire_at');
        });
    }
}
