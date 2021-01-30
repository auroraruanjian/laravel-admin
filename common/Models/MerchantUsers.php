<?php
namespace Common\Models;

use Common\Models\AdminRole;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MerchantUsers extends Authenticatable
{
    use Notifiable;

    protected $table = 'merchant_users';

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
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //'email_verified_at' => 'datetime',
    ];

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        dd($credentials);
    }

    /*
    // 用户角色
    public function roles()
    {
        return $this->belongsToMany(
            AdminRoles::class,
            'admin_user_has_roles',
            'user_id',
            'role_id'
        );
    }

    // 判断用户是否具有某个角色
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return !!$role->intersect($this->roles)->count();
    }

    // 判断用户是否具有某权限
    public function hasPermission($permission)
    {
        return $this->hasRole($permission->roles);
    }

    // 给用户分配角色
    public function assignRole($role)
    {
        return $this->roles()->save($role);
    }


    // 角色整体添加与修改
    public function giveRoleTo(array $role_id)
    {
        $this->roles()->detach();
        $roles = AdminRoles::whereIn('id', $role_id)->get();
        foreach ($roles as $v) {
            $this->assignRole($v);
        }
        return true;
    }
    */
}
