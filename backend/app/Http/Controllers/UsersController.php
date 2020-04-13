<?php

namespace App\Http\Controllers;

use Common\Imports\UsersImport;
use Common\Models\ActivityIssue;
use Common\Models\Users;
use Illuminate\Http\Request;
use DB;
use League\Flysystem\Config;

class UsersController extends Controller
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
        $page  = (int)$request->get('page',0);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $data = [
            'total'       => 0,
            'users_list' => [],
        ];

        $where = [];

        $username = $request->get('username');
        if( !empty($username) ){
            $where[] = ['users.username','=',$username];
        }

        $time = $request->get('time');
        if( !empty($time[0]) ){
            $where[] = ['users.created_at','>=',$time[0]];
        }
        if( !empty($time[1]) ){
            $where[] = ['users.created_at','<=',$time[1]];
        }

        $now = (string)\Carbon\Carbon::now();

        // 检查活动状态以及活动是否存在
        $activity_issue = ActivityIssue::select([
            'id',
            'start_at',
            'end_at',
        ])
            ->where([
                ['activity_id','=',1],
                ['start_at','<=',$now],
                ['end_at','>=',$now],
            ])
            ->first();

        $userslist = Users::select([
            'users.id',
            'users.username',
            'users.nickname',
            'users.status',
            'users.last_ip',
            'users.last_time',
            'users.draw_time',
            'users.created_at',
            DB::raw("count(activity_record.id) as draw_count")
        ])
            ->leftJoin('activity_record',function($join) use ($activity_issue){
                $join->on('activity_record.user_id','=','users.id');
                if( !empty($activity_issue) ){
                    $join->where([
                        ['activity_record.created_at','>=',$activity_issue->start_at],
                        ['activity_record.created_at','<=',$activity_issue->end_at]
                    ]);
                }
            });

        if( !empty($where) ) $userslist = $userslist->where($where);

        $userslist = $userslist->groupBy('users.id')
            ->orderBy('id', 'asc')
            ->skip($start)
            ->take($limit)
            ->get();

        $data['total'] = Users::where($where)->count();

        if (!$userslist->isEmpty()) {
            $data['users_list'] = $userslist->toArray();
        }

        return $this->response(1, 'Success!', $data);
    }

    public function postCreate(Request $request)
    {
        $users                  = new Users();
        $users->username        = $request->get('username','');
        $users->nickname        = $request->get('nickname','');
        $users->password        = $request->get('password','');
        $users->status          = (int)$request->get('status',0)?true:false;

        if( $users->save() ){
            return $this->response(1, '添加成功');
        } else {
            return $this->response(0, '添加失败');
        }
    }

    public function getEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $users = Users::find($id);

        if (empty($users)) {
            return $this->response(0, '配置不存在');
        }

        $users = $users->toArray();
        $users['password'] = '******';

        return $this->response(1, 'success', $users);
    }

    public function putEdit(Request $request)
    {
        $id = (int)$request->get('id');

        $users = Users::find($id);

        if (empty($users)) {
            return $this->response(0, '配置不存在失败');
        }

        $users->username        = $request->get('username','');
        $users->nickname        = $request->get('nickname','');
        $users->password        = $request->get('password','');
        $users->status          = (int)$request->get('status',0)?true:false;

        if ($users->save()) {
            return $this->response(1, '编辑成功');
        } else {
            return $this->response(0, '编辑失败');
        }
    }

    public function deleteDelete(Request $request)
    {
        $id = $request->get('id');
        if( Users::where('id','=',$id)->delete() ){
            return $this->response(1,'删除成功！');
        }else{
            return $this->response(0,'删除失败！');
        }
    }

    public function postImport(Request $request)
    {
        $file = $request->file('file');

        // 是否上传成功
        if (! $file->isValid()) {
            return $this->response(0, '上传失败!');
        }

        // 是否符合文件类型 getClientOriginalExtension 获得文件后缀名
        $fileExtension = $file->getClientOriginalExtension();
        if(! in_array($fileExtension, ['txt', 'xls', 'xlsx'])) {
            return $this->response(0, '文件格式错误!');
        }

        // 判断大小是否符合 10M
        $tmpFile = $file->getRealPath();
        if (filesize($tmpFile) >= 1024*1024*10) {
            return $this->response(0, '文件大小超过10M!');
        }

        // 是否是通过http请求表单提交的文件
        if (! is_uploaded_file($tmpFile)) {
            return $this->response(0, '上传失败！');
        }

        // 根据后缀导入文件
        $import_data = [];
        if( $fileExtension == 'txt' ){
            $password = bcrypt('123456');
            foreach(file($tmpFile) as $key => $item){
                $item = explode(',',$item);

                // 验证用户名是否合法
                $draw_time = intval(trim($item[1]));
                $username = trim($item[0]);
                if( !preg_match('/^[a-zA-Z1-9_]{4,32}$/',$username) ){
                    return $this->response(0, '对不起，第['.($key+1).']行【'.$username.'】用户名不合法！');
                }

                $import_data[] = "('".$username."','".$password."',".$draw_time.")";
            }

            if( count($import_data) == 0 ){
                return $this->response(0, '没有可导入数据！');
            }

            $import_sql = implode(',',$import_data);

            try{
                $affect = DB::statement("
                    INSERT INTO users (username,password,draw_time) VALUES
                    {$import_sql}
                    ON CONFLICT(username) 
                    DO UPDATE SET draw_time = users.draw_time + EXCLUDED.draw_time 
                ");
            }catch (\Exception $e){
                return $this->response(0, '系统异常！');
            }

            if( $affect > 0 ){
                return $this->response(1, '导入成功！');
            }
        }elseif( $fileExtension == 'xls' || $fileExtension == 'xlsx' ){
            $fileName = 'tmp/tmp.'. $fileExtension;

            // 需要移动到系统导入，导入完删除文件
            if (\Storage::disk('public')->put($fileName, file_get_contents($tmpFile)) ){
                try{
                    \Excel::import(new UsersImport(),''.config('filesystems.disks.public.root').'/'.$fileName);
                }catch (\Exception $e){
                    return $this->response(0, $e->getMessage());
                }finally{
                    \Storage::disk('public')->delete($fileName);
                }

                return $this->response(1, '导入成功！');
            }
        }

        return $this->response(0, '导入失败！');
    }
}
