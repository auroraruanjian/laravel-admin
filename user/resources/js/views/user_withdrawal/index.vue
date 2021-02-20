<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-row :gutter="20">
                    <el-col :span="18">
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
                            <el-form-item label="状态类型">
                                <el-select v-model="form.region" placeholder="状态类型">
                                    <el-option label="区域一" value="shanghai"></el-option>
                                    <el-option label="区域二" value="beijing"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="编号查询">
                                <el-input v-model="form.id" placeholder="请输入编号"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" @click="onSubmit">查询</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                    <el-col :span="6" style="text-align: right;">
                        <el-button type="success" size="small" @click="withdrawal_apply">提现申请</el-button>
                        <el-button type="primary" size="small" plain>刷新</el-button>
                    </el-col>
                </el-row>
            </div>

            <el-table :data="user_withdrawals" style="width: 100%;margin-top:30px;" border >
                <el-table-column align="center" label="订单编号" prop="id"></el-table-column>
                <el-table-column align="header-center" label="提现金额" prop="amount"></el-table-column>
                <el-table-column align="header-center" label="手续费" prop="fee"></el-table-column>
                <el-table-column align="header-center" label="取款人名称" prop="extra.account_name"></el-table-column>
                <el-table-column align="header-center" label="银行名称" prop="extra.bank_name"></el-table-column>
                <el-table-column align="header-center" label="银行卡卡号" prop="extra.account_number"></el-table-column>
                <el-table-column align="header-center" label="银行卡开户地点" prop="extra.branch"></el-table-column>
                <el-table-column align="header-center" label="提现时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="代付凭证" prop=""></el-table-column>
                <el-table-column align="header-center" label="提现状态" prop="status"></el-table-column>
                <el-table-column align="header-center" label="备注" prop="remark"></el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllRecord" />
        </div>

        <el-dialog :visible.sync="dialogVisible" title="提现申请">
            <el-form :model="withdrawal" label-width="15%" label-position="right">
                <el-form-item label="提现金额">
                    <el-input v-model="withdrawal.amount" placeholder="商户" />
                </el-form-item>
                <el-form-item label="可提现金额">
                    <el-input v-model="availableAmount" placeholder="散户名" disabled/>
                </el-form-item>
                <el-form-item label="银行名称">
                    <el-select v-model="withdrawal.bank_id" placeholder="请选择">
                        <el-option
                            v-for="item in banks"
                            :key="item.id"
                            :label="item.id"
                            :value="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="银行卡卡号">
                    <el-input v-model="withdrawal.account_number" placeholder="请输入银行卡卡号" />
                </el-form-item>
                <el-form-item label="持有人姓名">
                    <el-input v-model="withdrawal.account_name" placeholder="请输入银行卡持有人姓名" />
                </el-form-item>
                <el-form-item label="开户省份">
                    <el-input v-model="withdrawal.province" placeholder="请选择开户省份"/>
                </el-form-item>
                <el-form-item label="开户地址">
                    <el-input v-model="withdrawal.branch" placeholder="请输入开户网点"/>
                </el-form-item>
                <el-form-item label="资金密码">
                    <el-input v-model="withdrawal.pay_password" placeholder="密码" type="password" />
                </el-form-item>
            </el-form>
            <div style="text-align:right;">
                <el-button type="danger" @click="dialogVisible=false">取消</el-button>
                <el-button type="primary" @click="confirm">申请</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import permission from '@/directive/permission/index.js' // 权限判断指令
import Pagination from '@/components/Pagination' // Secondary package based on el-pagination
import { getAllRecord,applyWithdrawal } from '@/api/user_withdrawal'
import { mapGetters } from 'vuex'


const defaultWithdrawal = {
    amount:'',
    bank_id:'',
    account_number:'',
    account_name:'',
    province:'',
    branch:'',
    pay_password:'',
};

export default {
    name: "ChildAgentIndex",
    data(){
        return {
            withdrawal: Object.assign({}, defaultWithdrawal),
            user_withdrawals: [],
            banks:[],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
            dialogVisible: false,
            loading:false,
            form:{

            },
            availableAmount:100,// TODO: 余额
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

            let data = Object.assign(this.listQuery, this.form);
            let result = await getAllRecord(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.user_withdrawals = result.data.data.user_withdrawals;
            }else{
                this.$message.error(result.data.msg);
            }
            this.loading =  false;
        },
        onSubmit(){
            this.getAllRecord();
        },
        async confirm(){
            this.loading =  true;

            let result = await applyWithdrawal(this.withdrawal);

            let type;
            if(  result.data.code == 1 ){
                type = 'success';
                this.dialogVisible = false;
                this.getAllRecord();
            }else{
                type = 'danger';
            }
            this.$message({
                type: type,
                message: result.data.msg
            });

            this.loading =  false;
        },
        withdrawal_apply(){
            this.withdrawal = Object.assign({}, defaultWithdrawal),
            this.dialogVisible = true
        },
    },
    watch: {
    }
}
</script>

<style scoped>

</style>
