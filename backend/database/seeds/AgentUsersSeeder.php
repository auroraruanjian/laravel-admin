<?php

use Illuminate\Database\Seeder;

class AgentUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rebates = [
            "rebates"=> [
                "deposit_rebates" => [
                    "1"=>[
                        "rate"=> 2.2,
                        "status"=> true,
                        "payment_method_id"=> 1
                    ],
                    "2"=> [
                        "rate"=> 3.9,
                        "status"=> true,
                        "payment_method_id"=> 2
                    ],
                    "3"=> [
                        "rate"=> 4.2,
                        "status"=> true,
                        "payment_method_id"=> 3
                    ]
                ],
                "withdrawal_rebate" => [
                    "amount" => 5.8,
                    "status" => true
                ],
                "user_deposit_rebate" => [
                    "rate" => 1.2,
                    "status" => true
                ],
                "user_withdrawal_rebate"=> [
                    "amount"=> 1.8,
                    "status"=> true
                ]
            ]
        ];

        //
        //factory(\Common\Models\AgentUsers::class, 5)->create();
        DB::table('agent_users')->insert([
            'username' => '童丽',
            'nickname' => '童丽',
            'password' => bcrypt('123456'),
            'extra' => json_encode($rebates)
        ]);

        DB::table('funds')->insert([
            [
                'type'          => 1,
                'third_id'      => 1,
            ],
        ]);
    }
}
