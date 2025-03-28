<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bar_id');
            $table->string('seller_id', 40);
            $table->string('soft_descriptor', 45);
            $table->uuid('client_identify');
            $table->char('order_num', 15);
            $table->string('brand', 60);
            $table->char('final_numbers', 4);
            $table->char('type', 1); // (C)redit or (D)ebit 
            $table->double('amount', 8, 2);
            $table->string('payment_id', 45)->unique()->nullable();
            $table->string('status', 45)->nullable();
            $table->string('authorization_code', 45)->nullable();
            $table->dateTimeTz('authorized_at', $precision = 0)->nullable();
            $table->string('reason_code', 45)->nullable();
            $table->string('reason_message', 250)->nullable();
            $table->string('acquirer', 45)->nullable();
            $table->string('acquirer_transaction_id', 45)->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('terminal_nsu')->nullable();
            $table->dateTimeTz('received_at', $precision = 0)->nullable();
            $table->boolean('delayed')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
