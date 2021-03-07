<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('users')->insert([
            [
                'agent_id'      => 1,
                'username'      => 'nick188',
                'nickname'      => '测试',
                'password'          => bcrypt('123456'),
                'security_password' => bcrypt('123456'),
                'status'        => true,
                'extra'         => json_encode([
                    'rebates'   => [
                        "user_deposit_rebate" => [
                            "rate" => 1.0,
                            "status" => true
                        ],
                        "user_withdrawal_rebate"=> [
                            "amount"=> 1.6,
                            "status"=> true
                        ]
                    ]
                ]),
            ],
        ]);

        DB::table('funds')->insert([
            [
                'type'          => 3,
                'third_id'      => 1,
                'balance'       => 10000,
            ],
        ]);

        DB::table('user_banks')->insert([
            [
                'user_id'    => 1,
                'account_name'  => '张三',
                'account_number'=> '646543219876543216',
                'banks_id'      => 1,  //$bank_id
                'province_id'   => 2,
                'city_id'       => 36,
                'district_id'   => 398,
                'branch'        => '朝阳支行',
                'status'        => 1,
                'is_delete'     => 1,
                'is_open'       => 1,
                'limit_amount'  => 10000
            ],
        ]);
    }
}
