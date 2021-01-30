<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funds', function (Blueprint $table) {
            $table->integer('type')->comment('类型：1商户 2散户 3代理');
            $table->integer('third_id')->comment('商户、用户、代理 ID');
            $table->decimal('balance', 14, 4)->default(0)->comment('帐户余额(可用+冻结)');
            $table->decimal('hold_balance', 14, 4)->default(0)->comment('冻结金额');

            $table->primary(['type','third_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('funds');
    }
}
