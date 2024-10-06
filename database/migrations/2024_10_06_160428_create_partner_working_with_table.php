<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_partner_working_with_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerWorkingWithTable extends Migration
{
    public function up()
    {
        Schema::create('partner_working_with', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to the users table
            $table->string('working_with'); // Add any other necessary fields
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_working_with');
    }
}
