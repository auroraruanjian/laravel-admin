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

        <el-dialog :visible.sync="dialogVisible" :title="dialogType==='edit'?'Edit Agent Users':'New Agent Users'">
            <el-form :model="agent_users" label-width="15%" label-position="right">
                <el-form-item label="商户">
                    <el-input v-model="agent_users.client_id" placeholder="商户" />
                </el-form-item>
                <el-form-item label="代理名">
                    <el-input v-model="agent_users.username" placeholder="代理名" />
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
            </el-form>
            <div style="text-align:right;">
                <el-button type="danger" @click="dialogVisible=false">Cancel</el-button>
                <el-button type="primary" @click="confirm">Confirm</el-button>
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
        client_id:'1',
        username: '',
        //user_group_id: '1',
        nickname: '',
        password:'',
        status:true,
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
                }else{
                    this.$message.error(result.data.message);
                }
                this.loading =  false;
            },
            handleAddAgentUser(){
                this.agent_users = Object.assign({}, defaultAgentUsers)
                this.dialogType = 'new'
                this.dialogVisible = true
            },
            async handleEdit( scope ){
                this.loading =  true;
                let current_users = await getAgentUsers(scope.row.id);
                this.agent_users = current_users.data.data;
                this.dialogType = 'edit'
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

                let type = 'error';
                let message = '';

                let response;

                if (isEdit) {
                    response = await editAgentUsers(this.users)
                }else{
                    response = await addAgentUsers(this.users)
                }

                if( response.data.code == 1 ){
                    type = 'success';
                    message = `
                            <div>Users name: ${this.users.title}</div>
                          `;
                }else{
                    message = response.data.msg;
                }

                this.dialogVisible = false

                this.getAllAgentUsers();

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
                this.getAllAgentUsers();
            }
        }
    }
</script>

<style scoped>

</style>
