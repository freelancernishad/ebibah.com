<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_partner_professions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerProfessionsTable extends Migration
{
    public function up()
    {
        Schema::create('partner_professions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to the users table
            $table->string('profession'); // Add any other necessary fields
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_professions');
    }
}
