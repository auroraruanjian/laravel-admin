<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Common\Models\Activity;
use Common\Models\ActivityIssue;
use Common\Models\ActivityRecord;
use Illuminate\Http\Request;
use DB;

class ActivityController extends Controller
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

    public function getIndex(Request $request)
    {
        $activity_id = (int)$request->get('id',1);

        $now = (string)Carbon::now();
        // 检查活动状态以及活动是否存在
        $activity_issue = ActivityIssue::select([
            'activity_issue.id as issue_id',
            'activity_issue.activity_id',
            'activity_issue.start_at',
            'activity_issue.end_at',
            DB::raw("activity_issue.extra->>'tickets_total' as tickets_total"),
            DB::raw("activity_issue.extra->'prize_level' as prize_level"),
            'activity.ident',
            'activity.title',
            'activity.describe',
            'activity.content',
            'activity.status',
        ])
            ->leftJoin('activity','activity.id','activity_issue.activity_id')
            ->where([
                ['activity_issue.activity_id','=',$activity_id],
                ['activity_issue.start_at','<=',$now],
                ['activity_issue.end_at','>=',$now],
            ])
            ->orderBy('activity_issue.id','desc')
            ->first();

        if( !empty($activity_issue) ){
            $activity_issue = $activity_issue->toArray();
            $activity_issue['prize_level'] = json_decode($activity_issue['prize_level'],true);
            $activity_issue['code_len'] = strlen($activity_issue['tickets_total']*1000)-1;

            unset($activity_issue['tickets_total']);
        }else{
            $activity_issue = [];
        }

        $activity_issue['file_path'] = '/storage/activity/'.$activity_issue['issue_id'].'/';
        unset($activity_issue['tickets_total']);

        return $this->response(1,'success',$activity_issue);
    }

    /**
     * 抽奖
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDraw(Request $request)
    {
        $activity_id = (int)$request->get('id',1);

        $now = (string)Carbon::now();

        // 检查活动状态以及活动是否存在
        $activity_issue = ActivityIssue::select([
            'id',
            'activity_id',
            'start_at',
            'end_at',
            DB::raw("extra->>'tickets_total' as tickets_total"),
            DB::raw("extra->'tickets_rule' as tickets_rule"),
        ])
            ->where([
                ['activity_id','=',$activity_id],
                ['start_at','<=',$now],
                ['end_at','>=',$now],
            ])
            ->first();

        if( empty($activity_issue) ){
            return $this->response(0,'对不起，没可用活动！');
        }

        if( empty($activity_issue->tickets_total) || empty($activity_issue->tickets_rule) ){
            return $this->response(0,'对不起，活动配置错误！');
        }

        // 检查抽奖次数
        $draw_count = ActivityRecord::where([
                ['user_id','=',auth()->id()],
                ['created_at','>=',$activity_issue->start_at],
                ['created_at','<=',$activity_issue->end_at]
            ])
            ->count();
        if( $draw_count >= auth()->user()->draw_time ){
            return $this->response(0,'对不起，您的抽奖次数已经用完！');
        }

        // 获取今天和开始天数间隔
        $diff_day = Carbon::parse($activity_issue->start_at)->diffInDays($now);

        $activity_issue->tickets_rule = json_decode($activity_issue->tickets_rule,true);

        if( empty($activity_issue->tickets_rule[$diff_day]) || !is_array($activity_issue->tickets_rule[$diff_day]) ){
            return $this->response(0,'对不起，前缀规则配置错误！');
        }

        $rules = $activity_issue->tickets_rule[$diff_day]['range'];

        // 生成号码
        $code = str_pad(
                mt_rand(
                    $rules[0],
                    $rules[1]
                ),
                strlen($activity_issue->tickets_total-1),
                '0',
                STR_PAD_LEFT
            ).str_pad(mt_rand(0,999),3,'0',STR_PAD_LEFT);


        // 写入抽奖记录
        $activity_record = new ActivityRecord();
        $activity_record->user_id = auth()->id();
        $activity_record->activity_id = $activity_issue->activity_id;
        $activity_record->activity_issue_id = $activity_issue->id;
        $activity_record->ip = $request->ip();
        $activity_record->extra = json_encode(['code' => $code]);

        if( $activity_record->save() ){
            return $this->response(1,'抽奖成功，号码为：'.$code,['code'=>str_split($code)]);
        }

        return $this->response(0,'抽奖失败！');
    }

    /**
     * 获取抽奖记录
     */
    public function postRecord(Request $request)
    {
        $ident = $request->get('ident');
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit',20);

        $start = ($page - 1) * $limit;

        $data = [
            'total'             => 0,
            'activity_record'    => [],
        ];

        $where = [
            ['activity.ident','=',$ident],
            ['activity_record.user_id','=',auth()->id()]
        ];

        $start_at = $request->get('start_at');
        if( !empty($start_at) ){
            $where[] = ['activity_record.created_at','>=',$start_at];
        }

        $end_at = $request->get('end_at');
        if( !empty($end_at) ){
            $where[] = ['activity_record.created_at','<=',$end_at];
        }

        $filed = [
            'activity_record.id',
            'activity.title',
            'activity.ident',
            'activity_record.user_id',
            'activity_record.activity_issue_id',
            'activity_record.status',
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
            ->leftJoin('activity','activity.id','activity_record.activity_id')
            ->leftJoin('activity_issue','activity_issue.id','activity_record.activity_issue_id')
            ->where($where)
            ->orderBy('activity_record.id', 'desc')
            ->skip($start)
            ->take($limit)
            ->get();

        $data['total'] = ActivityRecord::leftJoin('activity','activity.id','activity_record.activity_id')
            ->where($where)
            ->count();

        if (!$activity_record->isEmpty()) {
            $data['activity_record'] = $activity_record->toArray();
        }

        return $this->response(1, 'Success!', $data);
    }

    /**
     * 获取中奖榜单
     *
     */
    public function getRankList(Request $request)
    {
        $filed = [
            'activity.title',
            'users.username',
            DB::raw("to_char(activity_record.draw_at,'yyyy-mm-dd') as draw_at"),
            DB::raw("to_char(activity_record.created_at,'yyyy-mm-dd') as created_at"),
            DB::raw("activity_record.extra->>'draw_level' as draw_level"),
            DB::raw("activity_issue.extra->'prize_level' as prize_level"),
        ];

        $activity_record = ActivityRecord::select($filed)
            ->leftJoin('users','users.id','activity_record.user_id')
            ->leftJoin('activity','activity.id','activity_record.activity_id')
            ->leftJoin('activity_issue','activity_issue.id','activity_record.activity_issue_id')
            ->whereRaw("cast(activity_record.extra->>'draw_level' as integer) >= 0")
            ->orderBy('activity_record.id', 'desc')
            ->skip(0)
            ->take(100)
            ->get();


        if(!empty($activity_record)) {
            $activity_record = $activity_record->toArray();
        }else{
            $activity_record = [];
        }

        foreach ($activity_record as $key => $item){
            $prize_level = json_decode($item['prize_level'],true);

            $activity_record[$key]['prize_level_name'] = $prize_level[$item['draw_level']]['name'];

            // str_pad('',strlen($item['username'])-4,'*')
            $activity_record[$key]['username'] = substr($item['username'],0,2).'***'.substr($item['username'],-2);

            $activity_record[$key]['created_at']  = Carbon::parse($item['created_at'])->format('Y-m-d');

            unset($activity_record[$key]['prize_level']);
        }

        return $this->response(1,'success',$activity_record);
    }
}
