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
                    <el-button type="primary" style="float:right" @click="handleAddAgent" size="small">新增代理</el-button>
                </el-form>
            </div>

            <el-table :data="agent_list" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="用户名" prop="username"></el-table-column>
                <el-table-column align="header-center" label="用户账号" prop="nickname"></el-table-column>
                <el-table-column align="header-center" label="代收费率(%)" width="200">
                    <template slot-scope="scope" >
                        <div v-for="(item,key) in scope.row.extra.rebates.deposit_rebates">{{item.payment_method_name}}:{{item.rate}}</div>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="代付手续费(元)" prop="extra.rebates.withdrawal_rebate.amount">
                </el-table-column>
                <el-table-column align="header-center" label="散户代收佣金(%)" prop="extra.rebates.user_deposit_rebate.rate">
                </el-table-column>
                <el-table-column align="header-center" label="散户代付佣金(元)" prop="extra.rebates.user_withdrawal_rebate.amount">
                </el-table-column>
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
            <el-form :model="agent_users" label-width="22%" label-position="right">
                <el-form-item label="姓名" class="is-required">
                    <el-input v-model="agent_users.nickname" placeholder="请输入4~10位字符，以字母开头" />
                </el-form-item>
                <el-form-item label="账号" class="is-required">
                    <el-input v-model="agent_users.username" placeholder="请输入4~10位字符，以字母开头" />
                </el-form-item>
                <el-form-item label="登录密码" class="is-required">
                    <el-input v-model="agent_users.password" placeholder="请输入6~11位英文或数字且符合0~9或a~z字符" type="password" />
                </el-form-item>
                <el-form-item label="确认密码" class="is-required">
                    <el-input v-model="agent_users.repassword" placeholder="请再次输入登录密码" type="password" />
                </el-form-item>

                <el-form-item label="商户费率">
                    <el-divider>商户代收手续费</el-divider>
                    <el-row v-for="(item,key) in agent_users.rebates.deposit_rebates" :key="key">
                        <el-col :span="6">
                            <el-checkbox v-model="item.status">{{item.name}}费率(%)</el-checkbox>
                        </el-col>
                        <el-col :span="16">
                            <el-slider
                                :min="item.min_rate"
                                :max="10"
                                :step="0.1"
                                :disabled="item.status!=true"
                                v-model="item.rate"
                                show-input>
                            </el-slider>
                        </el-col>
                    </el-row>
                    <el-divider>商户代付手续费</el-divider>
                    <el-row>
                        <el-col :span="6">
                            <el-checkbox v-model="agent_users.rebates.withdrawal_rebate.status">商户代付手续费(元)</el-checkbox>
                        </el-col>
                        <el-col :span="16">
                            <el-slider
                                :min="rebates_limit.withdrawal_rebate"
                                :max="10"
                                :step="0.1"
                                :disabled="!agent_users.rebates.withdrawal_rebate.status"
                                v-model="agent_users.rebates.withdrawal_rebate.amount"
                                show-input>
                            </el-slider>
                        </el-col>
                    </el-row>
                    <el-divider>散户代收佣金</el-divider>
                    <el-row>
                        <el-col :span="6">
                            <el-checkbox v-model="agent_users.rebates.user_deposit_rebate.status">散户代收佣金费率(%)</el-checkbox>
                        </el-col>
                        <el-col :span="16">
                            <el-slider
                                :step="0.1"
                                :min="0"
                                :max="rebates_limit.user_deposit_rebate"
                                :disabled="!agent_users.rebates.user_deposit_rebate.status"
                                v-model="agent_users.rebates.user_deposit_rebate.rate"
                                show-input>
                            </el-slider>
                        </el-col>
                    </el-row>
                    <el-divider>散户代付佣金</el-divider>
                    <el-row>
                        <el-col :span="6">
                            <el-checkbox v-model="agent_users.rebates.user_withdrawal_rebate.status">散户代付佣金(元)</el-checkbox>
                        </el-col>
                        <el-col :span="16">
                            <el-slider
                                :step="0.1"
                                :min="0"
                                :max="rebates_limit.user_withdrawal_rebate"
                                :disabled="!agent_users.rebates.user_withdrawal_rebate.status"
                                v-model="agent_users.rebates.user_withdrawal_rebate.amount"
                                show-input>
                            </el-slider>
                        </el-col>
                    </el-row>
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
    rebates:{
        deposit_rebates:{},
        withdrawal_rebate:{},
        user_deposit_rebate:{},
        user_withdrawal_rebate:{},
    },
};

export default {
    name: "AgentIndex",
    data(){
        return {
            agent_users: Object.assign({}, defaultAgent),
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
            payment_method:[],
            rebates_limit:{},
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
                this.payment_method = result.data.data.payment_method;
                this.rebates_limit = result.data.data.rebates_limit;
            }else{
                this.$message.error(result.data.message);
            }
            this.loading =  false;
        },
        search(){

        },
        handleAddAgent(){
            this.agent_users = Object.assign({}, defaultAgent)
            this.dialogType = 'new'

            this.agent_users.rebates.deposit_rebates = [];
            for(let i in this.payment_method){
                this.agent_users.rebates.deposit_rebates.push({
                    name:this.payment_method[i].name,
                    rate:0,
                    status:false,
                    id:this.payment_method[i].id,
                    min_rate:this.payment_method[i].min_rate,
                });
            }

            this.dialogVisible = true
        },
        async handleEdit( scope ){
            this.loading =  true;
            let current_users = await getAgent(scope.row.id);
            this.agent_users = JSON.parse(JSON.stringify(current_users.data.data));
            this.dialogType = 'edit'

            this.agent_users.status = this.agent_users.status == 1?true:false;

            this.agent_users.rebates.deposit_rebates = [];
            for(let i in this.payment_method){
                let rate = (typeof current_users.data.data.rebates.deposit_rebates[this.payment_method[i].id] != 'undefined')?current_users.data.data.rebates.deposit_rebates[this.payment_method[i].id].rate:0;
                let status = (typeof current_users.data.data.rebates.deposit_rebates[this.payment_method[i].id] != 'undefined')?current_users.data.data.rebates.deposit_rebates[this.payment_method[i].id].status:false;

                this.agent_users.rebates.deposit_rebates.push({
                    name:this.payment_method[i].name,
                    rate:rate,
                    status:status,
                    id:this.payment_method[i].id,
                    min_rate:this.payment_method[i].min_rate,
                });
            }

            if( current_users.data.data.rebates.withdrawal_rebate instanceof Array && current_users.data.data.rebates.withdrawal_rebate.length == 0 ){
                this.agent_users.rebates.withdrawal_rebate = {};
            }
            if( current_users.data.data.rebates.user_deposit_rebate instanceof Array && current_users.data.data.rebates.user_deposit_rebate.length == 0 ){
                this.agent_users.rebates.user_deposit_rebate = {};
            }
            if( current_users.data.data.rebates.user_withdrawal_rebate instanceof Array && current_users.data.data.rebates.user_withdrawal_rebate.length == 0 ){
                this.agent_users.rebates.user_withdrawal_rebate = {};
            }

            this.dialogVisible = true
            this.loading =  false;
        },
        async confirm(){
            const isEdit = this.dialogType === 'edit'

            let response;

            if (isEdit) {
                response = await editAgent(this.agent_users)
            }else{
                response = await addAgent(this.agent_users)
            }

            let type = 'error';
            if( response.data.code == 1 ){
                type = 'success';
                this.dialogVisible = false
                this.getAllAgent();
            }

            this.$message({
                message: response.data.msg,
                type: type
            });
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
