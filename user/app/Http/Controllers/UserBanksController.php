<?php

namespace App\Http\Controllers;

use Common\Models\UserBanks;
use Illuminate\Http\Request;
use Common\Models\Bank;
use Common\Models\Region;
use Illuminate\Support\Facades\Hash;

class UserBanksController extends Controller
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

    public function getIndex(Request $request)
    {
        $model = UserBanks::select([
            'user_banks.id',
            'user_banks.user_id',
            'user_banks.account_name',
            'user_banks.account_number',
//            'user_banks.banks_id',
//            'user_banks.province_id',
//            'user_banks.city_id',
//            'user_banks.district_id',
            'banks.name as bank_name',
            'user_banks.branch',
            'user_banks.status',
            'user_banks.is_delete',
            'user_banks.is_open',
            'user_banks.limit_amount',
            'user_banks.created_at'
        ])
            ->leftJoin('banks','banks.id','user_banks.banks_id')
            ->where([
                ['user_banks.user_id','=',auth()->id()],
                ['user_banks.is_delete','=',1]
            ]);

        $user_bank_list = $model->orderBy('banks.id','desc')
            ->get()
            ->toArray();

        return $this->response(1,'success',[
            'user_bank_list'        => $user_bank_list,
            'total'                 => $model->count(),
            'security_password'     => !empty(auth()->user()->security_password)?true:false,
        ]);
    }

    public function getCreate(Request $request)
    {
        $flag = $request->get('flag', '');
        if ($flag == 'get_city') {
            $id = (int)$request->get('parent_id', 0);
            $city = Region::select(['id', 'parent_id', 'name', 'level'])->where('parent_id', $id)->get()->toArray();
            foreach ($city as &$value) {
                if ($value['level'] < 3) {
                    $value['cities'] = array();
                }
            }
            return $this->response(1,'success',$city);
        }

        $user = auth()->user();
        $has_security_password = false;
        if (!empty($user->security_password)) {
            $has_security_password = true;
        }

        if (!$has_security_password) {
            //return $this->response(0,'为了您的账户安全，请先设置资金密码!');
        }

        $banks = Bank::select(['id', 'name', 'ident'])->where('disabled', false)->get()->toArray();
        $province = Region::select(['id', 'name', 'level'])->where('parent_id', 0)->get()->toArray();
        foreach ($province as &$v) {
            $v['cities'] = array();
        }

        return $this->response(1,'success',[
            'banks'     => $banks,
            'province'  => $province
        ]);
    }

    public function postCreate(Request $request)
    {
        $account_name       = $request->get('account_name');
        $account_number     = $request->get('account_number');
        $bank_id            = $request->get('bank_id');
        $branch             = $request->get('branch');
        $limit_amount       = $request->get('limit_amount');


        $province           = $request->get('province');

        $user = auth()->user();
        $security_password  = $request->get('security_password');
        if (!Hash::check($security_password, $user->security_password)) {
            //return $this->response(0,'资金密码不正确');
        }

        $user_payment_method = new UserBanks();
        $user_payment_method->user_id       = auth()->id();
        $user_payment_method->account_name = $account_name;
        $user_payment_method->account_number = $account_number;
        $user_payment_method->banks_id = $bank_id;
        $user_payment_method->branch = $branch;
        $user_payment_method->province_id = $province[0];
        $user_payment_method->city_id = $province[1];
        $user_payment_method->district_id = $province[2];
        $user_payment_method->limit_amount  = $limit_amount;

        if( $user_payment_method->save() ){
            return $this->response(1,'success');
        }
        return $this->response(0,'error');
    }

    /**
     * 修改支付方式是否开启收款
     * @param Request $request
     */
    public function putIsopen(Request $request)
    {
        $id = $request->get('id');
        $is_open = $request->get('is_open') ? 1 : 0;

        $user_payment = UserBanks::select([
            'id',
            'is_open'
        ])
            ->where([
                ['user_id','=',auth()->id()],
                ['id','=',$id]
            ])
            ->first();

        if( empty($user_payment) ){
            return $this->response(0,'对不起，支付方式不存在');
        }

        $user_payment->is_open = $is_open;
        if( $user_payment->save() ){
            return $this->response(1,'状态已修改成功！');
        }

        return $this->response(0,'状态已修改失败！');
    }

    /**
     * 修改支付方式是否可用
     * @param Request $request
     */
    public function putAvailable(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status') ? 1 : 0;

        $user_payment = UserBanks::select([
            'id',
            'status'
        ])
            ->where([
                ['user_id','=',auth()->id()],
                ['id','=',$id]
            ])
            ->first();

        if( empty($user_payment) ){
            return $this->response(0,'对不起，支付方式不存在');
        }

        $user_payment->status = $status;
        if( $user_payment->save() ){
            return $this->response(1,'状态已修改成功！');
        }

        return $this->response(0,'状态已修改失败！');
    }

    public function putChantLimitAmount(Request $request)
    {
        $id = $request->get('id');
        $limit_amount = $request->get('limit_amount');

        $user_payment = UserBanks::select([
            'id',
            'status'
        ])
            ->where([
                ['user_id','=',auth()->id()],
                ['id','=',$id]
            ])
            ->first();

        if( empty($user_payment) ){
            return $this->response(0,'对不起，支付方式不存在');
        }

        $user_payment->limit_amount = $limit_amount;
        if( $user_payment->save() ){
            return $this->response(1,'限额已修改成功！');
        }

        return $this->response(0,'限额已修改失败！');
    }
}
