<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientCreateRequest;
use App\Http\Requests\CommonIndexRequest;
use Common\API\Rebates;
use Common\Models\Funds;
use Common\Models\Merchants;
use Common\Models\MerchantUsers;
use Common\Models\PaymentMethod;
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
            'status',
            //'extra'
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

        //$data['payment_method'] = PaymentMethod::select(['id','ident','name'])->where('status','=','1')->get();

        $data['rebates_limit'] = [
            'deposit'     => [],
        ];
        $self_extra = json_decode(auth()->user()->extra,true);
        if( !empty($self_extra['rebates']) && !empty($self_extra['rebates']['deposit_rebates'])){
            foreach( $self_extra['rebates']['deposit_rebates'] as $key => $deposit_rebates ){
                if( $deposit_rebates['status'] ){
                    $payment_method_name = PaymentMethod::where('id','=',$deposit_rebates['payment_method_id'])->value('name');
                    if( empty($payment_method_name) ) continue;

                    $data['rebates_limit']['deposit'][] = [
                        'id'        => $deposit_rebates['payment_method_id'],
                        'name'      => $payment_method_name,
                        'min_rate'  => $deposit_rebates['rate'],
                    ];
                }
            }
        }

        $data['rebates_limit']['withdrawal'] = false;
        if( !empty($self_extra['rebates']) && !empty($self_extra['rebates']['withdrawal_rebate']) && $self_extra['rebates']['withdrawal_rebate']['status'] ){
            $data['rebates_limit']['withdrawal'] = $self_extra['rebates']['withdrawal_rebate']['amount']??false;
        }

        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(Request $request)
    {
        $system_key = \Common\Helpers\RSA::new();
        $merchant_key = \Common\Helpers\RSA::new();

        $request_rebates        = $request->get('rebates');

        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateMerchant( $request_rebates );
        if( !$rebates ){
            return $this->response(0, $api_rebates->error_message);
        }

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

        $merchant->extra = json_encode( [
            'rebates' => $rebates,
        ]);

        if( $merchant->save() ){
            // 新增商户资金记录
            $fund = DB::table('funds')->insert(['type'=>'2','third_id' => $merchant->id]);
            if( $fund ) {
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

        $merchant = Merchants::find($id);

        if (empty($merchant)) {
            return $this->response(0, '配置不存在');
        }

        $merchant = $merchant->toArray();

        $extra = isset($merchant['extra']) ? json_decode($merchant['extra'],true) : [];
        $merchant['rebates'] = $extra['rebates']??[
                'deposit_rebates'       => [],
                'withdrawal_rebate'     => [],
                'user_deposit_rebate'   => [],
                'user_withdrawal_rebate'=> [],
            ];

        return $this->response(1, 'success', $merchant);
    }

    public function putEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $merchant = Merchants::find($id);
        if (empty($merchant)) {
            return $this->response(0, '商户不存在');
        }

        $extra = json_decode($merchant->extra,true);

        $request_rebates        = $request->get('rebates');

        $api_rebates = new Rebates();
        $rebates = $api_rebates->generateMerchant( $request_rebates,$merchant );
        if( !$rebates ){
            return $this->response(0, $api_rebates->error_message);
        }
        $extra['rebates'] = $rebates;
        $merchant->extra = json_encode($extra);

        $merchant->account     = $request->get('account',0);
        $merchant->status      = (int)$request->get('status',0)?true:false;

        if ($merchant->save()) {
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
