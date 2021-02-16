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

    public function getInfo( Request $request )
    {
        $user = auth()->user();

        $permission = [
            [
                "rule"          => "payment_method",
                "name"          => "收款方式",
                "extra"         => [
                    "icon"      => "users",
                    "redirect"  => "/payment_method/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "payment_method/index",
                        "name"=> "收款方式",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "payment_method/index",
                            'hidden'    => true,
                        ],
                    ],
                    [
                        "rule"=> "deposit/index",
                        "name"=> "充值申请",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "deposit/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            /*
            [
                "rule"          => "payment_method",
                "name"          => "Q币购入",
                "extra"         => [
                    "icon"      => "users",
                    "redirect"  => "/payment_method/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "payment_method/index",
                        "name"=> "Q币购入",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "payment_method/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "payment_method",
                "name"          => "Q币售出",
                "extra"         => [
                    "icon"      => "users",
                    "redirect"  => "/payment_method/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "payment_method/index",
                        "name"=> "Q币售出",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "payment_method/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "report",
                "name"          => "记录中心",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "report/deposit",
                        "name"=> "保证金充值记录",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "report/profit"
                        ],
                    ],
                    [
                        "rule"=> "report/widthdrawal",
                        "name"=> "提现记录",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "report/profit"
                        ],
                    ],
                    [
                        "rule"=> "report/widthdrawal",
                        "name"=> "账变记录",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "report/profit"
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "agent",
                "name"          => "代理中心",
                "extra"         => [
                    "icon"      => "users",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "agent/deposit",
                        "name"=> "团队成员管理",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "report/profit"
                        ],
                    ],
                    [
                        "rule"=> "report/widthdrawal",
                        "name"=> "公告中心",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "report/profit"
                        ],
                    ],
                ]
            ],
            */
        ];
        return [
            'code'      => 1,
            'data'      => [
                'id'        => $user->id,
                'username'  => $user->username,
                'nickname'  => $user->nickname,
                'last_time' => $user->last_time,
                'last_ip'   => $user->last_ip,
                'permission'=> $permission,
                'wechat_status' => !empty($user->unionid)?true:false,
            ]
        ];
    }
}
