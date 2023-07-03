<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMilitaryCodeIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('military_code_id')->nullable()->default(null);
            $table->foreign('military_code_id')->references('id')->on('military_codes');
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
            if($table->hasColumn('military_code_id')) {
                $table->dropForeign(['military_code_id']);
                $table->dropColumn('military_code_id');
            }
        });
    }
}
