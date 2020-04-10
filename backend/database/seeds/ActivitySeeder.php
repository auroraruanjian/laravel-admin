<?php

use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 生成100期奖期
        $insertData = array();
        for ($i=0;$i<50;$i++) {
            $day = ($i - 45) * 7;

            $extra = [
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
                ],
            ];
            if( $i < 45 ){
                $extra['code'] = str_pad(mt_rand(0,999),3,STR_PAD_LEFT);
            }

            $insertData[] = [
                'activity_id'   => 1,
                'start_at'      => (string)\Carbon\Carbon::parse($day .' days')->startOfWeek(),
                'end_at'        => (string)\Carbon\Carbon::parse($day .' days')->endOfWeek(),
                'extra'         => json_encode($extra),
                'created_at'    => (string)\Carbon\Carbon::now(),
            ];
        }
        DB::table('activity_issue')->insert($insertData);

        // 生成120条记录
        factory(\Common\Models\ActivityRecord::class, 120)->create();
    }
}
