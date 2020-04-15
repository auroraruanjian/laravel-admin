<?php

namespace Common\Exports;

use Common\Models\ActivityRecord;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActivityRecordExport implements FromQuery,WithHeadings,WithColumnFormatting,ShouldAutoSize,WithMapping
{
    use Exportable;

    private $_query;
    private $_ident;

    public function __construct( $query , $ident)
    {
        $this->_query = $query;
        $this->_ident = $ident;
    }

    public function headings(): array
    {
        $head = [];
        if( $this->_ident == 'raffle_tickets' ){
            $head = [
                '编号',
                '活动名称',
                '用户名',
                '活动奖期',
                '号码',
                '开奖号码',
                '中奖等级',
                //'奖品名称',
                '领奖状态',
                'IP',
                '抽奖时间',
                '领奖时间',
            ];
        }
        return $head;
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $filed = [];
        if( $this->_ident == 'raffle_tickets' ){
            if( $row->draw_level >= 0 && $row->status ==1 ){
                $status = '已领奖';
            }elseif($row->draw_level >=0 && $row->status==0){
                $status = '未领奖';
            }elseif( isNull($row->open_code) ){
                $status = '未开奖';
            }else{
                $status = '未中奖';
            }

            $filed = [
                $row->id,
                $row->title,
                $row->username,
                $row->activity_issue_id,
                $row->code,
                $row->open_code,
                $row->draw_level,
                $status,
                $row->ip,
                $row->created_at,
                $row->draw_at,
            ];
        }
        return $filed;
    }

    public function columnFormats(): array
    {
        return [
//            'draw_at' => NumberFormat::FORMAT_DATE_DDMMYYYY,
//            'created_at' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function collection()
    {
        return $this->_query->get();
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return $this->_query;
    }
}
