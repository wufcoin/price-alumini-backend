<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGraduationToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('graduation_id')->nullable()->after('country_id');
            $table->index('graduation_id');
            $table->foreign('graduation_id')->references('id')->on('countries');
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
            if($table->hasColumn('graduation_id')) {
                $table->dropForeign(['graduation_id']);
                $table->dropColumn('graduation_id');
            }
        });
    }
}
