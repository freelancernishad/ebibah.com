<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_partner_qualifications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerQualificationsTable extends Migration
{
    public function up()
    {
        Schema::create('partner_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to the users table
            $table->string('qualification'); // Add any other necessary fields
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_qualifications');
    }
}
