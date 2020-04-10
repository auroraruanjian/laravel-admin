<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableActivityIssue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_issue', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('activity_id')->comment('活动ID');
            $table->timestamp('start_at')->comment('活动开始时间');
            $table->timestamp('end_at')->comment('活动结束时间');
            $table->jsonb('extra')->default('{}')->comment('扩展字段，存开奖号码等');
            $table->timestamps();
        });

        $this->_permission();
        //$this->_data();
    }

    private function _permission()
    {
        $row = DB::table('admin_role_permissions')->where('name', '活动管理')->first();
        if( empty($row) ){
            return false;
        }

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $row->id,
                'rule'        => 'activityIssue/index',
                'name'        => '奖期列表',
                'extra'       => json_encode(['hidden' => true,'component'=>'activity_issue/index','params'=>['id']]),
            ],
            [
                'parent_id'   => $row->id,
                'rule'        => 'activityIssue/create',
                'name'        => '增加奖期',
                'extra'       => json_encode(['hidden' => true,'component'=>'activity_issue/create']),
            ],
            [
                'parent_id'   => $row->id,
                'rule'        => 'activityIssue/edit',
                'name'        => '编辑奖期',
                'extra'       => json_encode(['hidden' => true,'component'=>'activity_issue/edit','params'=>['id']]),
            ],
            [
                'parent_id'   => $row->id,
                'rule'        => 'activityIssue/delete',
                'name'        => '删除奖期',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $row->id,
                'rule'        => 'activityIssue/open',
                'name'        => '开奖',
                'extra'       => json_encode(['hidden' => true]),
            ],

            [
                'parent_id'   => $row->id,
                'rule'        => 'upload/index',
                'name'        => '上传图片',
                'extra'       => json_encode(['hidden' => true]),
            ],
            [
                'parent_id'   => $row->id,
                'rule'        => 'upload/delete',
                'name'        => '删除图片',
                'extra'       => json_encode(['hidden' => true]),
            ],
        ]);
    }

    /**
     * 数据
     */
    private function _data()
    {
        DB::table('activity_issue')->insert([
            'activity_id'   => 1,
            'start_at'      => (string)\Carbon\Carbon::now()->startOfWeek(),
            'end_at'        => (string)\Carbon\Carbon::now()->endOfWeek(),
            'extra'     => json_encode([
                // 奖品，奖级,名额
                'prize_level'  => [
                    [
                        'name'          => '一等奖',
                        'prize_name'    => 'iphone',
                        'prize_img'     => '',
                    ],
                    [
                        'name'          => '二等奖',
                        'prize_name'    => 'ipad',
                        'prize_img'     => '',
                    ],
                    [
                        'name'          => '三等奖',
                        'prize_name'    => '精美礼品',
                        'prize_img'     => '',
                    ]
                ],
                // 奖券总量
                'tickets_total' => 100,
                // 周期号码分配规则 天 => 前两位号码范围
                'tickets_rule'  => [
                    [
                        'range' => ['00','20'],
                    ],
                    [
                        'range' => ['21','40'],
                    ],
                    [
                        'range' => ['41','50'],
                    ],
                    [
                        'range' => ['51','60'],
                    ],
                    [
                        'range' => ['61','70'],
                    ],
                    [
                        'range' => ['71','90'],
                    ],
                    [
                        'range' => ['91','99'],
                    ],
                ]
            ]),
            'created_at'    => (string)\Carbon\Carbon::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_issue');
    }
}
