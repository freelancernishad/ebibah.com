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
        Schema::create('notices', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('title'); // Title of the notice
            $table->text('description')->nullable(); // Description of the notice
            $table->date('start_date')->nullable(); // Start date of the notice
            $table->date('end_date')->nullable(); // End date of the notice
            $table->boolean('is_active')->default(true); // Whether the notice is active
            $table->string('type')->default('general'); // Type of notice (e.g., general, top-bar)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
