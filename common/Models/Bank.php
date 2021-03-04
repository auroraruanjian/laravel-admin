<?php

namespace Common\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';
    public $timestamps = false;

    public static function luhm($s)
    {
        $n = 0;
        for ($i = strlen($s); $i >= 1; $i--) {
            $index = $i - 1;
            //偶数位
            if ($i % 2 == 0) {
                $n += $s{$index};
            } else {//奇数位
                $t = $s{$index} * 2;
                if ($t > 9) {
                    $t = (int)($t / 10) + $t % 10;
                }
                $n += $t;
            }
        }
        return ($n % 10) == 0;
    }
}
