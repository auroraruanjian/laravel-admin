<?php
namespace Common\API;

use Carbon\Carbon;
use Common\Helpers\RSA;
use Common\Models\Merchants;
use Common\Models\Orders;
use Common\Models\Withdrawals as WithdrawalModel;
use Common\Models\UserBanks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class Withdrawal
{
    // 商户信息
    private $merchant       = [];

    // 解密的数据
    private $decrypt_data   = [];

    // 错误消息
    public $error_message   = '';

    // 支付模型
    public $withdrawal_model;

    public function __construct()
    {
    }

    /**
     * 支付
     * @return array
     * @throws Exception
     */
    public function apply(Request $request)
    {
        list($init_code,$init_message) = $this->_init( 'apply' ,$request);
        if( $init_code != 1 ){
            return [ $init_code, $init_message , []];
        }

        // 检查订单号是否存在
        if( Withdrawal::where([
                ['merchant_id','=',$this->merchant['id']],
                ['merchant_order_no',$this->decrypt_data['order_no']],
            ])
            ->count() > 0
        ){
            return [-8,'订单号已存在' , []];
        }

        // TODO 获取代付通道
        $account_number = '';
        $payee_user_id = '';

        // 获取开启接单的散户

        // 添加支付订单记录
        $withdrawal_model = new WithdrawalModel();
        $withdrawal_model->merchant_id = $this->merchant['id'];                                       // 商户ID
        //$deposits_model->payment_channel_detail_id = $channel_detail['channel_detail_id'];          // 支付通道ID
        $withdrawal_model->account_number = $account_number;                                          // 支付商户号
        $withdrawal_model->payee_user_id = $payee_user_id;                                            // 接收人ID 0：系统
        //merchant_fee  从商户号分配费率计算
        //$withdrawal_model->third_fee = $this->decrypt_data['amount'] * $channel_detail['rate'];       // 第三方手续费
        $withdrawal_model->amount = $this->decrypt_data['amount'];                                    // 金额
        $withdrawal_model->merchant_order_no = $this->decrypt_data['order_no'];                       // 订单号
        $withdrawal_model->ip = request()->ip();                                                      // IP
        $withdrawal_model->created_at = (string)Carbon::now();                                        // 申请时间
        $withdrawal_model->extra = json_encode([
            'notify_url'        => $this->decrypt_data['notify_url'],
            'bank_code'         => $this->decrypt_data['bank_code'],
            'account_name'      => $this->decrypt_data['account_name'],
            'account_number'    => $this->decrypt_data['account_number'],
            'branch'            => $this->decrypt_data['branch'],
        ]);

        try {
            // 保存订单记录
            $withdrawal_model->save();

            return [1,'申请已提交成功！'];
        } catch (\PDOException $e) {
            \Log::error($e);
            // TODO:触发系统告警-程序
            // 保存数据出错发送警报信息给系统管理员

            return [-10,'数据写入失败！' , []];
        }
    }

    /**
     * 商户查询订单状态记录
     * @param Reqeust $reqeust
     * @return array
     */
    public function query(Reqeust $request)
    {
        $return_data = [
            'code'      => '',
            'msg'       => '',
            'data'      => '',
        ];

        list($init_code,$init_message) = $this->_init( 'query' , $request );
        if( $init_code != 1 ){
            $return_data['code'] = $init_code;
            $return_data['msg'] = $init_message;
            return $return_data;
        }

        $withdrawal_record = Withdrawal::select([
            'id',
            'amount',
            'real_amount',
            'merchant_order_no',
            'status',
            'done_at',
            'created_at',
        ])
            ->where('deposits.merchant_order_no',$this->decrypt_data['order_no'])
            ->first();

        if( empty($withdrawal_record) ){
            $return_data['code'] = -8;
            $return_data['msg'] = '订单号不存在';
            return $return_data;
        }

        $withdrawal_record = $withdrawal_record->toArray();

        // 此处订单ID加密转码返回
        $withdrawal_record['id'] = id_encode($withdrawal_record['id']);
        // 增加签名
        $withdrawal_record['sign'] = md5_sign($withdrawal_record,$this->merchant['md5_key']);

        // 返回加密签名后的数据
        $return_data['code'] = 0;
        $return_data['msg']  = 'success';
        $return_data['data'] = $this->_encrypt($withdrawal_record);
        return $return_data;
    }

    /**
     * 获取商户信息
     * @param string $merchant_id 商户号
     * @return array|boolean
     */
    private function getMerchant( $merchant_id )
    {
        // 根据用户商户号获取商户信息
        $merchant = Merchants::select([
            'id',
            'account',
            //'system_public_key',
            //'system_private_key',
            'merchant_public_key',
            //'merchant_private_key',
            'md5_key'
        ])
            ->where('account',$merchant_id)
            ->first();

        // 商户号不存在
        if( empty($merchant) ) {
            return false;
        }

        return  $merchant->toArray();
    }

    /**
     * 商户请求初始化
     * @param string $api_name
     * @param Request $request
     * @param array $keys
     * @return array
     */
    private function _init(string $api_name, Request $request)
    {
        $validator = $this->_validator('request',$request->all());

        // 验证失败
        if( !$validator['status'] ) {
            return [-1, $validator['message']];
        };

        $this->merchant = $this->getMerchant( $request->get('merchant_id') );
        if( !$this->merchant ){
            return [-3, '商户不存在！'];
        }

        // 商户使用商户私钥加密数据请求平台接口，平台使用商户公钥解密数据
        $this->decrypt_data = $this->_decrypt($request->get('data'));

        // 如果数据解密失败
        if( !$this->decrypt_data ){
            return [-4, '数据解密失败！'];
        }
        if( $this->merchant['account'] != $this->decrypt_data['merchant_id'] ){
            return [-5, '对不起，账户校验失败！'];
        }

        $validator = $this->_validator($api_name,$this->decrypt_data);
        // 验证失败
        if( !$validator['status'] ) {
            return [-6,$validator['message']];
        };

        $keys = [];
        if($api_name == 'apply'){
            $keys = ['merchant_id','amount','notify_url','order_no','bank_code','account_name','account_number','branch','ip','bank_code'];
        }elseif($api_name == 'query'){
            $keys = ['merchant_id','order_no'];
        }
        $keys[] = 'sign';

        $request_data = [];
        foreach($keys as $key){
            $request_data[$key] = $this->decrypt_data[$key];
        }

        // 验证MD5签名
        if( !md5_verify($request_data,$this->merchant['md5_key']) ){
            // 签名验证失败
            return [-7,'签名校验失败！'];
        }

        return [1, 'success'];
    }

    /**
     * 获取支付模型
     * @param $ident
     * @param $channel
     * @return object
     * @throws /Exception
     */
    private function getPaymentModel($channel)
    {

        $ident = ucfirst($channel['category_ident']);

        $class = "Common\\API\\Payment\\{$ident}\\{$ident}";

        try {
            $this->pay_model_reflection = new \ReflectionClass($class);

            return $this->pay_model_reflection->newInstance($channel);
        } catch (Exception $e) {
            \Log::error($e);
            return false;
        }
    }

    public function getResponse( $pay_status )
    {
        return $this->pay_model->getResponse( $pay_status );
    }

    /**
     * 加密参数
     * @param array $data 需要加密的参数
     * @return string
     */
    public function _encrypt( $data )
    {
        return RSA::private_encrypt( json_encode($data) , $this->merchant['system_private_key'] );
    }

    /**
     * 解密参数
     * @param string $string 加密的参数
     * @return array
     */
    public function _decrypt( $string )
    {
        $decrypt_string = RSA::public_decrypt( $string , $this->merchant['merchant_public_key'] );
        if( $decrypt_string ){
            return json_decode($decrypt_string,true);
        }
        return false;
    }

    /**
     * 校验数据
     * @param string $api_name
     * @param array $data
     * @return array [status,message] status:通过为true，失败为false，message：消息
     */
    private function _validator( $api_name , $data )
    {
        $rule       = [];
        $messages   = [];

        // 原始加密请求验证
        if( $api_name == 'request' ){
            // 验证参数
            $rule = [
                'merchant_id'   => 'bail|required|alpha_dash|between:8,16',
                'data'          => 'bail|required|string',
            ];

            $messages = [
                'merchant_id.required'      => '商户号不能为空！',
                'merchant_id.alpha_dash'    => '商户号格式不正确！',
                'merchant_id.between'       => '商户号格式不正确！',
                'data.required'             => '数据不能为空！',
                'data.string'               => '数据格式不正确！',
            ];
            // 解密后的支付请求数据
        }elseif( $api_name == 'apply' ){
            // 验证数据类型是否正确
            $rule = [
                'amount'            => 'bail|required|numeric|min:0.01',
                'notify_url'        => 'bail|required|url|max:255',
                'order_no'          => 'bail|required|alpha_dash|between:8,32',
                'bank_code'         => 'bail|required|alpha',
                'account_name'      => 'bail|required|string',
                'account_number'    => 'bail|required|between:8,32|numeric',
                'branch'            => 'bail|required|alpha_dash',
                'ip'                => 'bail|required|ip',
                'sign'              => 'bail|required|string',
            ];

            $messages = [
                'amount.required'               => '金额不能为空！',
                'amount.numeric'                => '金额类型不正确！',
                'amount.min'                    => '金额不正确！',
                'notify_url.required'           => '异步回调地址不能不空！',
                'notify_url.url'                => '异步回调地址格式不正确！',
                'notify_url.max'                => '异步回调地址长度不能超过255个字符！',
                'return_url.required'           => '同步回调地址不能不空！',
                'return_url.url'                => '同步回调地址格式不正确！',
                'return_url.max'                => '同步回调地址长度不能超过255个字符！',
                'order_no.required'             => '订单号不能为空！',
                'order_no.alpha_dash'           => '订单号格式不正确！',
                'order_no.between'              => '订单号长度不正确！',
                'bank_code.required'            => '银行代码不能为空！',
                'bank_code.alpha'               => '银行代码格式不正确！',
                'account_name.required'         => '账户名不能为空！',
                'account_name.string'           => '账户名格式不正确！',
                'account_number.required'       => '账户号不能为空！',
                'account_number.between'        => '账户号格式不正确！',
                'account_number.numeric'        => '账户号格式不正确！',
                'branch.required'               => '支行名称不能为空！',
                'branch.alpha_dash'             => '支行名称格式不正确！',
                'ip.required'                   => 'IP不能为空！',
                'ip.ip'                         => 'IP格式不正确！',
                'sign.required'                 => '签名不能为空！',
                'sign.string'                   => '签名格式不正确！',
            ];
        }elseif( $api_name == 'query' ){

        }

        $validator = Validator::make($data, $rule , $messages);

        $result = [
            'status'    => true,
            'message'   => 'success',
        ];

        if ($validator->fails()) {
            foreach($validator->errors()->all() as $error){
                $result['status']   = false;
                $result['message']  = $error;
                break;
            }
        }

        return $result;
    }
}
