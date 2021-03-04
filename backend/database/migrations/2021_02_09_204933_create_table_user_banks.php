<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserBanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_banks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('用户ID');
            $table->string('account_name',20)->comment('账户名');
            $table->string('account_number',18)->comment('卡号');
            $table->smallInteger('banks_id')->comment('银行ID');
            $table->integer('province_id')->comment('省份');
            $table->integer('city_id')->comment('开户城市');
            $table->integer('district_id')->comment('开户地区');
            $table->string('branch',64)->comment('分行/开户行');
            $table->tinyInteger('status')->default(0)->comment('状态：0：不可用 1：可用');
            $table->tinyInteger('is_delete')->default(1)->comment('是否删除：0：删除 1：未删除');
            $table->tinyInteger('is_open')->default(0)->comment('是否开启收款：0：关闭 1：开启');
            $table->decimal('limit_amount',8,2)->comment('每日限额');
            $table->timestamp('created_at')->default(DB::raw('LOCALTIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

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
        Schema::dropIfExists('user_banks');
    }
}
