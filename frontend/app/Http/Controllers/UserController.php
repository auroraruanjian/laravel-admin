<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Common\Models\ActivityIssue;
use Common\Models\ActivityRecord;
use Illuminate\Http\Request;

class UserController extends Controller
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

    public function getInfo( Request $request )
    {
        $user = auth()->user();

        $now = (string)Carbon::now();

        // 获取已用抽奖次数
        $activity_issue = ActivityIssue::select([
            'start_at',
            'end_at',
        ])
            ->where([
                ['activity_id','=',1],
                ['start_at','<=',$now],
                ['end_at','>=',$now],
            ])
            ->first();

        $draw_count = 0;
        if( !empty($activity_issue) ){
            $draw_count = ActivityRecord::where([
                    ['user_id','=',auth()->id()],
                    ['created_at','>=',$activity_issue->start_at],
                    ['created_at','<=',$activity_issue->end_at]
                ])
                ->count();
        }


        return [
            'code'      => 1,
            'data'      => [
                'id'        => $user->id,
                'username'  => $user->username,
                'nickname'  => $user->nickname,
                'last_time' => $user->last_time,
                'last_ip'   => $user->last_ip,
                'draw_time' => $user->draw_time,
                'draw_count'=> $draw_count,
            ]
        ];
    }
}
