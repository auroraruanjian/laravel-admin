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

            $draw_time = (int)trim($item[1]);
            $username = trim($item[0]);

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
