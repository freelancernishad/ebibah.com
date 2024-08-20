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
               // Add the active_package_id column
               $table->unsignedBigInteger('active_package_id')->nullable()->after('id'); // Replace 'some_existing_column' with an actual column name to position this new column appropriately

               // Add foreign key constraint if needed
               $table->foreign('active_package_id')->references('id')->on('packages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraint if it exists
            $table->dropForeign(['active_package_id']);

            // Drop the active_package_id column
            $table->dropColumn('active_package_id');
        });
    }
};
