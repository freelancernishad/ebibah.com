<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexnoToPackageServicesTable extends Migration
{
    public function up()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->integer('indexno')->after('slug')->default(0); // Add 'indexno' column with default value
        });
    }

    public function down()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->dropColumn('indexno');
        });
    }
}
