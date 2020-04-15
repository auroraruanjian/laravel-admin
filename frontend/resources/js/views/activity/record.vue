<template>
    <el-dialog v-loading="record_loading" title="抽奖记录" :visible.sync="visible" :modal-append-to-body="false">
        <el-form :model="form" :inline="true" size="small ">
            <el-form-item label="时间">
                <el-col :span="11">
                    <el-date-picker type="date" placeholder="选择日期" v-model="form.start_at" style="width: 100%;"></el-date-picker>
                </el-col>
                <el-col class="line" :span="2">-</el-col>
                <el-col :span="11">
                    <el-time-picker type="fixed-time" placeholder="选择时间" v-model="form.end_at" style="width: 100%;"></el-time-picker>
                </el-col>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="onSubmit">查询</el-button>
                <el-button>取消</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="record_list" style="width: 100%" stripe>
            <el-table-column property="created_at" label="时间" ></el-table-column>
            <el-table-column property="activity_issue_id" label="奖期" width="150"></el-table-column>
            <el-table-column property="code" label="号码" width="150"></el-table-column>
            <el-table-column property="open_code" label="开奖号码" width="150"></el-table-column>
            <el-table-column label="状态" width="150">
                <template slot-scope="scope">
                    <el-tag v-if="scope.row.open_code==null" type="warning">未开奖</el-tag>
                    <el-tag v-else-if="scope.row.draw_level==0" type="success">一等奖</el-tag>
                    <el-tag v-else-if="scope.row.draw_level==1" type="success">二等奖</el-tag>
                    <el-tag v-else-if="scope.row.draw_level==2" type="success">三等奖</el-tag>
                    <el-tag v-else-if="scope.row.draw_level==3" type="success">四等奖</el-tag>
                    <el-tag v-else-if="scope.row.draw_level==4" type="success">五等奖</el-tag>
                    <el-tag v-else >未中奖</el-tag>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
            background
            current-page.sync="form.page"
            layout="prev, pager, next"
            :total="totalPage">
        </el-pagination>

        <div slot="footer" class="dialog-footer">
            <el-button @click="visible = false">取 消</el-button>
        </div>
    </el-dialog>
</template>

<script>
    import { getRecord } from '@/api/activity'

    export default {
        name: "activity_record",
        created() {
            this.$eventBus.$on('showActivityRecord',(val)=>{
                this.visible = val;

                if( this.visible ){
                    this.onSubmit();
                }
            });
        },
        data(){
            return {
                visible:false,
                form:{
                    ident:'raffle_tickets',
                    start_at: '',
                    end_at: '',
                    page:1,
                },
                formLabelWidth:'180px',
                record_loading:false,
                record_list:[],
                totalPage:0,
            };
        },
        methods:{
            onSubmit(){
                this.record_loading = true;
                getRecord( this.form ).then( response => {
                    this.record_loading = false;
                    if( response.data.code == 1 ){
                        this.totalRow = response.data.data.total;
                        this.totalPage = Math.floor(this.totalRow/20);
                        this.record_list = response.data.data.activity_record;
                    }else{
                        this.$notify({ type: 'danger', message: response.data.data.msg ,duration:1000});
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>
