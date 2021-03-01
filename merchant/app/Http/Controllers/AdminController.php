<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\CommonIndexRequest;
use Common\Models\AdminRolePermissions;
use Common\Models\AdminUser;
use Common\Models\AdminUserHasRole;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function putUnbindWechat()
    {
        $user = auth()->user();
        $user->unionid = '';

        if( $user->save() ){
            return $this->response(1,'解绑成功！');
        }else{
            return $this->response(0,'解绑失败！');
        }
    }

    public function getInfo( Request $request )
    {
        $user = auth()->user();

        $permission = [
            [
                "rule"          => "orders1",
                "name"          => "订单管理",
                "extra"         => [
                    "icon"      => "users",
                    "redirect"  => "/orders/index1",
                    "component" => "Layout",
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "deposit/index",
                        "name"=> "代收记录",
                        "extra"=> [
                            "icon"      => "permission",
                            "component" => "deposit/index"
                        ],
                    ],
                    [
                        "rule"=> "withdrawal/index",
                        "name"=> "代付记录",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "withdrawal/index",
                        ],
                    ],
                    [
                        "rule"=> "orders/index",
                        "name"=> "账变记录",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "orders/index",
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "merchant_deposit",
                "name"          => "充值管理",
                "extra"         => [
                    "icon"      => "users",
                    "redirect"  => "/merchant_deposit/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "merchant_deposit/index",
                        "name"=> "充值",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "deposit/index",
                        ],
                    ],
                    [
                        "rule"=> "merchant_deposit/record",
                        "name"=> "充值记录",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "deposit/index",
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "merchant_withdrawal",
                "name"          => "提现管理",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "merchant_withdrawal/index",
                        "name"=> "提现",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "deposit/index"
                        ],
                    ],
                    [
                        "rule"=> "merchant_withdrawal/merchantRecord",
                        "name"=> "提现记录",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "deposit/index"
                        ],
                    ]
                ]
            ],
            [
                "rule"          => "report",
                "name"          => "收款统计",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
            ],
            [
                "rule"          => "notices",
                "name"          => "公告列表",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
            ],
            [
                "rule"          => "systemConfig",
                "name"          => "系统设置",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
            ]
        ];
        return [
            'code'      => 1,
            'data'      => [
                'id'        => $user->id,
                'username'  => $user->username,
                'nickname'  => $user->nickname,
                'last_time' => $user->last_time,
                'last_ip'   => $user->last_ip,
                'role_name' => $user->id==1?'超级管理员':$user->roles()->name??'',
                'permission'=> $permission,
                'wechat_status' => !empty($user->unionid)?true:false,
            ]
        ];
    }
}
