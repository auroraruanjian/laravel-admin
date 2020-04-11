<?php

namespace Common\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; //这里修改

class Users extends Authenticatable
{
    //
    protected $table='users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'nickname', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
