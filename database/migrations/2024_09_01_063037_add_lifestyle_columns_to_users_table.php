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
            $table->string('diet')->nullable()->after('step');
            $table->string('drinking')->nullable()->after('diet');
            $table->string('other_lifestyle_preferences')->nullable()->after('drinking');
            $table->string('smoking')->nullable()->after('other_lifestyle_preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('diet');
            $table->dropColumn('drinking');
            $table->dropColumn('other_lifestyle_preferences');
            $table->dropColumn('smoking');
        });
    }
};
