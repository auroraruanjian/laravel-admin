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
                "rule"          => "user_banks",
                "name"          => "收款方式",
                "extra"         => [
                    "icon"      => "list",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "user_banks/index",
                        "name"=> "收款银行卡",
                        "extra"=> [
                            "icon"      => "bank_card",
                            "component" => "user_banks/index",
                        ],
                    ],
                    [
                        "rule"=> "user_banks/wechat",
                        "name"=> "收款微信",
                        "extra"=> [
                            "icon"      => "wechat",
                            "component" => "user_banks/index",
                        ],
                    ],
                    [
                        "rule"=> "user_banks/alipay",
                        "name"=> "收款支付宝",
                        "extra"=> [
                            "icon"      => "alipay",
                            "component" => "user_banks/index",
                        ],
                    ],
                ]
            ],

            [
                "rule"          => "deposit",
                "name"          => "Q币售出",
                "extra"         => [
                    "icon"      => "orders",
                    "redirect"  => "/deposit/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "deposit/index",
                        "name"=> "Q币售出",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "deposit/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "orders",
                "name"          => "Q币买卖",
                "extra"         => [
                    "icon"      => "deposit",
                    "redirect"  => "/orders/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "orders/index",
                        "name"=> "Q币买卖",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "orders/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "withdrawal",
                "name"          => "Q币买入",
                "extra"         => [
                    "icon"      => "withdrawal",
                    "redirect"  => "/withdrawal/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "withdrawal/index",
                        "name"=> "Q币买入",
                        "extra"=> [
                            "icon"      => "list",
                            "component" => "withdrawal/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "user_deposit",
                "name"          => "保证金充值",
                "extra"         => [
                    "icon"      => "user_deposit",
                    "redirect"  => "/user_deposit/index",
                    "component" => "Layout"
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "user_deposit/index",
                        "name"=> "保证金充值",
                        "extra"=> [
                            "icon"      => "user_deposit",
                            "component" => "user_deposit/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ],
            [
                "rule"          => "user_withdrawal",
                "name"          => "收益提取",
                "extra"         => [
                    "icon"      => "user_withdrawal",
                    "component" => "Layout",
                    "redirect"  => "/user_withdrawal/index",
                ],
                "description"   => "",
                "child"         => [
                    [
                        "rule"=> "user_withdrawal/index",
                        "name"=> "收益提取",
                        "extra"=> [
                            "icon"=> "permission",
                            "component"=> "user_withdrawal/index",
                            'hidden'    => true,
                        ],
                    ],
                ]
            ]
            /*
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
                'id'        => auth()->id(),
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
