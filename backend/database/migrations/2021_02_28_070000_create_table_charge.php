<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCharge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->integer('order_id')->comment('帐变ID');
            $table->integer('rebates_id')->comment('佣金类型：1 充值 2提款');
            $table->integer('deposit_withdrawal_id')->comment('充值订单ID');
            $table->smallInteger('type')->comment('账变类型：1：代理 2：商户 3：散户');
            $table->integer('third_id')->comment('商户、用户、代理 ID');
            $table->decimal('rate', 14, 4)->comment('费率');
            $table->decimal('amount', 14, 4)->comment('本条账变所产生的资金变化量');
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
        Schema::dropIfExists('charge');
    }
}
