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
        //
        //factory(\Common\Models\AgentUsers::class, 5)->create();
        DB::table('agent_users')->insert([
            'username' => '童丽',
            'nickname' => '童丽',
            'password' => bcrypt('123456'),
            'extra' => json_encode([
                'rebates'   => [
                    'deposit_rebates'       => [],
                    'withdrawal_rebate'     => [],
                    'user_deposit_rebate'   => [],
                    'user_withdrawal_rebate'=> [],
                ]
            ])
        ]);

        DB::table('funds')->insert([
            [
                'type'          => 1,
                'third_id'      => 1,
            ],
        ]);
    }
}
