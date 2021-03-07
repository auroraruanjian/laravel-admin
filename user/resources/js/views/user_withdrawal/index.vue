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
                <el-table-column align="header-center" label="银行名称" prop="bank_name"></el-table-column>
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
                    <el-input v-model="withdrawal.amount" placeholder="金额" />
                </el-form-item>
                <el-form-item label="可提现金额">
                    <el-input v-model="availableAmount" placeholder="可提现金额" disabled/>
                </el-form-item>
                <el-form-item label="银行卡卡号">
                    <el-input v-model="withdrawal.account_number" placeholder="请输入银行卡卡号" />
                </el-form-item>
                <el-form-item label="持有人姓名">
                    <el-input v-model="withdrawal.account_name" placeholder="请输入银行卡持有人姓名" />
                </el-form-item>
                <el-form-item label="开户银行" prop="bank_id">
                    <el-select placeholder="请选择开户银行"
                               v-model="withdrawal.bank_id"
                               style="width: 300px">
                        <el-option
                            v-for="(option, key) in userBank.banks"
                            :key="key"
                            :label="option.name"
                            :value="option.id"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="省/市/县，区域" prop="branch" >
                    <el-cascader
                        placeholder="请选择省/市/县，区域"
                        v-model="withdrawal.province"
                        :options="userBank.province"
                        @active-item-change="handleGetCity"
                        :props="userBank.props"
                        style="width: 300px"
                    >
                    </el-cascader>
                </el-form-item>
                <el-form-item label="开户地址">
                    <el-input v-model="withdrawal.branch" placeholder="请输入开户网点"/>
                </el-form-item>
                <el-form-item label="资金密码">
                    <el-input v-model="withdrawal.security_password" placeholder="密码" type="password" />
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
import { getCreate } from '@/api/user_banks'
import { mapGetters } from 'vuex'


const defaultWithdrawal = {
    amount:'',
    bank_id:'',
    account_number:'',
    account_name:'',
    province:'',
    branch:'',
    security_password:'',
};

export default {
    name: "ChildAgentIndex",
    data(){
        let checkBankNo = (rule, value, callback) => {
            if (/^\d{16}|\d{19}$/.test(value) === false) {
                callback(new Error('银行卡号验证失败!'));
            } else {
                callback();
            }
        };
        let checkReBankNo = (rule, value, callback) => {
            if (value === '') {
                callback(new Error('请再次输入银行卡号'));
            } else if (value !== this.addUserBank.form.account_no) {
                callback(new Error('两次输入银行卡号不一致!'));
            } else {
                callback();
            }
        };
        let checkBankUser = (rule, value, callback) => {
            if (/^[\u4e00-\u9fa5]+(·[\u4e00-\u9fa5]+)*$/.test(value) === false) {
                callback(new Error('请输入持卡人姓名'));
            } else {
                callback();
            }
        };


        return {
            withdrawal: Object.assign({}, defaultWithdrawal),
            user_withdrawals: [],
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
            userBank:{
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'cities'
                },
                banks: [],
                province: [],
                rules: {
                    account_number: [
                        {
                            required: true,
                            message: '输入银行卡号！',
                            trigger: 'blur'
                        },
                        { validator: checkBankNo, trigger: 'blur' }
                    ],
                    account_name: [
                        {
                            required: true,
                            message: '输入卡户名！',
                            trigger: 'blur'
                        },
                        { validator: checkBankUser, trigger: 'blur' }
                    ],
                    bank_id:  [
                        {
                            type: 'number',
                            required: true,
                            message: '请选择开户行！',
                            trigger: 'change'
                        }
                    ],
                    province: [
                        {
                            type: 'array',
                            required: true,
                            message: '请选择卡户地区！',
                            trigger: 'change'
                        }
                    ],
                    branch: [
                        {
                            required: true,
                            message: '输入支行名称！',
                            trigger: 'blur'
                        }
                    ],
                    limit_amount: [
                        {
                            required: true,
                            message: '请输入限额！',
                            trigger: 'change'
                        }
                    ],
                    security_password: [
                        {
                            required: true,
                            message: '输入资金密码！',
                            trigger: 'blur'
                        },
                        {
                            min: 6,
                            max: 16,
                            message: '长度在 6 到 16 个字符',
                            trigger: 'blur'
                        }
                    ],
                },
            },
            isSetSecurityPwd: false,
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
                this.isSetSecurityPwd = result.data.data.hasSecurityPwd;
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
                type = 'error';
            }
            this.$message({
                type: type,
                message: result.data.msg
            });

            this.loading =  false;
        },
        withdrawal_apply(){
            this.withdrawal = Object.assign({}, defaultWithdrawal);

            if (!this.isSetSecurityPwd) {
                this.$alert('您还没设置资金密码，无法绑定银行卡!', '提示', {
                    type: 'error',
                    confirmButtonText: '立即设置',
                    callback: action => {
                        //this.$router.push('/user/index');
                    }
                });
                return false;
            }
            this.dialogVisible = true

            let banks_provice = false;
            try {
                banks_provice = JSON.parse(window.sessionStorage.getItem('banks_provice'));
            } catch (e) {
                banks_provice = false;
            }
            if (banks_provice) {
                this.dialogVisible = true
                this.userBank.banks = banks_provice.banks;
                this.userBank.province = banks_provice.province;
            } else {
                getCreate(0, 0)
                    .then(response => {
                        this.loading = false;
                        if (response.data.code == 1) {
                            this.dialogVisible = true
                            this.userBank.banks = response.data.data.banks;
                            this.userBank.province = response.data.data.province;
                            window.sessionStorage.setItem('banks_provice', JSON.stringify(response.data.data));
                        } else {
                            this.$message.error('获取数据失败！' + response.data.msg);
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        this.$message.error('获取数据失败！' + err.message);
                    });
            }
        },
        handleGetCity(val) {
            let ids = val.toString().split(',');
            let id = ids[ids.length - 1];
            let level = ids.length;
            getCreate('get_city', id)
                .then(response => {
                    this.loading = false;
                    if (level == 1) {
                        for (let i = 0; i < this.userBank.province.length; i++) {
                            if (this.userBank.province[i].id == id) {
                                this.userBank.province[i].cities = response.data.data;
                            }
                        }
                    } else if (level == 2) {
                        for (let j = 0; j < this.userBank.province.length; j++) {
                            if (this.userBank.province[j].id == ids[0]) {
                                for (let i = 0; i < this.userBank.province[j].cities.length; i++) {
                                    if (this.userBank.province[j].cities[i].id == id) {
                                        this.userBank.province[j].cities[i].cities = response.data.data;
                                    }
                                }
                            }
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                });
        },
    },
    watch: {
    }
}
</script>

<style scoped>

</style>
