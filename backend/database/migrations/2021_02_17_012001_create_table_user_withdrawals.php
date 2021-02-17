<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserWithdrawals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_withdrawals', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('amount', 15, 4)->comment('交易金额');
            $table->integer('user_id')->comment('提现散户ID');
            $table->decimal('merchant_fee', 15, 4)->default(0)->comment('用户的手续费，负数扣除，整数返还');
            $table->decimal('third_fee', 15, 4)->default(0)->comment('平台手续费');
            $table->tinyInteger('status')->default(0)->comment('状态，０提现中，１已审核，２提现成功，３提现失败');
            $table->jsonb('extra')->default(json_encode([]))->comment('扩展数据：存储收款银行信息');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_withdrawals');
    }
}
