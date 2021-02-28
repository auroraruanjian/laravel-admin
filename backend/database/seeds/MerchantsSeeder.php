<?php

use Illuminate\Database\Seeder;

class MerchantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $system = Common\Helpers\RSA::new();
        $merchant = Common\Helpers\RSA::new();

        $extra = [
            "rebates" => [
                "deposit_rebates" => [
                    "1" => [
                        "rate" => 6,
                        "status" => true,
                        "payment_method_id" => 1
                    ],
                    "2" => [
                        "rate" => 6.4,
                        "status" => true,
                        "payment_method_id" => 2
                    ],
                    "3" => [
                        "rate" => 6,
                        "status" => true,
                        "payment_method_id" => 3
                    ]
                ],
                "withdrawal_rebate"=>[
                    "amount" =>  7.5,
                    "status" => true
                ]
            ]
        ];

        DB::table('merchants')->insert([
            [
                'agent_id'              => 1,
                'account'               => 'zf10000000001',
                'nickname'              => '测试',
                'system_public_key'     => $system['public'],
                'system_private_key'    => $system['private'],
                'merchant_public_key'   => $merchant['public'],
                'merchant_private_key'  => $merchant['private'],
                'md5_key'               => '3c6e0b8a9c15224a8228b9a98ca1531d',
                'status'                => 0,
                'extra'                 => json_encode($extra),
            ]
        ]);

        DB::table('merchant_users')->insert([
            'merchant_id'   => 1,
            'username'      => 'admin',
            'nickname'      => '管理员',
            'phone'         => '13125412563',
            'password'      => bcrypt('123456'),
            'pay_password'  => bcrypt('123456'),
            'status'        => 1,
        ]);

        DB::table('funds')->insert([
            [
                'type'          => 2,
                'third_id'      => 1,
            ],
        ]);
    }
}
