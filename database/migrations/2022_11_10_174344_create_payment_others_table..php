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
        Schema::create('payment_others', function (Blueprint $table) {
            $table->id();
            $table->string('gateway',120);
            $table->string('label',60);
            $table->string('detail',120);
            $table->string('img_url',255);
            $table->string('api_sufix',255);
            $table->boolean('only_app')->default(true);
            $table->boolean('only_pdv')->default(false);
            $table->boolean('only_totem')->default(true);
            $table->tinyInteger('order');
            $table->string('inserted_for',60);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_others');
    }
};
