<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use Common\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityController extends Controller
{
    private $filed = [
        //'ident'     => '',
        'title'     => '',
        'describe'  => '',
        'content'   => '',
        'status'    => 0,
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function postIndex(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $data = [
            'total'         => 0,
            'activitys'     => [],
        ];

        $activitys = Activity::select([
            'id',
            'ident',
            'title',
            'status',
            'created_at',
            'updated_at'
        ])
            ->orderBy('id', 'asc')
            ->skip($start)
            ->take($limit)
            ->get();

        $data['total'] = Activity::count();

        if (!$activitys->isEmpty()) {
            $data['activitys'] = $activitys->toArray();
        }

        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(Request $request)
    {

    }

    public function getEdit(Request $request)
    {
        $id = (int)$request->get('id',0);

        $activity = Activity::find($id);

        if (empty($activity)) {
            return $this->response(0, '活动不存在');
        }

        $activity = $activity->toArray();

        /*
        $activity['extra'] = json_decode($activity['extra'],true);

        foreach(\Storage::disk('public')->files('activity') as $file_name){
            $activity['file_list'][] = [
                'name'  => explode('/',$file_name)[1],
                'url'   => \Storage::url($file_name)
            ];
        }
        */

        return $this->response(1, 'success', $activity);
    }

    public function putEdit(ActivityRequest $request)
    {
        $id = (int)$request->get('id');

        $activity = Activity::find($id);

        if (empty($activity)) {
            return $this->response(0, '活动不存在');
        }

        foreach( $this->filed as $filed => $default_val ){
            /*
            if( $filed == 'start_at'){
                $activity->$filed = $request->get('activity_at')[0] ?? (string)Carbon::now()->startOfWeek();
            }elseif( $filed == 'end_at'){
                $activity->$filed = $request->get('activity_at')[1] ?? (string)Carbon::now()->endOfWeek();
            }elseif( $filed == 'extra'){
                $activity->$filed = json_encode($request->get($filed,$default_val));
            }else{
                $activity->$filed = $request->get($filed,$default_val);
            }
            */
            $activity->$filed = $request->get($filed,$default_val);
        }

        if ($activity->save()) {
            return $this->response(1, '编辑成功');
        } else {
            return $this->response(0, '编辑失败');
        }
    }

    public function deleteDelete(Request $request)
    {
        $id = $request->get('id');
        if( Activity::where('id','=',$id)->delete() ){
            return $this->response(1,'删除成功！');
        }else{
            return $this->response(0,'删除失败！');
        }
    }
}
