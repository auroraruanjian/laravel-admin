<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentCreateRequest;
use App\Http\Requests\CommonIndexRequest;
use Common\API\Rebates;
use Common\Models\AgentUsers;
use Common\Models\Funds;
use Common\Models\Merchants;
use Common\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChildAgentController extends Controller
{
    //
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function postIndex(CommonIndexRequest $request) {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $data = [
            'total'         => 0,
            'agent_list'    => [],
        ];

        $data['agent_list'] = AgentUsers::select([
            'agent_users.id',
            'agent_users.username',
            'agent_users.nickname',
            'agent_users.status',
            //'agent_users.google_key',
            'agent_users.extra',
            'au.username as parent_username',
        ])
            ->leftJoin('agent_users as au','agent_users.parent_id','au.id')
            ->where('agent_users.parent_id' , '=' , Auth()->user()->id)
            ->skip($start)
            ->take($limit)
            ->get()
            ->toArray();

        $data['total'] = AgentUsers::where('parent_id' , '=' , Auth()->user()->id)->count();

        $data['payment_method'] = PaymentMethod::select([
            'id',
            'ident',
            'name',
        ])
            ->where('status','=',true)
            ->get()
            ->toArray();

        $_prefix_payment_method = [];
        // 计算最小返点
        $api_rebates = new Rebates();
        foreach($data['payment_method'] as &$payment_method){
            $min_rate = $api_rebates->getMinRate( $payment_method['id'] );
            if( $min_rate !== false ){
                $payment_method['min_rate'] = $min_rate;
            }else{
                unset($payment_method);
            }

            $_prefix_payment_method[$payment_method['id']] = $payment_method;
        }

        // 重建索引
        $data['payment_method'] = array_merge($data['payment_method']);

        // 获取返点限制
        $data['rebates_limit'] = [
            'withdrawal_rebate'      => (float)getSysConfig('rebates_withdrawal_rebate',0),
            'user_deposit_rebate'    => (float)getSysConfig('rebates_user_deposit_rebate',0),
            'user_withdrawal_rebate' => (float)getSysConfig('rebates_user_withdrawal_rebate',0),
        ];

        foreach( $data['agent_list'] as &$agent ){
            $agent['extra'] = json_decode($agent['extra'],true);
            if( !empty($agent['extra']['rebates']) && !empty($agent['extra']['rebates']['deposit_rebates']) ){
                foreach( $agent['extra']['rebates']['deposit_rebates'] as $key => $deposit_rebate ){
                    $agent['extra']['rebates']['deposit_rebates'][$key]['payment_method_name'] = $_prefix_payment_method[$key]['name'].'费率(%)'??'';
                }
            }
        }

        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(AgentCreateRequest $request)
    {
        $user = auth()->user();

        $parent_tree = json_encode(array_merge(json_decode($user->parent_tree,true),[$user->id]));

        $password = $request->get('password');
        $repassword = $request->get('repassword');
        if( $password != $repassword ){
            return $this->response(0, '对不起，请检查密码和确认密码是否相同？');
        }

        $request_rebates        = $request->get('rebates');

        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateAgent( $request_rebates );
        if( !$rebates ){
            return $this->response(0, $api_rebates->error_message);
        }

        DB::beginTransaction();

        $agent = new AgentUsers();
        $agent->top_id      = $user->top_id;
        $agent->parent_id   = $user->id;
        $agent->parent_tree = $parent_tree;
        $agent->username    = $request->get('username');
        $agent->nickname    = $request->get('nickname');
        $agent->password    = bcrypt($password);
        $agent->status      = 1;
        $agent->google_key  = '';
        $agent->extra       = json_encode([
            'rebates'       => $rebates
        ]);

        if( $agent->save() ){
            // 新增代理资金记录
            $merchant_fund = DB::table('funds')->insert(['type'=>'1','third_id' => $agent->id]);
            if( !$merchant_fund ) {
                DB::rollBack();
                return $this->response(0, '资金添加失败');
            }

            DB::commit();
            return $this->response(1, '添加成功');
        }

        DB::rollBack();
        return $this->response(0, '添加失败');
    }

    public function getEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $agent_user = AgentUsers::find($id);

        if (empty($agent_user)) {
            return $this->response(0, '配置不存在');
        }

        $agent_user = $agent_user->toArray();

        $extra = isset($agent_user['extra']) ? json_decode($agent_user['extra'],true) : [];
        $agent_user['rebates'] = $extra['rebates']??[
                'deposit_rebates'       => [],
                'withdrawal_rebate'     => [],
                'user_deposit_rebate'   => [],
                'user_withdrawal_rebate'=> [],
            ];

        return $this->response(1, 'success', $agent_user);
    }

    public function putEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $agent = AgentUsers::find($id);
        $extra = json_decode($agent->extra,true);

        if (empty($agent)) {
            return $this->response(0, '代理不存在！');
        }

        $agent->nickname    = $request->get('nickname',0);
        $agent->status      = (int)$request->get('status',0)?true:false;

        $password        = $request->get('password','');
        if( !empty($password) ){
            $agent->password = bcrypt($password);
        }

        $request_rebates        = $request->get('rebates');

        // TODO:修改费率
        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateAgent( $request_rebates , $agent );
        if( !$rebates ){
            return $this->response(0, $api_rebates->error_message);
        }

        $extra['rebates'] = $rebates;
        $agent->extra = json_encode($extra);

        if ($agent->save()) {
            return $this->response(1, '编辑成功');
        } else {
            return $this->response(0, '编辑失败');
        }
    }

    public function deleteDelete(Request $request)
    {
        $id = (int)$request->get('id');
        if( AgentUsers::where('id','=',$id)->softDelete() ){
            return $this->response(1,'删除成功！');
        }else{
            return $this->response(0,'删除失败！');
        }
    }
}
