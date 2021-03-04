<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * 用户表
         */
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
//            $table->integer('top_id')->default(0)->comment('总代用户 ID，总代为 0');
//            $table->integer('parent_id')->default(0)->comment('父级用户 ID，总代为 0');
//            $table->jsonb('parent_tree')->default('[]')->comment('父级树');
            //$table->smallInteger('user_group_id')->comment('用户组 ID');
            //$table->integer('merchant_id')->nullable()->comment('商户号ID');
            $table->integer('agent_id')->default(0)->comment('代理的ID,0:表示系统');
            $table->string('username',32)->comment('用户名');
            $table->string('nickname',20)->unique()->comment('昵称');
            $table->string('password')->comment('密码');
            $table->string('security_password')->nullable()->comment('密码');
            $table->tinyInteger('status')->default(0)->comment('状态:0 正常，1 冻结');
            $table->ipAddress('last_ip')->nullable()->comment('最后一次登录IP');
            $table->timestamp('last_time')->nullable()->comment('最后登录时间');
            $table->string('last_session', 64)->default('')->comment('最近登陆SESSIONID');
            $table->string('google_key', 16)->default('')->comment('谷歌登录器秘钥');
            $table->string('unionid')->nullable()->comment('微信登陆唯一ID');
            $table->jsonb('extra')->default(json_encode([]))->comment('扩展数据，存放返点{rebates,} 费率数据');

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();

            //$table->unique(['merchant_id','username']);
            $table->index('username');
        });

        $this->_permission();
    }

    private function _permission()
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => 0,
            'rule'        => 'user_system',
            'name'        => '散户系统',
            'extra'       => json_encode(['icon' => 'list','component'=>'Layout']),
        ]);

        $this->_addUserIndex( $id );
        $this->_addUserOrders( $id );
        $this->_addUserDeposit( $id );
        $this->_addUserWithdrawal( $id );
        $this->_addUserSelfDeposit( $id );
        $this->_addUserSelfWithdrawal( $id );
        $this->_addUserPaymentMethod( $id );
        $this->_addUserProfit( $id );
    }

    private function _addUserIndex( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'users/',
            'name'        => '散户管理',
            'extra'       => json_encode(['icon' => 'users','component'=>'SubPage','redirect'=>'/users/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $id,
                'rule'        => 'users/index',
                'name'        => '散户列表',
                'extra'       => json_encode(['hidden' => true,'component'=>'users/index']),
            ],
            [
                'parent_id'   => $id,
                'rule'        => 'users/create',
                'name'        => '增加散户',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $id,
                'rule'        => 'users/delete',
                'name'        => '删除散户',
                'extra'       => json_encode(['hidden' => true]),
            ],
        ]);
    }

    private function _addUserOrders( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => $parent_id,
            'rule' => 'user_orders/',
            'name' => '散户账变',
            'extra' => json_encode(['icon' => 'users', 'component' => 'orders/user']),
        ]);
    }

    private function _addUserDeposit( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => $parent_id,
            'rule' => 'user_deposit/',
            'name' => '散户代收订单',
            'extra' => json_encode(['icon' => 'users', 'component' => 'deposit/user']),
        ]);
    }

    private function _addUserWithdrawal( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => $parent_id,
            'rule' => 'user_withdrawal/',
            'name' => '散户代付订单',
            'extra' => json_encode(['icon' => 'users', 'component' => 'users/index']),
        ]);
    }
    private function _addUserSelfDeposit( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => $parent_id,
            'rule' => 'user_self_deposit/',
            'name' => '散户充值审核',
            'extra' => json_encode(['icon' => 'users', 'component' => 'users/index']),
        ]);
    }

    private function _addUserSelfWithdrawal( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => $parent_id,
            'rule' => 'user_self_withdrawal/',
            'name' => '散户提款审核',
            'extra' => json_encode(['icon' => 'users', 'component' => 'users/index']),
        ]);
    }

    private function _addUserPaymentMethod( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => $parent_id,
            'rule' => 'user_payment_method/',
            'name' => '散户收款方式',
            'extra'       => json_encode(['icon' => 'users','component'=>'SubPage']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $id,
                'rule'        => 'users/user_banks',
                'name'        => '散户收款银行卡',
                'extra'       => json_encode(['component'=>'users/user_banks']),
            ],
            [
                'parent_id'   => $id,
                'rule'        => 'users/wechat',
                'name'        => '散户收款微信',
                'extra'       => json_encode(['component'=>'users/user_banks']),
            ],
            [
                'parent_id'   => $id,
                'rule'        => 'users/alipay',
                'name'        => '散户收款支付宝',
                'extra'       => json_encode(['component'=>'users/user_banks']),
            ],
        ]);
    }

    private function _addUserProfit( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => $parent_id,
            'rule' => 'user_profit/',
            'name' => '散户收益统计',
            'extra' => json_encode(['icon' => 'users', 'component' => 'users/index']),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
