<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-button type="primary" @click="handleAddAgentUser" v-permission="'agentUsers/create'" size="small">创建代理</el-button>
            </div>

            <el-table :data="agent_users_list" style="width: 100%;margin-top:30px;" border >
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="商户名称" prop="account"></el-table-column>
                <el-table-column align="header-center" label="代理名" prop="username"></el-table-column>
<!--                <el-table-column align="header-center" label="代理组" prop="user_group_name"></el-table-column>-->
                <el-table-column align="header-center" label="昵称" prop="nickname"></el-table-column>
                <el-table-column align="header-center" label="上次登录IP" prop="last_ip"></el-table-column>
                <el-table-column align="header-center" label="上次登录时间" prop="last_time"></el-table-column>
                <el-table-column align="center" label="Operations">
                    <template slot-scope="scope" >
                        <el-button type="primary" size="small" @click="handleEdit(scope)">Edit</el-button>
                        <el-button type="danger" size="small" @click="handleDelete(scope)">Delete</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllAgentUsers" />
        </div>

        <el-dialog :visible.sync="dialogVisible" :title="dialogType==='edit'?'编辑代理用户':'新增代理用户'">
            <el-form :model="agent_users" label-width="18%" label-position="right">
                <el-form-item label="代理名">
                    <el-input v-model="agent_users.username" placeholder="代理名"  :disabled="dialogType==='edit'"/>
                </el-form-item>
                <el-form-item label="昵称">
                    <el-input v-model="agent_users.nickname" placeholder="昵称" />
                </el-form-item>
                <el-form-item label="密码">
                    <el-input v-model="agent_users.password" placeholder="密码" type="password" />
                </el-form-item>
                <el-form-item label="是否启用">
                    <el-switch
                            v-model="agent_users.status"
                            active-color="#13ce66"
                            inactive-color="#ddd">
                    </el-switch>
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
                <el-button type="primary" @click="confirm">提交</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import permission from '@/directive/permission/index.js' // 权限判断指令
    import Pagination from '@/components/Pagination' // Secondary package based on el-pagination
    import { getAllAgentUsers,editAgentUsers,addAgentUsers,getAgentUsers,deleteAgentUsers } from '@/api/agent_users'
    import { mapGetters } from 'vuex'


    const defaultAgentUsers = {
        id:'',
        username: '',
        //user_group_id: '1',
        nickname: '',
        password:'',
        status:true,
        rebates:{
            deposit_rebates:{},
            withdrawal_rebate:{},
            user_deposit_rebate:{},
            user_withdrawal_rebate:{},
        },
    };

    export default {
        name: "AgentUsersIndex",
        data(){
            return {
                agent_users: Object.assign({}, defaultAgentUsers),
                agent_users_list: [],
                total: 0,
                listQuery: {
                    page: 1,
                    limit: 20
                },
                dialogVisible: false,
                dialogType: 'new',
                loading:false,
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
            this.getAllAgentUsers();
        },
        methods:{
            async getAllAgentUsers(){
                this.loading =  true;

                let data = this.listQuery;
                data.parent_id = this.parent_id;
                let result = await getAllAgentUsers(data);

                if( result.data.code == 1 ){
                    this.total = result.data.data.total;
                    this.agent_users_list = result.data.data.agent_users_list;
                    this.payment_method = result.data.data.payment_method;
                    this.rebates_limit = result.data.data.rebates_limit;
                }else{
                    this.$message.error(result.data.message);
                }
                this.loading =  false;
            },
            handleAddAgentUser(){
                this.agent_users = Object.assign({}, defaultAgentUsers)
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
                let current_users = await getAgentUsers(scope.row.id);
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
            handleDelete( scope ){
                this.$confirm('此操作将永久删除该配置, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then( async() => {
                    let result = await deleteAgentUsers( scope.row.id )
                    if( result.data.code == 1 ){
                        this.$message({
                            type: 'success',
                            message: '删除成功!'
                        });
                        this.getAllAgentUsers();
                    }else{
                        this.$message.error(result.data.message);
                    }
                }).catch((e) => {
                    console.log(e);
                });
            },
            async confirm(){
                const isEdit = this.dialogType === 'edit'


                let response;

                if (isEdit) {
                    response = await editAgentUsers(this.agent_users)
                }else{
                    response = await addAgentUsers(this.agent_users)
                }

                let type = 'error';
                if( response.data.code == 1 ){
                    type = 'success';
                }

                this.dialogVisible = false

                this.getAllAgentUsers();

                this.$message({
                    type: type,
                    message: response.data.msg
                });
            },
        },
        watch: {
            parent_id(){
                this.getAllAgentUsers();
            }
        }
    }
</script>

<style scoped>

</style>
