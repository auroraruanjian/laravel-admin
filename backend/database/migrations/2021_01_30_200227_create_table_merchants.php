<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMerchants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * 商户表
         */
        Schema::create('merchants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id')->default(0)->comment('代理的ID,0:表示系统');
            $table->string('account',60)->comment('商户号');
            $table->string('nickname',20)->comment('昵称');
            $table->text('system_public_key','')->comment('系统公钥');
            $table->text('system_private_key','')->comment('系统私钥');
            $table->text('merchant_public_key','')->comment('商户公钥');
            $table->text('merchant_private_key','')->comment('商户私钥');
            $table->string('md5_key','')->comment('MD5签名校验秘钥');
            $table->jsonb('payment_channel')->default(json_encode([]))->comment('分配的支付通道以及费率');
            $table->tinyInteger('status')->default(0)->comment('状态:0 正常，1 冻结');
            $table->jsonb('extra')->default(json_encode([]))->comment('扩展数据，存放返点{rebates,} 费率数据');

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });

        $this->_permission();
    }

    private function _permission()
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => 0,
            'rule'        => 'merchant_system',
            'name'        => '商户系统',
            'extra'       => json_encode(['icon' => 'merchant','component'=>'Layout']),
        ]);

        $this->_addMerchantIndex( $id );
        $this->_addMerchantOrders( $id );
        //$this->_addMerchantDeposit( $id );
        //$this->_addMerchantWithdrawal( $id );
        //$this->_addMerchantSelfDeposit( $id );
        $this->_addMerchantSelfWithdrawal( $id );
        $this->_addMerchantProfit( $id );
    }

    private function _addMerchantIndex( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'merchant/index',
            'name'        => '商户管理',
            'extra'       => json_encode(['icon' => 'client','component'=>'merchant/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $id,
                'rule'        => 'merchant/create',
                'name'        => '增加商户',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $id,
                'rule'        => 'merchant/edit',
                'name'        => '编辑商户',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $id,
                'rule'        => 'merchant/delete',
                'name'        => '删除商户',
                'extra'       => json_encode(['hidden' => true]),
            ],
        ]);
    }

    private function _addMerchantOrders( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'merchant_orders/index',
            'name'        => '商户账变',
            'extra'       => json_encode(['icon' => 'client','component'=>'orders/merchant']),
        ]);
    }

    private function _addMerchantDeposit( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'merchant_deposit/index',
            'name'        => '商户代收订单',
            'extra'       => json_encode(['icon' => 'deposit','component'=>'deposit/merchant']),
        ]);
    }

    private function _addMerchantWithdrawal( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'merchant_withdrawal/index',
            'name'        => '商户代付订单',
            'extra'       => json_encode(['icon' => 'withdrawal','component'=>'merchant/index']),
        ]);
    }

    private function _addMerchantSelfDeposit( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'merchant_self_deposit/index',
            'name'        => '商户充值审核',
            'extra'       => json_encode(['icon' => 'money','component'=>'merchant/index']),
        ]);
    }

    private function _addMerchantSelfWithdrawal( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'merchant_self_withdrawal/index',
            'name'        => '商户提款审核',
            'extra'       => json_encode(['icon' => 'money','component'=>'merchant/index']),
        ]);
    }

    private function _addMerchantProfit( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'merchant_profit/index',
            'name'        => '商户盈亏统计',
            'extra'       => json_encode(['icon' => 'orders_record','component'=>'merchant/index']),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants');
    }
}
