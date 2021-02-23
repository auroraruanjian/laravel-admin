<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePaymentChannelDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_channel_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('payment_channel_id')->unsigned()->comment('支付通道ID');
            $table->smallInteger('payment_method_id')->unsigned()->comment('支付渠道ID');
            $table->decimal('rate',15,4)->default(0)->comment('第三方费率(%),第三方收取平台的费率');
            $table->decimal('min_amount',15,4)->default(0)->comment('最低支付金额');
            $table->decimal('max_amount',15,4)->default(0)->comment('最高支付金额');
            $table->boolean('status')->default(false)->comment('是否启用');
            $table->string('start_time')->default('00:00:00')->comment('每天开始时间');
            $table->string('end_time')->default('00:00:00')->comment('每天开始时间,如果结束时间小于开始时间，则表示跨天');
            $table->jsonb('extra')->default('{}')->comment('扩展参数');
            $table->timestamps();

            $table->softDeletes();
            $table->unique(['payment_channel_id','payment_method_id']);
        });

        $this->_data();
    }



    private function _data()
    {
        DB::table('payment_channel')->insert([
            [
                'name'                  => '点对点支付',
                'payment_category_id'   => 1,
                'channel_param'         => json_encode([]),
                'status'                => true,
            ]
        ]);

        DB::table('payment_channel_detail')->insert([
            // 网银
            [
                'payment_channel_id'    => 1,
                'payment_method_id'     => 1,
                'rate'                  => 1.3,
                'min_amount'            => 0.01,
                'max_amount'            => 10000,
                'status'                => true,
                'start_time'            => '00:00:00',
                'end_time'              => '00:00:00',
                'extra'                 => json_encode([]),
            ],
            // 支付宝扫码
            [
                'payment_channel_id'    => 1,
                'payment_method_id'     => 2,
                'rate'                  => 1.3,
                'min_amount'            => 0.01,
                'max_amount'            => 10000,
                'status'                => true,
                'start_time'            => '00:00:00',
                'end_time'              => '00:00:00',
                'extra'                 => json_encode([]),
            ],
            // 支付宝转卡
            [
                'payment_channel_id'    => 1,
                'payment_method_id'     => 3,
                'rate'                  => 1.3,
                'min_amount'            => 0.01,
                'max_amount'            => 10000,
                'status'                => true,
                'start_time'            => '00:00:00',
                'end_time'              => '00:00:00',
                'extra'                 => json_encode([]),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_channel_detail');
    }
}
