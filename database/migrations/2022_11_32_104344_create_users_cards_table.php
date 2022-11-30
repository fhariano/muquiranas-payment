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
        Schema::create('users_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('identiy');
            $table->uuid('card_id');
            $table->string('type', 10); // (Credit or Debit)
            $table->string('brand', 60);
            $table->char('last_four_digits', 4);
            $table->char('bin', 6);
            $table->tinyInteger('expiration_month');
            $table->tinyInteger('expiration_year');
            $table->string('cardholder_name', 26);
            $table->string('first_name', 26);
            

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
