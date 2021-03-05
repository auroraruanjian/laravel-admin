<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_deposits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('用户ID');

            $table->decimal('amount', 15, 4)->comment('交易金额');
            $table->decimal('real_amount', 15, 4)->nullable()->comment('实际支付金额');

            $table->string('third_order_no', 100)->default('')->comment('银行交易流水或是第三方交易流水');

            $table->integer('order_id')->nullable()->comment('本站帐变流水号');
            $table->integer('accountant_admin_id')->nullable()->comment('会计，通过审核的管理员');
            $table->integer('cash_admin_id')->nullable()->comment('出纳，确认充值的管理员');

            $table->tinyInteger('status')->default(0)->comment('状态，０支付中，１已审核，２充值成功，３充值失败');
            $table->tinyInteger('report_status')->default(0)->comment('报表汇总状态：0. 未开始; 1. 进行中; 2. 完成');

            $table->string('manual_postscript')->default('')->comment('人工输入附言');
            $table->decimal('manual_amount', 15, 4)->default(0)->comment('人工输入实际充值金额');
            $table->decimal('manual_fee', 15, 4)->default(0)->comment('人工输入实际手续费');

            $table->decimal('merchant_fee', 15, 4)->default(0)->comment('用户的手续费，负数扣除，整数返还');
            $table->decimal('third_fee', 15, 4)->default(0)->comment('平台手续费');
            $table->string('account_number', 50)->default('')->comment('商户号或银行卡号');
            $table->string('error_type', 64)->default('')->comment('违规类型');

            $table->string('remark', 64)->default('')->comment('订单备注');
            $table->string('admin_remark', 64)->default('')->comment('管理员备注');
            $table->jsonb('extra')->default(json_encode([]))->comment('扩展字段');
            $table->ipAddress('ip')->nullable()->comment("用户IP地址");
            $table->timestamp('deal_at')->nullable()->comment("管理处理时间");
            $table->timestamp('done_at')->nullable()->comment("到账实际");
            $table->timestamp('created_at')->default(DB::raw('LOCALTIMESTAMP'))->comment('申请时间');

            $table->index('created_at');
            $table->index(['status', 'report_status']);
            $table->index(['status', 'created_at']);
        });

        DB::statement('ALTER SEQUENCE IF EXISTS "deposits_id_seq" RESTART WITH 1000000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_deposits');
    }
}
