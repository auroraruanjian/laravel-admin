<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-button type="primary" @click="handleAddPaymentMethod" size="small">添加银行卡</el-button>
            </div>

            <el-table :data="user_bank_list" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column type="index" width="50"></el-table-column>
                <el-table-column align="header-center" label="持卡人姓名" prop="account_name"></el-table-column>
                <el-table-column align="header-center" label="银行卡号" prop="account_number"></el-table-column>
                <el-table-column align="header-center" label="银行名称" prop="bank_name"></el-table-column>
                <el-table-column align="header-center" label="开户行" prop="branch"></el-table-column>
                <el-table-column align="header-center" label="是否可用" prop="status">
                    <template slot-scope="scope">
                        <el-tag type="success" style="cursor: pointer" v-if="scope.row.status==1"  @click="handleChangeStatus(scope.row.id,0)">可用</el-tag>
                        <el-tag type="danger"  style="cursor: pointer" v-if="scope.row.status==0"  @click="handleChangeStatus(scope.row.id,1)">不可用</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="是否删除" prop="is_delete" >
                    <template slot-scope="scope">
                        <el-tag type="success" v-if="scope.row.is_delete==1">未删除</el-tag>
                        <el-tag type="danger"  v-if="scope.row.is_delete==0">删除</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="是否开启收款" prop="is_open">
                    <template slot-scope="scope">
                        <el-tag type="success" style="cursor: pointer" v-if="scope.row.is_open==1" @click="handleChangeIsOpen(scope.row.id,0)">开启</el-tag>
                        <el-tag type="danger"  style="cursor: pointer" v-if="scope.row.is_open==0" @click="handleChangeIsOpen(scope.row.id,1)">关闭</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="每日收款限额" prop="limit_amount"></el-table-column>
                <el-table-column align="header-center" label="今日总收款" prop="today_deposit"></el-table-column>
                <el-table-column align="header-center" label="添加时间" prop="created_at"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="primary" size="small" @click="handleChantLimitAmount(scope.row.id)">修改限额</el-button>
<!--                        <el-button type="danger" size="small" @click="handleEdit(scope)">删除</el-button>-->
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllPaymentMethod" />
        </div>

        <el-dialog :visible.sync="dialogVisible" :title="dialogType==='edit'?'编辑银行卡':'新增银行卡'">
            <el-form :model="payment_method" ref="payment_method" label-width="15%" label-position="right" :rules="userBank.rules">
                <el-form-item label="持卡人姓名" prop="account_name" >
                    <el-input v-model="payment_method.account_name"
                              placeholder="请输入银行卡持有人姓名" />
                </el-form-item>
                <el-form-item label="账(卡)号" class="is-required" prop="account_number" >
                    <el-input v-model="payment_method.account_number" placeholder="请输入银行卡卡号" />
                </el-form-item>
                <el-form-item label="开户银行" prop="bank_id">
                    <el-select placeholder="请选择开户银行"
                               v-model="payment_method.bank_id"
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
                        v-model="payment_method.province"
                        :options="userBank.province"
                        @active-item-change="handleGetCity"
                        :props="userBank.props"
                        style="width: 300px"
                    >
                    </el-cascader>
                </el-form-item>
                <el-form-item label="开户网点" class="is-required" prop="branch">
                    <el-input v-model="payment_method.branch"
                              placeholder="请输入开户网点"/>
                </el-form-item>
                <el-form-item label="每日限额" class="is-required" prop="limit_amount">
                    <el-input v-model="payment_method.limit_amount"
                              placeholder="每日限额"/>
                </el-form-item>
                <el-form-item label="资金密码" prop="security_password">
                    <el-input
                        type="password"
                        v-model="payment_method.security_password"
                        auto-complete="new-password"
                    ></el-input>
                </el-form-item>
            </el-form>
            <div style="text-align:right;">
                <el-button type="danger" @click="dialogVisible=false">取消</el-button>
                <el-button type="primary" @click="confirm">新增</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import permission from '@/directive/permission/index.js' // 权限判断指令
