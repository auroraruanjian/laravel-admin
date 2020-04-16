<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableActivityRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_record', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('用户ID');
            $table->integer('activity_id')->comment('活动ID');
            $table->integer('activity_issue_id')->comment('活动奖期ID');
            $table->tinyInteger('status')->unsigned()->nullable()->comment('是否已领奖: 0:否, 1:是');
            $table->ipAddress('ip')->nullable()->comment('用户ip');
            $table->jsonb('extra')->default('{}')->comment('扩展字段，存抽奖号码，中奖等级');
            $table->timestamp('draw_at')->nullable()->comment('领奖时间');

            $table->timestamps();
        });

        DB::statement("CREATE INDEX ON activity_record ((extra->>'code'))");
        DB::statement("CREATE INDEX ON activity_record ((extra->>'draw_level'))");
        DB::statement("CREATE INDEX ON activity_record (right(extra->>'code',1))");
        DB::statement("CREATE INDEX ON activity_record (right(extra->>'code',2))");
        DB::statement("CREATE INDEX ON activity_record (right(extra->>'code',3))");

        $this->_permission();
    }

    private function _permission()
    {
        $row = DB::table('admin_role_permissions')->where('name', '活动系统')->first();
        if( empty($row) ){
            return false;
        }

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id'   => $row->id,
                'rule'        => 'activityRecord/index',
                'name'        => '抽奖记录',
                'extra'       => json_encode(['icon' => 'star','component'=>'activity_record/index']),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_record');
    }
}
