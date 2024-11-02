<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['fixed', 'percent']); // Type of discount
            $table->decimal('discount_value', 8, 2); // Fixed amount or percentage value
            $table->date('expiry_date'); // Expiration date of the coupon
            $table->enum('type', ['profile', 'package']); // Type of coupon
            $table->boolean('is_active')->default(true); // Active status of the coupon
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
