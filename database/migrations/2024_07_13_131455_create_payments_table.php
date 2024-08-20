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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_purchase_id'); // Foreign key to PackagePurchase
            $table->string('union')->nullable();
            $table->string('trxId')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('applicant_mobile')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->date('date')->nullable();
            $table->string('month')->nullable();
            $table->year('year')->nullable();
            $table->string('paymentUrl')->nullable();
            $table->text('ipnResponse')->nullable();
            $table->string('method')->nullable();
            $table->string('payment_type')->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('package_purchase_id')->references('id')->on('package_purchases')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
