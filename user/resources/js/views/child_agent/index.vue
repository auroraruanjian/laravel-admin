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
                <el-button style="float: right;" type="primary" @click="handleAddAgent" v-permission="'merchant/create'" size="small">新增下级</el-button>
            </div>

            <el-table :data="agent_list" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="用户名" prop="username"></el-table-column>
                <el-table-column align="header-center" label="用户账号" prop="nickname"></el-table-column>
                <el-table-column align="header-center" label="转卡费率(商家)" prop="balance"></el-table-column>
                <el-table-column align="header-center" label="微信费率(商家)" prop="balance"></el-table-column>
                <el-table-column align="header-center" label="支付宝转卡费率(商家)" prop="balance"></el-table-column>
                <el-table-column align="header-center" label="支付宝扫码费率(商家)" prop="balance"  width="220"></el-table-column>
                <el-table-column align="header-center" label="今日 卡转卡/宝转卡" prop="balance" width="150"></el-table-column>
                <el-table-column align="header-center" label="是否可用" prop="status" width="150">
                    <template slot-scope="scope" >
                        <el-tag type="success" v-if="scope.row.status==1">可用</el-tag>
                        <el-tag type="danger" v-else>不可用</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="可用余额" prop="balance"></el-table-column>
                <el-table-column align="header-center" label="谷歌秘钥" prop="google_key"></el-table-column>
                <el-table-column align="header-center" label="上级代理账号" prop="parent_username"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="warning" size="small" @click="handleEdit(scope)">修改</el-button>
                        <el-button type="primary" size="small" @click="handleEdit(scope)">修改费率</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllAgent" />
        </div>

        <el-dialog :visible.sync="dialogVisible" :title="dialogType==='edit'?'编辑商户':'新增商户'">
            <el-form :model="Agent" label-width="22%" label-position="right">
                <el-form-item label="姓名" class="is-required">
                    <el-input v-model="Agent.nickname" placeholder="请输入4~10位字符，以字母开头" />
                </el-form-item>
                <el-form-item label="账号" class="is-required">
                    <el-input v-model="Agent.username" placeholder="请输入4~10位字符，以字母开头" />
                </el-form-item>
                <el-form-item label="登录密码" class="is-required">
                    <el-input v-model="Agent.password" placeholder="请输入6~11位英文或数字且符合0~9或a~z字符" type="password" />
                </el-form-item>
                <el-form-item label="确认密码" class="is-required">
                    <el-input v-model="Agent.repassword" placeholder="请再次输入登录密码" type="password" />
                </el-form-item>
                <el-form-item label="转卡费率">
                    <el-input v-model="Agent.balance" placeholder="请输入支付宝扫码费率 %" />
                </el-form-item>
                <el-form-item label="微信费率">
                    <el-input v-model="Agent.balance" placeholder="请输入支付宝扫码费率 %" />
                </el-form-item>
                <el-form-item label="支付宝转卡费率">
                    <el-input v-model="Agent.balance" placeholder="请输入支付宝扫码费率 %" />
                </el-form-item>
                <el-form-item label="支付宝扫码费率">
                    <el-input v-model="Agent.balance" placeholder="请输入支付宝转卡费率 %" />
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
import { getAllAgent,editAgent,addAgent,getAgent,deleteAgent } from '@/api/child_agent'
import { mapGetters } from 'vuex'


const defaultAgent = {
    id:'',
    username: '',
    nickname: '',
    password:'',
    repassword:'',
    status:true,
};

export default {
    name: "AgentIndex",
    data(){
        return {
            Agent: Object.assign({}, defaultAgent),
            agent_list: [],
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
        this.getAllAgent();
    },
    methods:{
        async getAllAgent(){
            this.loading =  true;

            let data = this.listQuery;
            data.parent_id = this.parent_id;
            let result = await getAllAgent(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.agent_list = result.data.data.agent_list;
            }else{
                this.$message.error(result.data.message);
            }
            this.loading =  false;
        },
        search(){

        },
        handleAddAgent(){
            this.Agent = Object.assign({}, defaultAgent)
            this.dialogType = 'new'
            this.dialogVisible = true
        },
        async handleEdit( scope ){
            this.loading =  true;
            let current_Agent = await getAgent(scope.row.id);
            this.Agent = current_Agent.data.data;
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
                response = await editAgent(this.Agent)
            }else{
                response = await addAgent(this.Agent)
            }

            if( response.data.code == 1 ){
                type = 'success';
                message = `
                    <div>Agent name: ${this.Agent.title}</div>
                  `;
            }else{
                message = response.data.msg;
            }

            this.dialogVisible = false

            this.getAllAgent();

            this.$notify({
                title: response.data.msg,
                dangerouslyUseHTMLString: true,
                message: message,
                type: type
            })
        },
    },
    watch: {
        parent_id(){
            this.getAllAgent();
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
