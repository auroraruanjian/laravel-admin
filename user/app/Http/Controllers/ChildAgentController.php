<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentCreateRequest;
use App\Http\Requests\CommonIndexRequest;
use Common\Models\AgentUsers;
use Common\Models\MerchantFund;
use Common\Models\Merchants;
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
            'total'       => 0,
            'client_list' => [],
        ];

        $agent_users = AgentUsers::select([
            'agent_users.id',
            'agent_users.username',
            'agent_users.nickname',
            'agent_users.status',
            'agent_users.google_key',
            'au.username as parent_username',
        ])
            ->leftJoin('agent_users as au','agent_users.parent_id','au.id')
            ->where('agent_users.parent_id' , '=' , Auth()->user()->id)
            ->skip($start)
            ->take($limit)
            ->get();


        $data['total'] = AgentUsers::where('parent_id' , '=' , Auth()->user()->id)->count();

        if (!$agent_users->isEmpty()) {
            $data['agent_list'] = $agent_users->toArray();
        }

        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(AgentCreateRequest $request)
    {
        $user = auth()->user();

        $parent_tree = json_encode(array_merge(json_decode($user->parent_tree,true),[$user->id]));

        DB::beginTransaction();

        $agent = new AgentUsers();
        $agent->top_id      = $user->top_id;
        $agent->parent_id   = $user->id;
        $agent->parent_tree = $parent_tree;
        $agent->username    = $request->get('username');
        $agent->nickname    = $request->get('nickname');
        $agent->password    = bcrypt($request->get('password'));
        $agent->status      = 1;
        $agent->google_key  = '';

        if( $agent->save() ){
            // TODO：新增代理资金记录

            // TODO：新增支付费率

            DB::commit();
            return $this->response(1, '添加成功');
        }

        DB::rollBack();
        return $this->response(0, '添加失败');
    }

    public function getEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $client = AgentUsers::find($id);

        if (empty($client)) {
            return $this->response(0, '配置不存在');
        }

        $client = $client->toArray();

        return $this->response(1, 'success', $client);
    }

    public function putEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $agent = AgentUsers::find($id);

        if (empty($client)) {
            return $this->response(0, '配置不存在失败');
        }

        $agent->nickname    = $request->get('nickname',0);
        $agent->status      = (int)$request->get('status',0)?true:false;

        // TODO:修改费率


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
