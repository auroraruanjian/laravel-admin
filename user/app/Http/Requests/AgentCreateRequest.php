<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'username'  => 'required|alpha_num',
            'nickname'  => 'required|alpha_num',
            'password'  => 'required|alpha_num',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息。
     * @return array
     */
    public function messages()
    {
        return [
            'account.required'      => '商户名称不能为空！',
            'account.alpha_num'     => '商户名称必须为数字！',
            'nickname.required'     => '状态不能为空！',
            'nickname.alpha_num'    => '状态不正确！',
            'password.required'     => '状态不能为空！',
            'password.alpha_num'    => '状态不正确！',
        ];
    }
}
