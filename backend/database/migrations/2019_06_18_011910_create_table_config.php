<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->smallIncrements('id')->comment('配置 ID');
            $table->SmallInteger('parent_id')->default(0)->comment('配置父 ID');
            $table->string('title', 64)->comment('配置标题');
            $table->string('key', 64)->unique()->comment('系统配置名称');
            $table->string('value', 256)->nullable()->comment('系统配置值');
            $table->smallInteger('type')->default(1)->comment('配置类型：1.输入框 2.下拉框 3.开关 4.单选框 5.多选框');
            $table->jsonb('extra')->default('{}')->comment('值列表，1.开关:0-关闭 1-开启 ,2.下拉框、单选、多选:["data":[{key:"选项",value:"值"}]] 3.输入框:{"encrypt":true}');
            $table->boolean('is_disabled')->default(0)->comment('配置项是否禁用 0:禁用 1:启用');
            $table->string('description', 128)->nullable()->comment('配置描述');
            $table->timestamps();
        });

        $this->_permission();
        $this->_data();
    }

    private function _permission()
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => 0,
            'rule'        => 'config',
            'name'        => '网站管理',
            'extra'       => json_encode(['icon' => 'config','component'=>'Layout']),
        ]);

        $config_id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $id,
            'rule'        => 'config/index',
            'name'        => '配置管理',
            'extra'       => json_encode(['icon' => 'setting','component'=>'config/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $config_id,
                'rule'        => 'config/create',
                'name'        => '添加配置',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $config_id,
                'rule'        => 'config/setting',
                'name'        => '设置配置',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $config_id,
                'rule'        => 'config/edit',
                'name'        => '修改配置',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $config_id,
                'rule'        => 'config/delete',
                'name'        => '删除配置',
                'extra'       => json_encode(['hidden' => true]),
            ],
        ]);
    }

    private function _data()
    {
        $id = DB::table('config')->insertGetId([
            'parent_id'     => 0,
            'title'         => '网站管理',
            'key'           => 'website',
            'value'         => '',
            'is_disabled'   => 1,
            'description'   => '',
        ]);
        DB::table('config')->insert([
            [
                'parent_id'     => $id,
                'title'         => '网站模式',
                'key'           => 'website_status',
                'value'         => '1',
                'type'          => '2',
                'extra'         => json_encode(['data'=>[['key'=>'开启','value'=>'1'],['key'=>'关闭','value'=>'0'],['key'=>'维护模式','value'=>'2']]]),
                'is_disabled'   => 1,
                'description'   => '',
            ]
        ]);

        $deposit_id = DB::table('config')->insertGetId([
            'parent_id'     => 0,
            'title'         => '代收配置',
            'key'           => 'deposit',
            'value'         => '',
            'is_disabled'   => 1,
            'description'   => '',
        ]);
        DB::table('config')->insert([
            [
                'parent_id'     => $deposit_id,
                'title'         => '散户订单模式',
                'key'           => 'person_order_model',
                'value'         => '1',
                'type'          => '2',
                'extra'         => json_encode(['data'=>[['key'=>'抢单','value'=>'0'],['key'=>'自动分配','value'=>'1']]]),
                'is_disabled'   => 1,
                'description'   => '0:抢单 1:自动分配',
            ],
        ]);

        $rebates_id = DB::table('config')->insertGetId([
            'parent_id'     => 0,
            'title'         => '手续费配置',
            'key'           => 'rebates',
            'value'         => '',
            'is_disabled'   => 1,
            'description'   => '',
        ]);
        DB::table('config')->insert([
            [
                'parent_id'     => $rebates_id,
                'title'         => '平台代收最低保留费率',
                'key'           => 'rebates_deposit_platform_min_rate',
                'value'         => '0',
                'type'          => '1',
                'extra'         => '{}',
                'is_disabled'   => 1,
                'description'   => '表示可以开给代理的最低费率(%)=第三方通道的费率(%)+平台最低保留费率(%)',
            ],
            [
                'parent_id'     => $rebates_id,
                'title'         => '平台代付最低手续费',
                'key'           => 'rebates_withdrawal_rebate',
                'value'         => '2',
                'type'          => '1',
                'extra'         => '{}',
                'is_disabled'   => 1,
                'description'   => '开给代理的商户代付手续费，元为单位，递增',
            ],
            [
                'parent_id'     => $rebates_id,
                'title'         => '平台散户代收佣金最高费率',
                'key'           => 'rebates_user_deposit_rebate',
                'value'         => '1.3',
                'type'          => '1',
                'extra'         => '{}',
                'is_disabled'   => 1,
                'description'   => '百分比，递减',
            ],
            [
                'parent_id'     => $rebates_id,
                'title'         => '平台散户代付佣金最高费率',
                'key'           => 'rebates_user_withdrawal_rebate',
                'value'         => '2',
                'type'          => '1',
                'extra'         => '{}',
                'is_disabled'   => 1,
                'description'   => '单位为元，递减',
            ],
        ]);
        /*
        $wechat_id = DB::table('config')->insertGetId([
            'parent_id'     => 0,
            'title'         => '微信配置',
            'key'           => 'wechat',
            'value'         => '',
            'is_disabled'   => 1,
            'description'   => '',
        ]);
        DB::table('config')->insert([
            [
                'parent_id'     => $wechat_id,
                'title'         => '微信登陆是否开启',
                'key'           => 'wechat_enable',
                'value'         => '1',
                'type'          => '3',
                'extra'         => '{}',
                'is_disabled'   => 1,
                'description'   => '0:关闭 1:开启',
            ],
            [
                'parent_id'     => $wechat_id,
                'title'         => '微信appid',
                'key'           => 'wechat_appid',
                'value'         => 'wx21dc2d3e2297df05',
                'type'          => '1',
                'extra'         => '{}',
                'is_disabled'   => 1,
                'description'   => '微信公众后台appID',
            ],
            [
                'parent_id'     => $wechat_id,
                'title'         => '微信secret',
                'key'           => 'wechat_secret',
                'value'         => '37c86b59f753fd768c47499e1c0a0cb5',
                'type'          => '1',
                'extra'         => json_encode(["encrypt"=>true]),
                'is_disabled'   => 1,
                'description'   => '微信公众后台appsecret',
            ],
            [
                'parent_id'     => $wechat_id,
                'title'         => '微信回调地址',
                'key'           => 'wechat_callback_url',
                'value'         => 'http://53d83880.ngrok.io/login/wechatCallback',
                'type'          => '1',
                'extra'         => '{}',
                'is_disabled'   => 1,
                'description'   => '微信公众后台appsecret',
            ],
            //
        ]);
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config');
    }
}
