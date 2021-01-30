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
            /*
            [
                "rule"          => "permission",
                "name"          => "后台权限管理",
                "extra"         => [
                    "icon"      => "lock",
                    "component" => "Layout"
                ],
                "description"   => "",
            ],
            */
            [
                "rule"          => "merchant",
                "name"          => "代收商户管理",
                "extra"         => [
                    "icon"      => "users",
                    "redirect"  => "/merchant/index",
                    "component" => "Layout",
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "merchant/index",
                        "name"=> "代收商户列表",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "merchant/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "child_agent",
                "name"          => "子代理管理",
                "extra"         => [
                    "icon"      => "users",
                    "redirect"  => "/child_agent/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "child_agent/index",
                        "name"=> "子代理列表",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "child_agent/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "report",
                "name"          => "统计管理",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "report/profit",
                        "name"=> "收益统计",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "report/profit"
                        ],
                    ],
                    [
                        "rule"=> "report/merchantRecord",
                        "name"=> "商户流水统计",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "report/merchantRecord"
                        ],
                    ]
                ]
            ],
            [
                "rule"          => "withdrawal",
                "name"          => "提现管理",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "withdrawal/index",
                        "name"=> "提现",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "withdrawal/index"
                        ],
                    ],
                    [
                        "rule"=> "withdrawal/record",
                        "name"=> "提现记录",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "withdrawal/record"
                        ],
                    ]
                ]
            ],
            [
                "rule"          => "funds",
                "name"          => "账变记录",
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
