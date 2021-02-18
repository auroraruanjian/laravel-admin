<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-row :gutter="20">
                    <el-col :span="24">
                        <el-form :inline="true" :model="form" size="small">
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
                            <el-form-item label="渠道查询">
                                <el-select v-model="form" placeholder="请选择">
                                    <el-option label="转卡" value="1"></el-option>
                                    <el-option label="支付宝转卡" value="2"></el-option>
                                </el-select>
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
                                <el-button type="warning" @click="getAllRecord">查询</el-button>
                                <el-button type="success" @click="getAllRecord">导出</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                </el-row>
            </div>

            <el-table :data="deposits" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="header-center" label="平台编号" prop="id"></el-table-column>
                <el-table-column align="header-center" label="商户编号" prop="merchant_order_no"></el-table-column>
                <el-table-column align="header-center" label="付款人" prop="id"></el-table-column>
                <el-table-column align="header-center" label="收款渠道" prop="payment_channel_detail_id"></el-table-column>
                <el-table-column align="header-center" label="到帐金额" prop="real_amount"></el-table-column>
                <el-table-column align="header-center" label="标准金额" prop="amount"></el-table-column>
                <el-table-column align="header-center" label="结算金额" prop="amount"></el-table-column>
                <el-table-column align="header-center" label="商户/平台手续费">
                    <template slot-scope="scope" >
                        {{ scope.row.merchant_fee }} / {{ scope.row.third_fee }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="订单时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="订单状态" prop="status">
                    <template slot-scope="scope" >
                        <el-tag type="info" v-if="scope.row.status==0">支付中</el-tag>
                        <el-tag type="warning" v-else-if="scope.row.status==1">已审核</el-tag>
                        <el-tag type="success" v-else-if="scope.row.status==2">充值成功</el-tag>
                        <el-tag type="danger" v-else>充值失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="商户确认状态" prop="push_status">
                    <template slot-scope="scope" >
                        <el-tag type="info" v-if="scope.row.push_status==0">确认中</el-tag>
                        <el-tag type="warning" v-else-if="scope.row.push_status==88">推送成功</el-tag>
                        <el-tag type="danger" v-else>推送失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="商户确认时间" prop="push_at"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="success" v-if="scope.row.status==0" size="small">已收款</el-button>
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
import { getAllRecord } from '@/api/deposits'
import { mapGetters } from 'vuex'

export default {
    name: "DepositIndex",
    data(){
        return {
            deposits: [],
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
                this.deposits = result.data.data.deposits;
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
