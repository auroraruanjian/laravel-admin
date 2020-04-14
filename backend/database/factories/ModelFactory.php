<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\Common\Models\Users::class, function (Faker $faker) {
    return [
        'username' => $faker->unique()->userName,
        'nickname' => $faker->name,
        'password' => bcrypt(env('IMPORT_DEFAULT_PASSWORD')),
        'draw_time'=> mt_rand(1,5),
    ];
});

$factory->define(\Common\Models\ActivityRecord::class, function (Faker $faker) {
    $extra = [
        'code' => str_pad(mt_rand(0,99999),5,'0'.STR_PAD_LEFT),
    ];

    $status = mt_rand(-1,1);
    if( $status >= 0 ){
        $extra['draw_level'] = mt_rand(0,2);
    }

    return [
        'user_id'           => mt_rand(0,100),
        'activity_id'       => 1,
        'activity_issue_id' => mt_rand(0,50),
        'status'            => $status==-1?null:$status,
        'extra'             => json_encode($extra),
    ];
});
