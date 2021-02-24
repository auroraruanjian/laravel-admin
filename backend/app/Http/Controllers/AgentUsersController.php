<?php

namespace App\Http\Controllers;

use Common\API\Rebates;
use Common\Models\AgentUsers;
use Common\Models\PaymentChannelDetail;
use Common\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentUsersController extends Controller
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

        $data['agent_users_list'] = AgentUsers::select([
            'agent_users.id',
            'agent_users.username',
            'agent_users.nickname',
            'agent_users.status',
            'agent_users.last_ip',
            'agent_users.last_time',
            'agent_users.created_at'
        ])
            ->orderBy('id', 'asc')
            ->skip($start)
            ->take($limit)
            ->get()
            ->toArray();

        $data['total'] = AgentUsers::count();

        $data['payment_method'] = PaymentMethod::select([
            'id',
            'ident',
            'name',
        ])
            ->where('status','=',true)
            ->get()
            ->toArray();

        // 计算最小返点
        $api_rebates = new Rebates();
        foreach($data['payment_method'] as &$payment_method){
            $min_rate = $api_rebates->getMinRate( $payment_method['id'] );
            if( $min_rate !== false ){
                $payment_method['min_rate'] = $min_rate;
            }else{
                unset($payment_method);
            }
        }

        // 重建索引
        $data['payment_method'] = array_merge($data['payment_method']);

        // 获取返点限制
        $data['rebates_limit'] = [
            'withdrawal_rebate'      => (float)getSysConfig('rebates_withdrawal_rebate',0),
            'user_deposit_rebate'    => (float)getSysConfig('rebates_user_deposit_rebate',0),
            'user_withdrawal_rebate' => (float)getSysConfig('rebates_user_withdrawal_rebate',0),
        ];


        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(Request $request)
    {
        $users                  = new AgentUsers();
        $users->username        = $request->get('username','');
        $users->nickname        = $request->get('nickname','');
        $users->password        = $request->get('password','');
        //$users->user_group_id   = $request->get('user_group_id');
        $users->status          = (int)$request->get('status',0)?true:false;

        $request_rebates        = $request->get('rebates');

        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateAgent( $request_rebates );
        if( !$rebates ){
            return $this->response(0, $api_rebates->error_message);
        }

        $users->extra = json_encode([
            'rebates'   => $rebates,
        ]);

        if( $users->save() ){
            return $this->response(1, '添加成功');
        } else {
            return $this->response(0, '添加失败');
        }
    }

    public function getEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $users = AgentUsers::find($id);

        if (empty($users)) {
            return $this->response(0, '代理不存在');
        }

        $users = $users->toArray();
        $extra = isset($users['extra']) ? json_decode($users['extra'],true) : [];
        $users['rebates'] = $extra['rebates']??[
                'deposit_rebates'       => [],
                'withdrawal_rebate'     => [],
                'user_deposit_rebate'   => [],
                'user_withdrawal_rebate'=> [],
            ];;
//        if( !empty($extra['rebates']) ){
//            foreach($extra['rebates'] as $rebate){
//                $users['rebates'][$rebate['payment_method_id']] = $rebate;
//            }
//        }

        return $this->response(1, 'success', $users);
    }

    public function putEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $users = AgentUsers::find($id);
        $extra = json_decode($users->extra,true);

        if (empty($users)) {
            return $this->response(0, '代理不存在');
        }

        $users->nickname        = $request->get('nickname','');
        $users->status          = (int)$request->get('status',0)?true:false;


        $password        = $request->get('password','');
        if( !empty($password) ){
            $users->password = bcrypt($password);
        }

        $request_rebates        = $request->get('rebates');

        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateAgent( $request_rebates, $users);
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
        if( AgentUsers::where('id','=',$id)->delete() ){
            return $this->response(1,'删除成功！');
        }else{
            return $this->response(0,'删除失败！');
        }
    }
}
