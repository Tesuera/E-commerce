<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->text('unique_id');
            $table->text('token');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('product_list');
            $table->text('from_date');
            $table->text('to_date');
            $table->enum('purchase_method', ['Paypal', 'WavePay', 'KBZpay', 'Cash on delivery']);
            $table->integer('total_amount');
            $table->string('name');
            $table->text('address');
            $table->text('phone_number');
            $table->text('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
