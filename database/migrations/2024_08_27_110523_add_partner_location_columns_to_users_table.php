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
            $table->string('partner_country')->nullable()->after('partner_professional_details');
            $table->string('partner_state')->nullable()->after('partner_country');
            $table->string('partner_city')->nullable()->after('partner_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('partner_country');
            $table->dropColumn('partner_state');
            $table->dropColumn('partner_city');
        });
    }
};
