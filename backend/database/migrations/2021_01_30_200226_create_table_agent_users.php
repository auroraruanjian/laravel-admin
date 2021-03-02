<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAgentUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * 代理表
         */
        Schema::create('agent_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('top_id')->default(0)->comment('总代用户 ID，总代为 0');
            $table->integer('parent_id')->default(0)->comment('父级用户 ID，总代为 0');
            $table->jsonb('parent_tree')->default('[]')->comment('父级树');
            $table->string('username',32)->unique()->comment('代理名');
            $table->string('nickname',20)->comment('昵称');
            $table->string('password')->comment('密码');
            $table->tinyInteger('status')->default(0)->comment('状态:0 正常，1 冻结');
            $table->ipAddress('last_ip')->nullable()->comment('最后一次登录IP');
            $table->timestamp('last_time')->nullable()->comment('最后登录时间');
            $table->string('last_session', 64)->default('')->comment('最近登陆SESSIONID');
            $table->string('google_key', 16)->default('')->comment('谷歌登录器秘钥');
            $table->string('unionid')->nullable()->comment('微信登陆唯一ID');
            $table->jsonb('extra')->default(json_encode([]))->comment('扩展数据，存放返点{rebates,}');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            //$table->unique(['merchant_id','username']);
            $table->index('username');
        });

        $this->_permission();
    }

    private function _permission()
    {

        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => 0,
            'rule'        => 'agent_system',
            'name'        => '代理系统',
            'extra'       => json_encode(['icon' => 'list','component'=>'Layout']),
        ]);

        $this->_addAgentIndex( $id );
        $this->_addAgentOrders( $id );
        $this->_addAgentWithdrawalVerify( $id );
        $this->_addAgentProfit( $id );
    }

    private function _addAgentIndex( $parent_id )
    {
        $agent_id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'agent_users/',
            'name'        => '代理管理',
            'extra'       => json_encode(['icon' => 'users','component'=>'SubPage','redirect'=>'/agent_users/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $agent_id,
                'rule'        => 'agent_users/index',
                'name'        => '代理列表',
                'extra'       => json_encode(['hidden' => true,'component'=>'agent/users']),
            ],
            [
                'parent_id'   => $agent_id,
                'rule'        => 'agent_users/create',
                'name'        => '增加代理',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $agent_id,
                'rule'        => 'agent_users/delete',
                'name'        => '删除代理',
                'extra'       => json_encode(['hidden' => true]),
            ],
        ]);
    }

    private function _addAgentWithdrawalVerify( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'agent_withdrawal/',
            'name'        => '代理提现审核',
            'extra'       => json_encode(['icon' => 'users','component'=>'SubPage','redirect'=>'/agent_withdrawal/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $id,
                'rule'        => 'agent_withdrawal/index',
                'name'        => '代理提现列表',
                'extra'       => json_encode(['hidden' => true,'component'=>'agent/users']),
            ],
        ]);
    }

    private function _addAgentOrders( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'agent_orders/',
            'name'        => '代理账变',
            'extra'       => json_encode(['icon' => 'users','component'=>'SubPage','redirect'=>'/agent_orders/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $id,
                'rule'        => 'agent_orders/index',
                'name'        => '代理账变列表',
                'extra'       => json_encode(['hidden' => true,'component'=>'orders/agent']),
            ],
        ]);
    }

    private function _addAgentProfit( $parent_id )
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $parent_id,
            'rule'        => 'agent_profit/',
            'name'        => '代理收益统计',
            'extra'       => json_encode(['icon' => 'users','component'=>'SubPage','redirect'=>'/agent_profit/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $id,
                'rule'        => 'agent_profit/index',
                'name'        => '代理收益统计报表',
                'extra'       => json_encode(['hidden' => true,'component'=>'agent/users']),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_users');
    }
}
