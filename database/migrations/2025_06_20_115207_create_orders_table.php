<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('vendor_id');

            // Buyer info
            $table->string('fullname');
            $table->string('whatsapp');
            $table->string('email');
            $table->text('address');
            $table->string('mobile_number')->nullable();

            // Delivery info
            $table->string('country');
            $table->string('state');
            $table->string('city');

            // Prices
            $table->decimal('delivery_price', 10, 2);
            $table->decimal('product_price', 10, 2);
            $table->decimal('total_price', 10, 2);

            // Payment and status
            $table->enum('payment_type', ['pay_now', 'pay_on_delivery']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
