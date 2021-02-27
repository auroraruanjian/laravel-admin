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
                'username'      => 'nick188',
                'nickname'      => '测试',
                'password'      => bcrypt('123456'),
                'status'        => true,
                'extra'         => json_encode([
                    'rebates'   => [
                        'user_deposit_rebate'   => [],
                        'user_withdrawal_rebate'=> [],
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

        DB::table('user_payment_methods')->insert([
            [
                'type'       => 1,
                'user_id'    => 1,
                'extra'      => json_encode([
                    'account_name'      => '张三',
                    'account_number'    => '6465432198765432165',
                    'banks_id'          => 1,  //$bank_id
                    'branch'            => '北京',
                    'province'          => '北京'
                ]),
                'status'        => 1,
                'is_delete'     => 1,
                'is_open'       => 1,
                'limit_amount'  => 10000
            ],
        ]);
    }
}
