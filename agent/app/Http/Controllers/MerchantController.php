<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientCreateRequest;
use App\Http\Requests\CommonIndexRequest;
use Common\Models\Funds;
use Common\Models\Merchants;
use Common\Models\MerchantUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psy\Util\Str;

class MerchantController extends Controller
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
            'merchant_list' => [],
        ];

        $merchant = Merchants::select([
            'id',
            'agent_id',
            'account',
            'nickname',
            'status'
        ])
            ->where('agent_id','=',auth()->id())
            ->orderBy('merchants.id', 'asc')
            ->skip($start)
            ->take($limit)
            ->get();

        $data['total'] = Merchants::where('agent_id','=',auth()->id())->count();

        if (!$merchant->isEmpty()) {
            $data['merchant_list'] = $merchant->toArray();
        }

        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(Request $request)
    {
        $system_key = \Common\Helpers\RSA::new();
        $merchant_key = \Common\Helpers\RSA::new();

        DB::beginTransaction();
        $merchant             = new Merchants();
        $merchant->agent_id   =  auth()->id();
        $merchant->account    = $request->get('account')??0;
        $merchant->nickname   = $request->get('nickname');

        $merchant->system_public_key    = $system_key['public'];
        $merchant->system_private_key   = $system_key['private'];
        $merchant->merchant_public_key  = $merchant_key['public'];
        $merchant->merchant_private_key = $merchant_key['private'];
        $merchant->md5_key              = \Illuminate\Support\Str::random(32);

        $merchant->status     = (int)$request->get('status',0)?true:false;

        if( $merchant->save() ){
            // 新增商户资金记录
            $merchant_fund = DB::table('merchant_fund')->insert(['type'=>'1','third_id' => $merchant->id]);
            if( $merchant_fund ) {
                // TODO:新增商户系统超级管理员
                $merchant_user = new MerchantUsers();
                $merchant_user->merchant_id = $merchant->id;
                $merchant_user->username    = 'admin';
                $merchant_user->nickname    = '管理员';
                $merchant_user->phone       = $request->get('phone');
                $merchant_user->password    = $request->get('password');
                $merchant_user->pay_password= $request->get('pay_password');

                if($merchant_user->save()){
                    DB::commit();
                    return $this->response(1, '添加成功');
                }
            }else{
                DB::rollBack();
                return $this->response(0, '金额添加失败！');
            }
        }

        DB::rollBack();
        return $this->response(0, '添加失败');
    }

    public function getEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $client = Merchants::find($id);

        if (empty($client)) {
            return $this->response(0, '配置不存在');
        }

        $client = $client->toArray();

        return $this->response(1, 'success', $client);
    }

    public function putEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $client = Merchants::find($id);

        if (empty($client)) {
            return $this->response(0, '配置不存在失败');
        }

        $client->account     = $request->get('account',0);
        $client->status      = (int)$request->get('status',0)?true:false;

        if ($client->save()) {
            return $this->response(1, '编辑成功');
        } else {
            return $this->response(0, '编辑失败');
        }
    }

    public function deleteDelete(Request $request)
    {
        $id = (int)$request->get('id');
        if( Merchants::where('id','=',$id)->delete() ){
            return $this->response(1,'删除成功！');
        }else{
            return $this->response(0,'删除失败！');
        }
    }
}
