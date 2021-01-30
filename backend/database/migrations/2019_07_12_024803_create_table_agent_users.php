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
            $table->string('username',32)->comment('代理名');
            $table->string('nickname',20)->unique()->comment('昵称');
            $table->string('password')->comment('密码');
            $table->tinyInteger('status')->default(0)->comment('状态:0 正常，1 冻结');
            $table->ipAddress('last_ip')->nullable()->comment('最后一次登录IP');
            $table->timestamp('last_time')->nullable()->comment('最后登录时间');
            $table->string('last_session', 64)->default('')->comment('最近登陆SESSIONID');
            $table->string('google_key', 16)->default('')->comment('谷歌登录器秘钥');
            $table->string('unionid')->nullable()->comment('微信登陆唯一ID');
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
        $row = DB::table('admin_role_permissions')->where('name', '会员管理')->where('parent_id', 0)->first();

        if (empty($row)) {
            return;
        }

        $users_id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $row->id,
            'rule'        => 'agent_users/',
            'name'        => '代理管理',
            'extra'       => json_encode(['icon' => 'users','component'=>'SubPage','redirect'=>'/agent_users/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $users_id,
                'rule'        => 'agent_users/index',
                'name'        => '代理列表',
                'extra'       => json_encode(['hidden' => true,'component'=>'agent/users']),
            ],
            [
                'parent_id'   => $users_id,
                'rule'        => 'agent_users/create',
                'name'        => '增加代理',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $users_id,
                'rule'        => 'agent_users/delete',
                'name'        => '删除代理',
                'extra'       => json_encode(['hidden' => true]),
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
