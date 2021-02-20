<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-form :inline="true" :model="form" size="small">
                    <el-row :gutter="20">
                        <el-col :span="8">
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
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="状态查询">
                                <el-select v-model="form" placeholder="请选择">
                                    <el-option label="未到帐" value="1"></el-option>
                                    <el-option label="已到帐" value="2"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="编号查询">
                                <el-input v-model="form.id" placeholder="请输入订单号"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :span="16">
                            <el-form-item label="编号查询">
                                <el-input v-model="form.min_amount" style="width:40%" placeholder="请输入最小金额"></el-input>-
                                <el-input v-model="form.max_amount" style="width:40%" placeholder="请输入最大金额"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button @click="getAllRecord">查询</el-button>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item>
                                <el-button @click="getAllRecord">刷新</el-button>
                                <el-button type="success" @click="getAllRecord">导出</el-button>
                                <el-button type="warning" @click="getAllRecord">批量新增</el-button>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
            </div>

            <el-table :data="withdrawals" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="header-center" label="平台编号/商户编号" prop="id">
                    <template slot-scope="scope">
                        {{ scope.row.id }} / {{ scope.row.merchant_order_no }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="卡号/收款人/银行名/支行">
                    <template slot-scope="scope">
                        {{ scope.row.extra.account_name }} / {{ scope.row.extra.account_number}} / {{ scope.row.extra.bank_code }} / {{ scope.row.extra.branch }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="代付金额" prop="amount"></el-table-column>
                <el-table-column align="header-center" label="代付佣金" prop="merchant_fee"></el-table-column>
                <el-table-column align="header-center" label="订单类型">
                    <el-tag type="info">代付</el-tag>
                </el-table-column>
                <el-table-column align="header-center" label="订单状态" prop="status">
                    <template slot-scope="scope" >
                        <el-tag type="info" v-if="scope.row.status==0">代付中</el-tag>
                        <el-tag type="warning" v-else-if="scope.row.status==1">已审核</el-tag>
                        <el-tag type="success" v-else-if="scope.row.status==2">代付成功</el-tag>
                        <el-tag type="danger" v-else>代付失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="备注" prop="remark"></el-table-column>
                <el-table-column align="header-center" label="处理时间" prop="done_at"></el-table-column>
                <el-table-column align="header-center" label="商户名" prop="merchant_id"></el-table-column>
                <el-table-column align="header-center" label="处理人" prop="cash_admin_id"></el-table-column>
                <el-table-column align="header-center" label="使用卡号">
                    <template slot-scope="scope">
                        {{ scope.row.extra.transfer_account }}
                    </template>
                </el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="success" v-if="scope.row.status==0" size="small">推送</el-button>
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
import { getAllRecord } from '@/api/withdrawal'
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
                id : '',
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
        /*
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
         */
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
