<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use Illuminate\Support\Str;
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
        'user_group_id' => 1,
        'username'      => $faker->unique()->userName,
        'nickname'      => $faker->name,
        'password'      => bcrypt('a123456'),
    ];
});
