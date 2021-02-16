<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-button type="primary" @click="handleAddPaymentMethod" size="small">添加银行卡</el-button>
            </div>

            <el-table :data="payment_method_list" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="持卡人姓名" prop="account_name"></el-table-column>
                <el-table-column align="header-center" label="银行卡号" prop="account_number"></el-table-column>
                <el-table-column align="header-center" label="银行名称" prop="bank_name"></el-table-column>
                <el-table-column align="header-center" label="开户行" prop="branch"></el-table-column>
                <el-table-column align="header-center" label="是否可用" prop="status">
                    <template slot-scope="scope">
                        <el-tag type="success" v-if="scope.row.status==1">可用</el-tag>
                        <el-tag type="danger"  v-if="scope.row.status==0">不可用</el-tag>
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
                        <el-tag type="success" v-if="scope.row.is_open==1">开启</el-tag>
                        <el-tag type="danger"  v-if="scope.row.is_open==0">关闭</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="每日收款限额" prop="limit_amount"></el-table-column>
                <el-table-column align="header-center" label="今日总收款" prop="today_deposit"></el-table-column>
                <el-table-column align="header-center" label="添加时间" prop="created_at"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="primary" size="small" @click="handleEdit(scope)">修改限额</el-button>
                        <el-button type="success" size="small" @click="handleEdit(scope)">开启收款</el-button>
                        <el-button type="danger" size="small" @click="handleEdit(scope)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllPaymentMethod" />
        </div>

        <el-dialog :visible.sync="dialogVisible" :title="dialogType==='edit'?'编辑银行卡':'新增银行卡'">
            <el-form :model="payment_method" label-width="15%" label-position="right">
                <el-form-item label="账(卡)号" class="is-required">
                    <el-input v-model="payment_method.account_number" placeholder="请输入银行卡卡号" />
                </el-form-item>
                <el-form-item label="银行名称">
                    <el-select v-model="payment_method.bank_id" placeholder="请选择">
                        <el-option
                            v-for="item in banks"
                            :key="item.id"
                            :label="item.id"
                            :value="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="持卡人姓名">
                    <el-input v-model="payment_method.account_name" placeholder="请输入银行卡持有人姓名" />
                </el-form-item>
                <el-form-item label="开户省份" class="is-required">
                    <el-input v-model="payment_method.province" placeholder="请选择开户省份"/>
                </el-form-item>
                <el-form-item label="开户网点" class="is-required">
                    <el-input v-model="payment_method.branch" placeholder="请输入开户网点"/>
                </el-form-item>
                <el-form-item label="每日限额" class="is-required">
                    <el-input v-model="payment_method.limit_amount" placeholder="每日限额"/>
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
import { getAllMethod,editMethod,addMethod,getMethod,deleteMethod } from '@/api/payment_method'
import { mapGetters } from 'vuex'


const defaultMethod = {
    id:'',
    account_number:'',
    account_name:'',
    bank_id:'',
    province:'',
    branch:'',
    limit_amount:'',
};

export default {
    name: "PaymentMethodIndex",
    data(){
        return {
            payment_method: Object.assign({}, defaultMethod),
            payment_method_list: [],
            banks:[],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
            dialogVisible: false,
            dialogType: 'new',
            loading:false,
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
                this.payment_method_list = result.data.data.payment_method_list;
                this.banks = result.data.data.banks;
            }else{
                this.$message.error(result.data.message);
            }
            this.loading =  false;
        },
        handleAddPaymentMethod(){
            this.payment_method = Object.assign({}, defaultMethod)
            this.dialogType = 'new'
            this.dialogVisible = true
        },
        async handleEdit( scope ){
            this.loading =  true;
            let current_method = await getMethod(scope.row.id);
            this.payment_method = current_method.data.data;
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
                response = await editMethod(this.payment_method)
            }else{
                response = await addMethod(this.payment_method)
            }

            if( response.data.code == 1 ){
                type = 'success';
                message = `
                            <div>PaymentMethod name: ${this.payment_method.title}</div>
                          `;
            }else{
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
