<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
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
        $file = $request->file('file');
        $path = $request->get('path');

        // 1.是否上传成功
        if (! $file->isValid()) {
            return $this->response(0, '上传失败!');
        }

        // 2.是否符合文件类型 getClientOriginalExtension 获得文件后缀名
        $fileExtension = $file->getClientOriginalExtension();
        if(! in_array($fileExtension, ['png', 'jpg', 'gif'])) {
            return $this->response(0, '文件格式错误!');
        }

        // 3.判断大小是否符合 2M
        $tmpFile = $file->getRealPath();
        if (filesize($tmpFile) >= 2048000) {
            return $this->response(0, '文件大小超过2M!');
        }

        // 4.是否是通过http请求表单提交的文件
        if (! is_uploaded_file($tmpFile)) {
            return $this->response(0, '上传失败！');
        }

        // 5.
        $origin_name = explode('.',$file->getClientOriginalName());
        $fileName = $path.'/'.$origin_name[0].'.'. $fileExtension;
        if (Storage::disk('public')->put($fileName, file_get_contents($tmpFile)) ){
            return $this->response(1, 'success！', ['filename'=>Storage::url($fileName)]);
        }

        return $this->response(0, '上传失败！');
    }

    public function deleteDelete(Request $request)
    {
        $path = $request->get('path');
        $filename = $request->get('filename');

        if( Storage::disk('public')->exists($path.'/'.$filename) && Storage::disk('public')->delete($path.'/'.$filename) ){
            return $this->response(1, '文件删除成功！');
        }

        return $this->response(0, '文件删除失败！');
    }
}
