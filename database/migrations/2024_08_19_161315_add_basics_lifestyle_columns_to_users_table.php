<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('height');
            $table->string('personal_values')->nullable()->after('birth_place');
            $table->string('disability')->nullable()->after('personal_values');
            $table->string('posted_by')->nullable()->after('disability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('birth_place');
            $table->dropColumn('personal_values');
            $table->dropColumn('disability');
            $table->dropColumn('posted_by');
        });
    }
};
