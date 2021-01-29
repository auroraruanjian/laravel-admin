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
        factory(\Common\Models\AgentUsers::class, 5)->create();
    }
}
