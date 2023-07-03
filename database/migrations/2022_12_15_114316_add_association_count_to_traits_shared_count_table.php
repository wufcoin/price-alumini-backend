<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssociationCountToTraitsSharedCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('traits_shared_count', function (Blueprint $table) {
            $table->unsignedBigInteger('association_count')->nullable()->after('graduation_count');
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
            if($table->hasColumn('association_count')) {
                $table->dropColumn('association_count');
            }
        });
    }
}
