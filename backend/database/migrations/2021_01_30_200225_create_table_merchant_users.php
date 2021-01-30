<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMerchantUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->comment('商户ID');
            $table->string('username',20)->comment('用户名');
            $table->string('nickname',20)->comment('昵称');
            $table->string('phone',20)->comment('手机号');
            $table->string('password')->comment('密码');
            $table->string('pay_password')->comment('支付密码');
            $table->tinyInteger('status')->default(0)->comment('状态:0 正常，1 冻结');
            $table->ipAddress('last_ip')->nullable()->comment('最后一次登录IP');
            $table->timestamp('last_time')->nullable()->comment('最后登录时间');
            $table->string('last_session', 64)->default('')->comment('最近登陆SESSIONID');
            $table->string('google_key', 16)->default('')->comment('谷歌登录器秘钥');
            $table->string('unionid')->nullable()->comment('微信登陆唯一ID');

            $table->rememberToken();
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
        Schema::dropIfExists('merchant_users');
    }
}
