<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    private $withdrawal_api;

    public function __construct(Request $request)
    {
        // 接口版本号
        $version      = $request->get('version');
        if( !empty($version) && !preg_match("/^V[0-9]{0,3}(\.[0-9]{0,3}){1,2}$/",$version) ){
            abort(403,'接口版本号错误！');
        }

        $class_name = 'Common\\API\\'.((!empty($version)&&$version!='V1.0')?$version.'\\':'').'Withdrawal';

        // 根据版本号构建接口模型
        try{
            $withdrawal_ref = new \ReflectionClass($class_name);

            $this->withdrawal_api = $withdrawal_ref->newInstance();
        }catch(\ReflectionException $e){

            abort(403,'接口版本号错误2！');
        }catch(\Exception $e){

            abort(403,'未知的异常！');
        }
    }

    /**
     * 申请代付
     * @param Request $request
     */
    public function apply(Request $request)
    {
        /**
         * $code    : 类型
         * $message : 消息内容
         * $data    : 数据
         */
        list($code , $message ,$data) = $this->withdrawal_api->apply($request);

        return $this->response($code,$message , $data);
    }

    /**
     * 查询代付订单状态
     * @param Request $request
     */
    public function query(Request $request)
    {
        return response()->json($this->withdrawal_api->query($request));
    }
}
