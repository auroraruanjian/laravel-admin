<?php

namespace App\Http\Controllers;

use Common\API\Rebates;
use Common\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
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

    public function postIndex(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $data = [
            'total'       => 0,
            'users_list' => [],
        ];

        $data['users_list'] = Users::select([
            'users.id',
            'users.username',
            'users.nickname',
            'users.status',
            'users.last_ip',
            'users.last_time',
            'users.created_at',
            'extra'
        ])
            //->leftJoin('merchants','merchants.id','users.merchants_id')
            //->leftJoin('user_group','user_group.id','users.user_group_id')
            ->orderBy('id', 'asc')
            ->skip($start)
            ->take($limit)
            ->get()
            ->toArray();

        foreach( $data['users_list'] as $key => $user ){
            $data['users_list'][$key]['extra'] = json_decode($data['users_list'][$key]['extra'],true);
            $data['users_list'][$key]['rebates'] = $data['users_list'][$key]['extra']['rebates']??[];
        }

        $data['total'] = Users::count();

        // 获取返点限制
        $data['rebates_limit'] = [
            'user_deposit_rebate'    => (float)getSysConfig('rebates_user_deposit_rebate',0),
            'user_withdrawal_rebate' => (float)getSysConfig('rebates_user_withdrawal_rebate',0),
        ];

        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(Request $request)
    {
        $request_rebates        = $request->get('rebates');

        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateUser( $request_rebates );
        if( !$rebates ){
            return $this->response(0, $api_rebates->error_message);
        }

        DB::beginTransaction();
        $users                  = new Users();
        $users->username        = $request->get('username','');
        $users->nickname        = $request->get('nickname','');
        $users->password        = bcrypt($request->get('password',''));
        //$users->user_group_id   = $request->get('user_group_id');
        $users->status          = (int)$request->get('status',0)?true:false;

        $users->extra = json_encode([
            'rebates'   => $rebates,
        ]);

        if( $users->save() ){
            // 新增商户资金记录
            $fund = DB::table('funds')->insert(['type'=>'3','third_id' => $users->id]);

            if( $fund ){
                DB::commit();
                return $this->response(1, '添加成功');
            }
        }

        DB::rollBack();
        return $this->response(0, '添加失败');
    }

    public function getEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $users = Users::find($id);

        if (empty($users)) {
            return $this->response(0, '配置不存在');
        }

        $users = $users->toArray();
        $extra = isset($users['extra']) ? json_decode($users['extra'],true) : [];
        $users['rebates'] = $extra['rebates']??[
                'user_deposit_rebate'   => [],
                'user_withdrawal_rebate'=> [],
            ];;

        return $this->response(1, 'success', $users);
    }

    public function putEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $users = Users::find($id);
        $extra = json_decode($users->extra,true);

        if (empty($users)) {
            return $this->response(0, '用户不存在！');
        }

        $users->username        = $request->get('username','');
        $users->nickname        = $request->get('nickname','');

        $password        = $request->get('password','');
        if( !empty($password) ){
            $users->password = bcrypt($password);
        }

        $users->status          = (int)$request->get('status',0)?true:false;

        $request_rebates        = $request->get('rebates');

        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateUser( $request_rebates, $extra['rebates']);
        if( !$rebates ){
            return $this->response(0, $api_rebates->error_message);
        }

        $extra['rebates'] = $rebates;
        $users->extra = json_encode($extra);

        if ($users->save()) {
            return $this->response(1, '编辑成功');
        } else {
            return $this->response(0, '编辑失败');
        }
    }

    public function deleteDelete(Request $request)
    {
        $id = $request->get('id');
        if( Users::where('id','=',$id)->delete() ){
            return $this->response(1,'删除成功！');
        }else{
            return $this->response(0,'删除失败！');
        }
    }
}