import Pagination from '@/components/Pagination' // Secondary package based on el-pagination
import { getAllMethod,getCreate,addMethod,putIsOpen,putAvailable,putChantLimitAmount } from '@/api/user_banks'
import { mapGetters } from 'vuex'


const defaultMethod = {
    id:'',
    account_number:'',
    account_name:'',
    bank_id:'',
    province:'',
    branch:'',
    limit_amount:'',
    security_password:'',
};

export default {
    name: "UserBanksIndex",
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
            payment_method: Object.assign({}, defaultMethod),
            user_bank_list: [],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
            dialogVisible: false,
            dialogType: 'new',
            loading:false,
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
        this.getAllPaymentMethod();
    },
    methods:{
        async getAllPaymentMethod(){
            this.loading =  true;

            let data = this.listQuery;
            let result = await getAllMethod(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.user_bank_list = result.data.data.user_bank_list;
                this.isSetSecurityPwd = result.data.data.security_password;
            }else{
                this.$message.error(result.data.message);
            }
            this.loading =  false;
        },
        handleAddPaymentMethod(){
            this.payment_method = JSON.parse(JSON.stringify(defaultMethod));
            this.dialogType = 'new'

            /*
            if (!this.isSetSecurityPwd) {
                this.$alert('您还没设置资金密码，无法绑定银行卡!', '提示', {
                    type: 'error',
                    confirmButtonText: '立即设置',
                    callback: action => {
                        this.$router.push('/user/index');
                    }
                });
                return false;
            }
            */
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
        /*
        async handleEdit( scope ){
            this.loading =  true;
            let current_method = await getMethod(scope.row.id);
            this.payment_method = current_method.data.data;
            this.dialogType = 'edit'
            this.dialogVisible = true
            this.loading =  false;
        },
        */
        handleChantLimitAmount( id ){
            this.$prompt('请输入限额', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                inputPattern: /^[0-9]{1,10}?/,
                inputErrorMessage: '金额格式不正确'
            }).then(async({ value }) => {
                await putChantLimitAmount({
                    id:id,
                    limit_amount:value
                }).then(response => {
                    this.$message({
                        type: (response.data.code==1)?'success':'error',
                        message: response.data.msg
                    });

                    this.getAllPaymentMethod();
                });
            }).catch(() => {

            });
        },
        confirm(){
            this.$refs['payment_method'].validate( async (valid) => {
                if (valid) {
                    const isEdit = this.dialogType === 'edit'

                    let type = 'error';
                    let message = '';

                    let response;

                    if (isEdit) {
                        //response = await editMethod(this.payment_method)
                    } else {
                        response = await addMethod(this.payment_method)
                    }

                    if (response.data.code == 1) {
                        type = 'success';
                        message = `
                            <div>PaymentMethod name: ${this.payment_method.title}</div>
                          `;
                    } else {
                        message = response.data.msg;
                    }

                    this.dialogVisible = false

                    this.getAllPaymentMethod();

                    this.$notify({
                        title: response.data.msg,
                        dangerouslyUseHTMLString: true,
                        message: message,
                        type: type
                    })
                } else {
                    return false;
                }
            });

        },
        handleChangeIsOpen( id , is_open ){
            //
            let tip_text = is_open==1 ? '开启' : '关闭';
            this.$confirm('确定'+tip_text+'收款？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then( async () => {
                let res = await putIsOpen({id,is_open});

                let type = 'danger';
                if( res.data.code == 1 ){
                    type = 'success';
                }

                this.getAllPaymentMethod();

                this.$message({
                    type: type,
                    message: res.data.msg
                });
            })
        },
        handleChangeStatus( id , status ){
            let tip_text = status==1 ? '开启' : '关闭';
            this.$confirm('确定'+tip_text+'此收款方式？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then( async () => {
                let res = await putAvailable({id,status});

                let type = 'danger';
                if( res.data.code == 1 ){
                    type = 'success';
                }

                this.getAllPaymentMethod();

                this.$message({
                    type: type,
                    message: res.data.msg
                });
            })
        }
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
