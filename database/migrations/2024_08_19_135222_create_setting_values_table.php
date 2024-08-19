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
        Schema::create('setting_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained()->onDelete('cascade');
            $table->string('value_id'); // e.g., A+, B+, etc.
            $table->string('name'); // e.g., A+, Divorce, Islam, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_values');
    }
};
