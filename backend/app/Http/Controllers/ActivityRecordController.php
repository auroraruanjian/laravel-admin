<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Common\Models\Activity;
use Common\Models\ActivityIssue;
use Common\Models\ActivityRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityRecordController extends Controller
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

    public function getInit()
    {
        $activity = Activity::select([
            'title',
            'ident'
        ])
            ->get();

        $activity_issue = ActivityIssue::select([
            'id',
            DB::raw("activity_issue.extra->'prize_level' as prize_level")
        ])
            ->where('activity_id',1)
            ->orderBy('id','desc')
            ->limit(1000)
            ->get();

        foreach($activity_issue as $key => $issue){
            $activity_issue[$key]['prize_level'] = json_decode($issue['prize_level'],true);
        }

        $data = [
            'activity'  => $activity,
            'issue'     => $activity_issue,
        ];

        return $this->response(1,'success',$data);
    }

    public function postIndex(Request $request)
    {
        $ident = $request->get('ident');
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $data = [
            'total'             => 0,
            'activity_record'    => [],
        ];

        $where = [
            ['activity.ident','=',$ident],
        ];
        // 用户名
        $username = $request->get('username');
        if( !empty($username) ){
            array_push($where,['users.username','=',$username]);
        }

        // 派奖状态
        $status = $request->get('status');
        if( isset($status) && $status>=0){
            array_push($where,['activity_record.status','=',$status]);
        }

        // 派奖状态
        $ip = $request->get('ip');
        if( !empty($ip) ){
            array_push($where,['activity_record.ip','=',$ip]);
        }

        // 时间
        $time = $request->get('time');
        if( is_array($time) && count($time) == 2 ){
            array_push($where,['activity_record.created_at','>=',$time[0]]);
            array_push($where,['activity_record.created_at','<=',$time[1]]);
        }

        if( $ident == 'raffle_tickets' ){
            // 中奖等级
            $draw_level = $request->get('draw_level');
            if( isset($draw_level) && $draw_level>=0 ){
                array_push($where,[DB::raw("activity_record.extra->>'draw_level'"),'=',$draw_level]);
            }

            // 奖期
            $activity_issue = $request->get('activity_issue');
            if( isset($activity_issue) && $activity_issue>=0 ){
                array_push($where,["activity_record.activity_issue_id",'=',$activity_issue]);
            }
        }


        $filed = [
            'activity_record.id',
            'activity.title',
            'activity.ident',
            'activity_record.user_id',
            'users.username',
            'activity_record.activity_issue_id',
            'activity_record.status',
            'activity_record.ip',
            'activity_record.draw_at',
            'activity_record.created_at',
        ];
        if( $ident == 'raffle_tickets' ){
            $filed = array_merge($filed,[
                DB::raw('activity_record.extra->>\'code\' as code'),
                DB::raw('activity_record.extra->>\'draw_level\' as draw_level'),
                DB::raw('activity_issue.extra->>\'code\' as open_code'),
            ]);
        }

        $activity_record = ActivityRecord::select($filed)
            ->leftJoin('users','users.id','activity_record.user_id')
            ->leftJoin('activity','activity.id','activity_record.activity_id')
            ->leftJoin('activity_issue','activity_issue.id','activity_record.activity_issue_id')
            ->where($where)
            ->orderBy('activity_record.id', 'desc')
            ->skip($start)
            ->take($limit)
            ->get();

        $data['total'] = ActivityRecord::leftJoin('activity','activity.id','activity_record.activity_id')
            ->leftJoin('users','users.id','activity_record.user_id')
            ->where($where)
            ->count();

        if (!$activity_record->isEmpty()) {
            $data['activity_record'] = $activity_record->toArray();
        }

        return $this->response(1, 'Success!', $data);
    }

    public function putDraw(Request $request)
    {
        $id = $request->get('id');

        $effect = ActivityRecord::where('id',$id)->update([
            'status'    => 1,
            'draw_at'   => (string)Carbon::now(),
        ]);

        if( $effect ){
            return $this->response(1, '派发成功');
        }

        return $this->response(0, '派发失败！');
    }
}
