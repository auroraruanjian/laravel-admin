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

        DB::table('merchants')->insert([
            [
                'account'               => 'zf10000000001',
                'nickname'              => '测试',
                'system_public_key'     => $system['public'],
                'system_private_key'    => $system['private'],
                'merchant_public_key'   => $merchant['public'],
                'merchant_private_key'  => $merchant['private'],
                'md5_key'               => '3c6e0b8a9c15224a8228b9a98ca1531d',
                'status'                => 0,
            ],
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

        DB::table('merchant_fund')->insert([
            [
                'merchant_id'           => 1,
            ],
        ]);
    }
}
