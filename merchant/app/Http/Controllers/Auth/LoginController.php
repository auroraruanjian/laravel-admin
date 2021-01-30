<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Common\Models\Merchants;
use core\lib\session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Common\Models\AdminUser;
use Cache;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username(){
        return "username";
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'account' => 'required|string',
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('merchant_id',$this->username(), 'password');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $merchant = Merchants::select('id')->where('account','=',$request->get('account'))->first();
        if( empty($merchant) ){
            return $this->sendFailedLoginResponse($request);
        }

        $request->offsetSet('merchant_id', $merchant->id);

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return redirect('/#/login');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return [
            'code'      => 1,
            'data'      => [
                'token'    => $request->session()->token(),//$user->last_session,
            ]
        ];
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->start();

        return [
            'code'      => 1,
            'msg'       => __('auth.logoutSuccess'),
        ];
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        // throw ValidationException::withMessages([
        //     $this->username() => [trans('auth.failed')],
        // ]);
        return [
            'code'    => -422,
            'msg'     => trans('auth.failed'),
        ];
    }

    /**
     * 微信登陆-获取state
     * @param Request $request
     */
    public function wechat(Request $request)
    {
        if( auth()->id() && !empty(auth()->user()->unionid)){
            return $this->response(0,'您已绑定微信');
        }

        $state = $request->get('state');
        $mode  = $request->get('mode','web');

        if(!empty($state) && Cache::has($state) ){
            $data = Cache::get($state);
            \Log::info($data);
            if( !empty($data) ){
                Cache::forget($state);
                // 检查用户是否已登录
                if( auth()->id() ){
                    $user = auth()->user();
                    $user->unionid = $data['openid'];
                    if( $user->save() ){
                        return $this->response(1,'微信绑定成功！');
                    }else{
                        return $this->response(0,'等待登陆请求...');
                    }
                }else{
                    $user = AdminUser::where('unionid',$data['openid'])->first();
                    if( !empty($user) ){
                        \Auth::login($user);

                        return response()->json($this->authenticated($request,$user));
                    }
                }
            }
            if( strpos($state,$mode) === 0 ){
                return $this->response(0,'等待登陆请求...');
            }
        }

        $appid          = getSysConfig('wechat_appid');
        $redirect_uri   = getSysConfig('wechat_callback_url');

        $prefix = 'web_';
        if( $mode == 'h5' ){
            $prefix = 'h5_';
        }
        $state          = $prefix.str_random(32);

        $wechat_qrcode_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";

        if( !empty(session('state'))){
            session()->remove('state');
            Cache::forget($state);
        }
        session('state',$state);
        Cache::put($state,[],now()->addMinutes(1));

        return $this->response(2,'Success',[
            'qrcode'    => $wechat_qrcode_url,
            'state'     => $state,
        ]);
    }

    /**
     * 微信登陆-请求微信换取id
     * @param Request $request
     */
    public function wechatCallback(Request $request)
    {
        $appid          = getSysConfig('wechat_appid');
        $secret         = getSysConfig('wechat_secret');

        $code   = $request->get('code');

        // 校验state是否合法,5分钟有效
        $state  = $request->get('state');

        // 请求微信接口，获取unionid，测试环境返回openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
        $data = file_get_contents($url);
        if (!$data) {
            return view('wechat',[
                'code'      => '0',
                'title'     => '接口请求异常！',
                'desc'      => '',
            ]);
        }
        $data = json_decode($data, true);

        if( isset( $data['errcode'] ) ){
            return view('wechat',[
                'code'      => '0',
                'title'     => $data['errmsg'],
                'desc'      => '',
            ]);
        }
        if( empty($data['openid'])  || empty( $data['access_token'] ) || empty($data['refresh_token']) || empty($data['scope'])){
            return view('wechat',[
                'code'      => '0',
                'title'     => '参数不完整！',
                'desc'      => '',
            ]);
        }

        // 如果未绑定账户返回403错误
        $user = AdminUser::where('unionid',$data['openid'])->first();
        if( !empty($user) ){
            if( strpos($state,'web_') === 0 ){
                Cache::put($state,$data,now()->addMinutes(1));
                return view('wechat',[
                    'code'      => '1',
                    'title'     => '授权成功！',
                    'desc'      => '',
                ]);
            }else {
                \Auth::login($user);

                Cache::forget($state);

                return redirect('/#/login?token=' . $request->session()->token(), 302);
            }
        }else{
            Cache::put($state,$data,now()->addMinutes(1));
            return view('wechat',[
                'code'      => '1',
                'title'     => '微信绑定中...',
                'desc'      => '',
            ]);
            // return view('wechat',[
            //     'code'      => '0',
            //     'title'     => '您的微信还未绑定账户！',
            //     'desc'      => '',
            // ]);
        }
    }
}
