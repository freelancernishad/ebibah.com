<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePartnerDetailsFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the specified columns
            $table->dropColumn([
                'partner_professional_details',
                'partner_country',
                'partner_state',
                'partner_city',
                'partner_marital_status',
                'partner_religion',
                'partner_community',
                'partner_mother_tongue',
            ]);
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
            // Add the columns back in case of rollback
            $table->json('partner_professional_details')->nullable();
            $table->json('partner_country')->nullable();
            $table->json('partner_state')->nullable();
            $table->json('partner_city')->nullable();
            $table->string('partner_marital_status')->nullable();
            $table->string('partner_religion')->nullable();
            $table->string('partner_community')->nullable();
            $table->string('partner_mother_tongue')->nullable();
        });
    }
}
