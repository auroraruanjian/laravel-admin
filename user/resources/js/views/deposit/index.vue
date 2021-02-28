<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-row :gutter="20">
                    <el-form :inline="true" :model="form" size="small">
                        <el-col :span="8">
                            <el-form-item label="日期查询">
                                <el-date-picker
                                    style="width: 350px;"
                                    v-model="form.time"
                                    type="datetimerange"
                                    range-separator="至"
                                    start-placeholder="开始时间"
                                    end-placeholder="结束时间">
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :span="4">
                            <el-form-item label="渠道查询">
                                <el-select v-model="form.payment_method_id" placeholder="渠道类型">
                                    <el-option label="全部" value=""></el-option>
                                    <el-option v-for="(item,key) in payment_method"  :key="key" :label="item.name" :value="item.id" ></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="4">
                            <el-form-item label="状态查询">
                                <el-select v-model="form.status" placeholder="渠道类型">
                                    <el-option label="全部" value=""></el-option>
                                    <el-option label="未到帐" value="1"></el-option>
                                    <el-option label="已到帐" value="2"></el-option>
                                    <el-option label="超时订单" value="4"></el-option>
                                    <el-option label="作废订单" value="5"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="条件查询">
                                <el-input placeholder="请根据选择条件输入搜索内容" v-model="form.id_or_account_number" class="input-with-select">
                                    <el-select v-model="form.form_type" slot="prepend" placeholder="请选择">
                                        <el-option label="订单号" value="1"></el-option>
                                        <el-option label="收款卡号" value="2"></el-option>
                                    </el-select>
                                    <el-button slot="append" icon="el-icon-search" @click="getAllRecord"></el-button>
                                </el-input>
                            </el-form-item>
                        </el-col>
                    </el-form>
                </el-row>
            </div>

            <el-table :data="deposits" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="header-center" label="平台订单号" prop="id"></el-table-column>
                <el-table-column align="header-center" label="到帐金额" prop="real_amount"></el-table-column>
                <el-table-column align="header-center" label="订单金额" prop="amount"></el-table-column>
                <el-table-column align="header-center" label="佣金" prop="rebates_amount"></el-table-column>
                <el-table-column align="header-center" label="付款人"></el-table-column>
                <el-table-column align="header-center" label="状态" prop="status">
                    <template slot-scope="scope" >
                        <el-tag type="info" v-if="scope.row.status==0">支付中</el-tag>
                        <el-tag type="warning" v-else-if="scope.row.status==1">已审核</el-tag>
                        <el-tag type="success" v-else-if="scope.row.status==2">充值成功</el-tag>
                        <el-tag type="danger" v-else>充值失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="新增时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="处理时间" prop="done_at"></el-table-column>
                <el-table-column align="header-center" label="渠道类型" prop="payment_method_name"></el-table-column>
                <el-table-column align="header-center" label="收款银行卡/姓名">
                    <template slot-scope="scope" >
                        {{ scope.row.payee_account_name }}/{{ scope.row.payee_account_number }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="备注" prop="remark"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="warning" @click="handleReceipted(scope.row.id)" v-if="scope.row.status==0" size="small">已收款</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllRecord" />
        </div>
    </div>
</template>

<script>
import permission from '@/directive/permission/index.js' // 权限判断指令
import Pagination from '@/components/Pagination' // Secondary package based on el-pagination
import { getAllRecord,postChanagestatus } from '@/api/deposits'
import { mapGetters } from 'vuex'

export default {
    name: "DepositIndex",
    data(){
        let curDate = new Date();

        return {
            deposits: [],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
            loading:false,
            form:{
                time:[
                    // new Date(curDate.getTime() + 24*60*60*1000),
                    // curDate
                ],
                payment_method_id:'',
                form_type:'',
                id_or_account_number:'',
                status:'',
            },
            payment_method:[],
        };
    },
    computed: {
        ...mapGetters([
            'username'
        ])
    },
    components: { Pagination },
    directives: { permission },
    created() {
        this.getAllRecord();
    },
    methods:{
        async getAllRecord(){
            this.loading =  true;

            let data = Object.assign(this.listQuery,this.form);
            let result = await getAllRecord(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.deposits = result.data.data.deposits;
                this.payment_method = result.data.data.payment_method;
            }else{
                this.$message.error(result.data.msg);
            }
            this.loading =  false;
        },
        handleReceipted( id ){
            this.$confirm('是否已确定收款?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(async () => {
                let result = await postChanagestatus({
                    id:id,
                    status:true,
                });
                let type = 'error';
                if(  result.data.code == 1 ){
                    type = 'success';
                }

                this.getAllRecord();

                this.$message({
                    type: type,
                    message: result.data.msg
                });
            });
        },
    },
    watch: {
    }
}
</script>

<style scoped>
/deep/.el-table th > .cell{
    text-align: center;
}
/deep/.el-form-item.is-required .el-form-item__label:before{
    content: "*";
    color: #f56c6c;
    margin-right: 4px;
}
/deep/.el-select .el-input {
    width: 130px;
}
/deep/.el-input-group__prepend .el-select .el-input {
    width: 100px;
}
/deep/.input-with-select .el-input-group__prepend {
    background-color: #fff;
}
</style>
