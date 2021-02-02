<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-form :inline="true" :model="form" class="demo-form-inline" size="small" style="display: inline;">
                    <el-form-item label="用户名查询">
                        <el-input v-model="form.user" placeholder="请输入用户名"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="warning" @click="search">查询</el-button>
                    </el-form-item>
                </el-form>
                <el-form :inline="true" :model="form" class="demo-form-inline" size="small" style="display: inline;margin-left:200px;">
                    <el-form-item label="是否可用" size="small">
                        <el-select v-model="choose_area" placeholder="请选择活动区域">
                            <el-option label="全部" value="0"></el-option>
                            <el-option label="可用" value="1"></el-option>
                            <el-option label="不可用" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
                <el-button style="float: right;" type="primary" @click="handleAddMerchant" v-permission="'merchant/create'" size="small">新增下级</el-button>
            </div>

            <el-table :data="merchant_list" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="账户" prop="account"></el-table-column>
                <el-table-column align="header-center" label="商家姓名" prop="nickname"></el-table-column>
                <el-table-column align="header-center" label="余额" prop="balance"></el-table-column>
                <el-table-column align="header-center" label="通道" prop="balance"></el-table-column>
                <el-table-column align="header-center" label="费率" prop="balance"></el-table-column>
                <el-table-column align="header-center" label="今日收款" prop="balance"  width="220"></el-table-column>
                <el-table-column align="header-center" label="今日收款分类(已扣手续费)" prop="last_ip" width="150"></el-table-column>
                <el-table-column align="header-center" label="今日收款分类(未扣手续费)" prop="last_ip" width="150"></el-table-column>
                <el-table-column align="header-center" label="历史总收款" prop="last_time"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="primary" size="small" @click="handleEdit(scope)">修改</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllMerchant" />
        </div>

        <el-dialog :visible.sync="dialogVisible" :title="dialogType==='edit'?'编辑商户':'新增商户'">
            <el-form :model="Merchant" label-width="22%" label-position="right">
                <el-form-item label="账号" class="is-required">
                    <el-input v-model="Merchant.account" placeholder="请输入4~10位字符，以字母开头" />
                </el-form-item>
                <el-form-item label="商户姓名">
                    <el-input v-model="Merchant.nickname" placeholder="请输入您的真实姓名" />
                </el-form-item>
                <el-form-item label="管理员手机号">
                    <el-input v-model="Merchant.phone" placeholder="请输入您的手机号码" />
                </el-form-item>
                <el-form-item label="管理员登录密码" class="is-required">
                    <el-input v-model="Merchant.password" placeholder="请输入6~11位英文或数字且符合0~9或a~z字符" type="password" />
                </el-form-item>
                <el-form-item label="管理员确认密码" class="is-required">
                    <el-input v-model="Merchant.repassword" placeholder="请再次输入登录密码" type="password" />
                </el-form-item>
                <el-form-item label="管理员资金密码" class="is-required">
                    <el-input v-model="Merchant.pay_password" placeholder="请输入6位纯数字" type="password" />
                </el-form-item>
                <el-form-item label="管理员确认资金密码" class="is-required">
                    <el-input v-model="Merchant.repay_password" placeholder="请再次输入资金密码" type="password" />
                </el-form-item>
                <el-form-item label="开通渠道">
                    <el-checkbox-group  v-model="Merchant.payment_method" >
                        <el-checkbox v-for="(item,key) in payment_method" :key="key" :label="item.id" @change="checkPaymentMethod(item)">{{ item.name }}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item v-for="(item,key) in payment_method" :label="item.name+'费率'" :key="key">
                    <el-input v-model="Merchant.payment_method_fee[item.id]" :placeholder="'请输入'+item.name+'费率'" :disabled="!Merchant.payment_method.includes(item.id)">
                        <template slot="append"> %</template>
                    </el-input>
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
import { getAllMerchant,editMerchant,addMerchant,getMerchant,deleteMerchant } from '@/api/merchant'
import { mapGetters } from 'vuex'


const defaultMerchant = {
    id:'',
    account:'',
    username: '',
    nickname: '',
    phone: '',
    password:'',
    pay_password: '',
    status:true,
    payment_method:[],
    payment_method_fee:{},
};

export default {
    name: "MerchantIndex",
    data(){
        return {
            Merchant: Object.assign({}, defaultMerchant),
            merchant_list: [],
            payment_method:[],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
            dialogVisible: false,
            dialogType: 'new',
            loading:false,
            form:{

            },
            choose_area:"0",
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
        this.getAllMerchant();
    },
    methods:{
        async getAllMerchant(){
            this.loading =  true;

            let data = this.listQuery;
            data.parent_id = this.parent_id;
            let result = await getAllMerchant(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.merchant_list = result.data.data.merchant_list;
                this.payment_method = result.data.data.payment_method;
            }else{
                this.$message.error(result.data.message);
            }
            this.loading =  false;
        },
        search(){

        },
        handleAddMerchant(){
            this.Merchant = Object.assign({}, defaultMerchant)

            this.dialogType = 'new'
            this.dialogVisible = true
        },
        async handleEdit( scope ){
            this.loading =  true;
            let current_Merchant = await getMerchant(scope.row.id);
            this.Merchant = current_Merchant.data.data;

            this.dialogType = 'edit'
            this.dialogVisible = true
            this.loading =  false;
        },
        async confirm(){
            const isEdit = this.dialogType === 'edit'

            let type = 'error';
            let message = '';

            let response;

            if (isEdit) {
                response = await editMerchant(this.Merchant)
            }else{
                response = await addMerchant(this.Merchant)
            }

            if( response.data.code == 1 ){
                type = 'success';
                message = `
                            <div>Merchant name: ${this.Merchant.title}</div>
                          `;
            }else{
                message = response.data.msg;
            }

            this.dialogVisible = false

            this.getAllMerchant();

            this.$notify({
                title: response.data.msg,
                dangerouslyUseHTMLString: true,
                message: message,
                type: type
            })
        },
        checkPaymentMethod( item )
        {
            console.log(item);
            return false;
        }
    },
    watch: {
        parent_id(){
            this.getAllMerchant();
        }
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
