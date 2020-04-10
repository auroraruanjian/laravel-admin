<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ident', 64)->unique()->comment('唯一英文标识');
            $table->string('title',255)->comment('活动标题');
            $table->string('describe',255)->comment('活动简述');
            $table->text('content')->comment('活动详情');
            $table->tinyInteger('status')->unsigned()->default(0)->comment('状态:0 正常，1 禁用');
            //$table->jsonb('extra')->comment('活动扩展数据，奖品，奖券总量，周期号码分配规则，奖级名额');// 号码规则，前两位随机，后三位随机
            $table->timestamps();
        });

        $this->_permission();
        $this->_data();
    }

    private function _permission()
    {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => 0,
            'rule'        => 'activityManage',
            'name'        => '活动系统',
            'extra'       => json_encode(['icon' => 'star','component'=>'Layout']),
        ]);

        $activity_id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id'   => $id,
            'rule'        => 'activity/',
            'name'        => '活动管理',
            'extra'       => json_encode(['icon' => 'star','component'=>'SubPage','redirect'=>'/activity/index']),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $id,
                'rule'        => 'activity/index',
                'name'        => '活动列表',
                'extra'       => json_encode(['hidden' => true,'component'=>'activity/index']),
            ],
            [
                'parent_id'   => $activity_id,
                'rule'        => 'activity/create',
                'name'        => '增加活动',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $activity_id,
                'rule'        => 'activity/edit',
                'name'        => '编辑活动',
                'extra'       => json_encode(['hidden' => true,'component'=>'activity/edit','params'=>['id']]),
            ],
            [
                'parent_id'   => $activity_id,
                'rule'        => 'activity/delete',
                'name'        => '删除活动',
                'extra'       => json_encode(['hidden' => true]),
            ],
        ]);
    }

    /**
     * 数据
     */
    private function _data()
    {
        DB::table('activity')->insert([
            'ident'     => 'raffle_tickets',
            'title'     => '抽奖活动',
            'describe'  => '抽奖活动描述',
            'content'   => '抽奖活动内容',
            'status'    => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity');
    }
}
