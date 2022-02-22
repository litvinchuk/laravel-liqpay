<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiqpayPaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liqpay_payment_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('liqpay_payment_id')->nullable()->index();
            $table->string('type');
            $table->string('ip');
            $table->text('payload')->nullable();
            $table->unsignedTinyInteger('is_successfuly_handled')->nullable();
            $table->text('fail_reason')->nullable();
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
        Schema::dropIfExists('liqpay_payment_logs');
    }
}
