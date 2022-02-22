<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiqpayPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liqpay_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableMorphs('order');
            $table->string('uuid')->unique();
            $table->string('language');
            $table->string('action');
            $table->text('description');
            $table->string('currency');
            $table->unsignedDecimal('amount');
            $table->string('status')->nullable();
            $table->string('err_code')->nullable();
            $table->string('err_description')->nullable();
            $table->string('sender_card_bank')->nullable();
            $table->string('sender_card_country')->nullable();
            $table->string('sender_card_mask2')->nullable();
            $table->string('sender_card_type')->nullable();
            $table->unsignedDecimal('sender_commission')->nullable();
            $table->string('sender_first_name')->nullable();
            $table->string('sender_last_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->unsignedTinyInteger('is_successful')->nullable();
            $table->timestamps();

            $table->index(['is_successful', 'created_at', 'id']);
            $table->index(['created_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liqpay_payments');
    }
}
