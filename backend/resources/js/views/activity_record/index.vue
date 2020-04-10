<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <el-form ref="form" :model="search" label-width="80px" ><!--size="small"-->
                <el-row :gutter="80" >
                    <el-col :span="8">
                        <el-form-item label="活动名称">
                            <el-select v-model="search.ident" placeholder="请选择活动区域">
                                <el-option label="未中奖" value="-1"></el-option>
                                <el-option v-for="(item,key) in form.activity" :key='key' :label="item.title" :value="item.ident"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="用户名">
                            <el-input v-model="search.username"></el-input>
                        </el-form-item>

                        <el-form-item label="领奖状态"  v-if="search.ident=='raffle_tickets'">
                            <el-select v-model="search.status">
                                <el-option label="全部" value="-1"></el-option>
                                <el-option label="已领奖" value="1"></el-option>
                                <el-option label="未领奖" value="0"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="抽奖时间">
                            <el-date-picker
                                    v-model="search.time"
                                    type="datetimerange"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期">
                            </el-date-picker>
                        </el-form-item>

                        <el-form-item label="奖期" v-if="search.ident=='raffle_tickets'">
                            <el-select v-model="current_issue_index" @change="changeIssue">
                                <el-option label="全部" value="-1"></el-option>
                                <el-option v-for="(item,key) in form.issue" :key='key' :label="item.id" :value="key"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="IP地址">
                            <el-input v-model="search.ip"></el-input>
                        </el-form-item>

                        <el-form-item label="中奖等级" v-if="search.ident=='raffle_tickets'">
                            <el-select v-model="search.draw_level" >
                                <el-option label="全部" value="-1"></el-option>
                                <el-option v-for="(item,key) in current_issue.prize_level" :key='key' :label="item.name" :value="key"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row justify="center" type="flex">
                    <el-button type="primary" icon="el-icon-search" @click="getActivityRecord" >搜索</el-button>
                    <!--
                    <el-button type="warning" icon="el-icon-circle-plus-outline" @click="handleExport" size="small">导出</el-button>
                    -->
                </el-row>
            </el-form>
        </div>

        <div class="container" style="margin-top:20px;">
            <el-table :data="activity_record" style="width: 100%;" border >
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="活动名称" prop="title"></el-table-column>
                <el-table-column align="header-center" label="用户名" prop="username"></el-table-column>
                <el-table-column align="header-center" label="活动奖期" prop="activity_issue_id"></el-table-column>
                <el-table-column align="header-center" label="号码" prop="code" v-if="search.ident=='raffle_tickets'"></el-table-column>
                <el-table-column align="header-center" label="开奖号码" prop="open_code" v-if="search.ident=='raffle_tickets'"></el-table-column>
                <el-table-column align="header-center" label="奖级" prop="draw_level" v-if="search.ident=='raffle_tickets'"></el-table-column>
                <el-table-column align="header-center" label="领奖状态">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.draw_level >=0 && scope.row.status==1" type="success">已领奖</el-tag>
                        <el-tag v-else-if="scope.row.draw_level >=0 && scope.row.status==0" type="error">未领奖</el-tag>
                        <el-tag v-else-if="scope.row.open_code == null" type="warning">未开奖</el-tag>
                        <el-tag v-else type="info">未中奖</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="IP地址" prop="ip"></el-table-column>
                <el-table-column align="header-center" label="抽奖时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="领奖时间" prop="draw_at"></el-table-column>
                <el-table-column align="center" label="Operations">
                    <template slot-scope="scope">
                        <el-button type="primary"
                                   size="small"
                                   @click="handleDraw(scope.row.id)"
                                   v-if="search.ident=='raffle_tickets' && scope.row.status==0"
                                   v-permission="'activityRecord/draw'">
                            派奖
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getActivityRecord" />
        </div>
    </div>
</template>

<script>
    import permission from '@/directive/permission/index.js'    // 权限判断指令
    import Pagination from '@/components/Pagination'            // Secondary package based on el-pagination
    import { getActivityInit,getActivityRecord,putDraw } from '@/api/activity_record'

    export default {
        name: "activity_record",
        data(){
            let now = new Date();
            let nowTime = now.getTime() ;
            let day = now.getDay();
            let one_day = 24*60*60*1000;

            return {
                loading:false,
                current_issue_index:'-1',
                current_issue:{
                    id:'',
                    prize_level:[]
                },
                form: {
                    activity: [],
                    issue:[]
                },
                search:{
                    ident: 'raffle_tickets',
                    username:'',
                    draw_level:'-1',
                    status:'-1',
                    ip:'',
                    time : [new Date(nowTime - (day-1)*one_day),new Date(nowTime + (7-day)*one_day)],
                    activity_issue:'',
                },
                activity_record: [],
                total: 0,
                listQuery: {
                    page: 1,
                    limit: 20
                },
            };
        },
        computed: {
        },
        components: { Pagination },
        directives: { permission },
        created(){
            this.init();
        },
        methods:{
            async init(){
                this.loading =  true;

                let result = await getActivityInit();

                if( result.data.code == 1 ){
                    this.form = result.data.data;

                    this.getActivityRecord();
                }
            },
            async getActivityRecord(){
                this.loading =  true;

                let data = Object.assign(this.search,this.listQuery);

                let result = await getActivityRecord(data);

                if( result.data.code == 1 ){
                    this.total = result.data.data.total;
                    this.activity_record = result.data.data.activity_record;
                }else{
                    this.$message.error(result.data.message);
                }
                this.loading =  false;
            },
            changeIssue(){
                if( this.current_issue_index == -1 ){
                    this.current_issue = {
                        id:'',
                        prize_level:[]
                    };
                    this.search.activity_issue = '-1';
                    this.search.draw_level = '-1';
                }else{
                    this.current_issue = this.form.issue[this.current_issue_index];
                    this.search.activity_issue = this.current_issue.id;
                }
            },
            handleDraw( id ){
                this.$confirm('确认已派发奖品？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    putDraw(id).then( response => {
                        let type = 'error';
                        if( response.data.code == 1 ){
                            type = 'success';
                        }

                        this.$message({
                            message: response.data.msg,
                            type
                        });

                        this.getActivityRecord();
                    });
                });

            },
            handleExport(){

            }
        },
    }
</script>

<style scoped lang="scss" >
/deep/ .el-select {
    width:100%;
}
</style>
