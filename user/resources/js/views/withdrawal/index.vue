<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-form :inline="true" :model="form" size="small">
                    <el-row :gutter="20">
                        <el-form-item label="日期查询">
                            <el-date-picker
                                style="width: 350px;"
                                v-model="form"
                                type="monthrange"
                                range-separator="至"
                                start-placeholder="开始时间"
                                end-placeholder="结束时间">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item label="状态查询">
                            <el-select v-model="form" placeholder="请选择">
                                <el-option label="未到帐" value="1"></el-option>
                                <el-option label="已到帐" value="2"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="编号查询">
                            <el-input v-model="form.id" placeholder="请输入订单号"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-button @click="getAllRecord" type="primary">查询</el-button>
                        </el-form-item>
                    </el-row>
                </el-form>
            </div>

            <el-table :data="withdrawals" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="header-center" label="订单编号" prop="id"></el-table-column>
                <el-table-column align="header-center" label="卡号/收款人/银行名/支行">
                    <template slot-scope="scope">
                        {{ scope.row.extra.account_name }} / {{ scope.row.extra.account_number}} / {{ scope.row.extra.bank_code }} / {{ scope.row.extra.branch }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="代付金额" prop="amount"></el-table-column>
                <el-table-column align="header-center" label="代付佣金" prop="merchant_fee"></el-table-column>
                <el-table-column align="header-center" label="订单类型"></el-table-column>
                <el-table-column align="header-center" label="订单状态" prop="status">
                    <template slot-scope="scope" >
                        <el-tag type="info" v-if="scope.row.status==0">支付中</el-tag>
                        <el-tag type="warning" v-else-if="scope.row.status==1">已审核</el-tag>
                        <el-tag type="success" v-else-if="scope.row.status==2">充值成功</el-tag>
                        <el-tag type="danger" v-else>充值失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="申请时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="完成时间" prop="done_at"></el-table-column>
                <el-table-column align="header-center" label="使用账户名/卡号">
                    <template slot-scope="scope">
                        {{ scope.row.extra.transfer_account_name }} / {{ scope.row.extra.transfer_account_number }}
                    </template>
                </el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="success" @click="handleReceipted(scope.row.id)" v-if="scope.row.status==0" size="small">已转账</el-button>
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
import { getAllRecord,postChanagestatus } from '@/api/withdrawal'
import { mapGetters } from 'vuex'

export default {
    name: "WithdrawalIndex",
    data(){
        return {
            withdrawals: [],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
            loading:false,
            form:{

            },
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

            let data = this.listQuery;
            let result = await getAllRecord(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.withdrawals = result.data.data.withdrawals;
            }else{
                this.$message.error(result.data.msg);
            }
            this.loading =  false;
        },
        handleReceipted( id ){
            this.$confirm('是否已确定付款?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(async () => {
                let result = await postChanagestatus({
                    id:id,
                    status:true,
                });
                let type = 'danger';
                if(  result.data.code == 1 ){
                    type = 'success';
                }
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
</style>
