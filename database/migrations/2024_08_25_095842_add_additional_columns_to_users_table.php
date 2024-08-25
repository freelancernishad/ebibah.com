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
            $table->string('profile_created_by')->nullable()->after('posted_by');
            $table->string('whatsapp')->nullable()->after('profile_created_by');
            $table->string('community')->nullable()->after('whatsapp');
            $table->string('mother_tongue')->nullable()->after('community');
            $table->string('sub_community')->nullable()->after('mother_tongue');
            $table->string('family_values')->nullable()->after('sub_community');
            $table->string('family_location')->nullable()->after('family_values');
            $table->string('family_type')->nullable()->after('family_location');
            $table->string('family_native_place')->nullable()->after('family_type');
            $table->integer('total_siblings')->nullable()->after('family_native_place');
            $table->integer('siblings_married')->nullable()->after('total_siblings');
            $table->integer('siblings_not_married')->nullable()->after('siblings_married');
            $table->string('state')->nullable()->after('siblings_not_married');
            $table->text('about_myself')->nullable()->after('state');
            $table->string('partner_age')->nullable()->after('about_myself');
            $table->string('partner_marital_status')->nullable()->after('partner_age');
            $table->string('partner_religion')->nullable()->after('partner_marital_status');
            $table->string('partner_community')->nullable()->after('partner_religion');
            $table->string('partner_mother_tongue')->nullable()->after('partner_community');
            $table->json('partner_qualification')->nullable()->after('partner_mother_tongue');
            $table->json('partner_working_with')->nullable()->after('partner_qualification');
            $table->json('partner_profession')->nullable()->after('partner_working_with');
            $table->text('partner_professional_details')->nullable()->after('partner_profession');
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
