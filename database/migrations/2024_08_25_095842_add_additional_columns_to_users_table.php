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
            $table->string('profile_created_by')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('community')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('sub_community')->nullable();
            $table->string('family_values')->nullable();
            $table->string('family_location')->nullable();
            $table->string('family_type')->nullable();
            $table->string('family_native_place')->nullable();
            $table->integer('total_siblings')->nullable();
            $table->integer('siblings_married')->nullable();
            $table->integer('siblings_not_married')->nullable();
            $table->string('state')->nullable();
            $table->text('about_myself')->nullable();
            $table->string('partner_age')->nullable();
            $table->string('partner_marital_status')->nullable();
            $table->string('partner_religion')->nullable();
            $table->string('partner_community')->nullable();
            $table->string('partner_mother_tongue')->nullable();
            $table->json('partner_qualification')->nullable();
            $table->json('partner_working_with')->nullable();
            $table->json('partner_profession')->nullable();
            $table->text('partner_professional_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_created_by',
                'whatsapp',
                'community',
                'mother_tongue',
                'sub_community',
                'family_values',
                'family_location',
                'family_type',
                'family_native_place',
                'total_siblings',
                'siblings_married',
                'siblings_not_married',
                'state',
                'about_myself',
                'partner_age',
                'partner_marital_status',
                'partner_religion',
                'partner_community',
                'partner_mother_tongue',
                'partner_qualification',
                'partner_working_with',
                'partner_profession',
                'partner_professional_details',
            ]);
        });
    }
};
