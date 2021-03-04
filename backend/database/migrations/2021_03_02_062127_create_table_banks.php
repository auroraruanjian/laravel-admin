<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name', 30)->comment("银行名称");
            $table->string('ident', 30)->comment("唯一标示");
            $table->boolean('withdraw')->default(0)->comment("是否接受提现,1允许提现");
            $table->boolean('disabled')->default(0)->comment("是否禁用,1禁用");
        });
        $this->data();
    }
    private function data()
    {
        $sql="
insert into banks (name, ident, disabled, withdraw) values('中国工商银行','ICBC','0','1');
insert into banks (name, ident, disabled, withdraw) values('中国建设银行','CCB','0','1');
insert into banks (name, ident, disabled, withdraw) values('交通银行','BOCOM','0','1');
insert into banks (name, ident, disabled, withdraw) values('招商银行','CMB','0','1');
insert into banks (name, ident, disabled, withdraw) values('中国民生银行','CMBC','0','1');
insert into banks (name, ident, disabled, withdraw) values('中国农业银行','ABC','0','1');
insert into banks (name, ident, disabled, withdraw) values('中国银行','BOC','0','1');
insert into banks (name, ident, disabled, withdraw) values('平安银行(原深圳发展银行)','PAB','0','1');
insert into banks (name, ident, disabled, withdraw) values('兴业银行','CIB','0','1');
insert into banks (name, ident, disabled, withdraw) values('中信银行','CNCB','0','1');
insert into banks (name, ident, disabled, withdraw) values('华夏银行','HXB','0','1');
insert into banks (name, ident, disabled, withdraw) values('中国光大银行','CEB','0','1');
insert into banks (name, ident, disabled, withdraw) values('中国邮政储蓄银行','PSBC','0','1');
insert into banks (name, ident, disabled, withdraw) values('恒丰银行','EGB','0','0');
insert into banks (name, ident, disabled, withdraw) values('浙商银行','CZBANK','0','0');
insert into banks (name, ident, disabled, withdraw) values('渤海银行','CBHB','0','0');
insert into banks (name, ident, disabled, withdraw) values('徽商银行股份有限公司','HSB','0','0');
insert into banks (name, ident, disabled, withdraw) values('上海农村商业银行','SNCB','0','0');
insert into banks (name, ident, disabled, withdraw) values('韩国外换银行','KEB','0','0');
insert into banks (name, ident, disabled, withdraw) values('友利银行','WOORI','0','0');
insert into banks (name, ident, disabled, withdraw) values('新韩银行','SHB','0','0');
insert into banks (name, ident, disabled, withdraw) values('企业银行(中国)有限公司 ','IBK','0','0');
insert into banks (name, ident, disabled, withdraw) values('韩亚银行','HNB','0','0');
insert into banks (name, ident, disabled, withdraw) values('广东发展银行','GDB','0','0');
insert into banks (name, ident, disabled, withdraw) values('财付通','tenpay','0','0');
insert into banks (name, ident, disabled, withdraw) values('上海浦东发展银行','SPDB','0','0');
";
          DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (app()->environment() != 'production') {
            Schema::dropIfExists('banks');
        }
    }
}
