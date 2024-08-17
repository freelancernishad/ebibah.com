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
            $table->string('profile_for')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->nullable();
            $table->string('highest_qualification')->nullable();
            $table->string('college_name')->nullable();
            $table->string('working_sector')->nullable();
            $table->string('profession')->nullable();
            $table->string('profession_details')->nullable();
            $table->string('monthly_income')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('living_country')->nullable();
            $table->string('currently_living_in')->nullable();
            $table->string('city_living_in')->nullable();
            $table->text('family_details')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('bodyType')->nullable();
            $table->string('race')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('mother_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_for',
                'date_of_birth',
                'gender',
                'first_name',
                'last_name',
                'father_name',
                'mother_name',
                'marital_status',
                'religion',
                'nationality',
                'highest_qualification',
                'college_name',
                'working_sector',
                'profession',
                'profession_details',
                'monthly_income',
                'father_occupation',
                'mother_occupation',
                'living_country',
                'currently_living_in',
                'city_living_in',
                'family_details',
                'height',
                'weight',
                'bodyType',
                'race',
                'blood_group',
                'mother_status'
            ]);
        });
    }
};
