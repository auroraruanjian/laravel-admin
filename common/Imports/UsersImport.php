<?php

namespace Common\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use DB;

class UsersImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        DB::beginTransaction();
        foreach($collection as $key => $item){
            if( empty($item) || empty($item[0])) continue;

            $draw_time = intval(trim($item[1]));
            $username = trim($item[0]);

            if( !preg_match('/^[a-zA-Z1-9_]{4,32}$/',$username) ){
                throw new \Exception('对不起，第['.($key+1).']行【'.$username.'】用户名不合法！',-1);
            }

            try{
                $affect = DB::statement("
                        INSERT INTO users (username,password,draw_time) VALUES (:username,:password,:draw_time)
                        ON CONFLICT(username) 
                        DO UPDATE SET draw_time = users.draw_time + EXCLUDED.draw_time 
                    ",[
                        'username'  => $username,
                        'password'  => bcrypt('123456'),
                        'draw_time' => $draw_time,
                ]);
            }catch (\Exception $e){
                DB::rollback();
            }
        }
        DB::commit();
    }
}
