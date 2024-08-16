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
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dateTime('started_date')->nullable()->after('page');
            $table->dateTime('end_date')->nullable()->after('started_date');
            $table->string('default_banner')->nullable()->after('end_date');
            $table->string('company_name')->nullable()->after('default_banner');
            $table->string('company_address')->nullable()->after('company_name');
            $table->string('provider_name')->nullable()->after('company_address');
            $table->string('provider_position')->nullable()->after('provider_name');
            $table->dateTime('agreement_date')->nullable()->after('provider_position');
            $table->string('status')->nullable()->after('agreement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn([
                'started_date', 'end_date', 'default_banner', 
                'company_name', 'company_address', 'provider_name', 
                'provider_position', 'agreement_date', 'status'
            ]);
        });
    }
};
