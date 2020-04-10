<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-button type="primary" icon="el-icon-circle-plus-outline" v-permission="'activityIssue/create'" @click="$router.push({path:'/activityIssue/create'})" size="small">新增奖期</el-button>
            </div>

            <el-table :data="activityIssueList" style="width: 100%;margin-top:30px;" border>
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="开始时间" prop="start_at"></el-table-column>
                <el-table-column align="header-center" label="结束时间" prop="end_at"></el-table-column>
                <el-table-column align="header-center" label="奖券总量（千）" v-if="listQuery.id==1">
                    <template slot-scope="scope">
                        {{ scope.row.extra.tickets_total }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="奖品" v-if="listQuery.id==1">
                    <template slot-scope="scope">
                        <p v-for="(item,key) in scope.row.extra.prize_level" :key="key">
                            {{item.name}}：{{item.prize_name}}
                        </p>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="开奖号码" v-if="listQuery.id==1">
                    <template slot-scope="scope">
                        {{ typeof scope.row.extra.code!='undefined'?scope.row.extra.code:'未开奖' }}
                    </template>
                </el-table-column>
                <el-table-column align="center" label="Operations">
                    <template slot-scope="scope">
                        <el-button type="primary" size="small"
                                   @click="$router.push({path:`/activityIssue/edit/${scope.row.id}`})"
                                   v-permission="'activityIssue/edit'">
                            编辑
                        </el-button>
                        <el-button type="warning" size="small"
                                   @click="handleOpenCode(scope.row.id)"
                                   v-permission="'activityIssue/openCode'"
                                   v-if="typeof scope.row.extra.code=='undefined' || scope.row.extra.code == null">
                            开奖
                        </el-button>
<!--                        <el-button type="primary" size="small" @click="handleDelete(scope)" v-permission="'activityIssue/delete'">删除</el-button>-->
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getActivityIssue" />
        </div>

        <el-dialog :visible.sync="openCode.dialog" title="开奖" width="450px">
            <el-form :model="openCode.form" :rules='openCode.rules' ref="openCode" label-width="15%" label-position="right">
                <el-form-item label="号码" prop="code">
                    <el-input v-model="openCode.form.code" placeholder="三位数字" />
                </el-form-item>
            </el-form>
            <div style="text-align:right;">
                <el-button type="danger" @click="openCode.dialog=false">Cancel</el-button>
                <el-button type="primary" @click="confirmOpenCode">确认</el-button>
            </div>
        </el-dialog>

    </div>
</template>

<script>
    import permission from '@/directive/permission/index.js' // 权限判断指令
    import Pagination from '@/components/Pagination' // Secondary package based on el-pagination
    import { getActivityIssue,deleteActivityIssue,putOpenCode } from '@/api/activity_issue'

    export default {
        name: "activity_issue_index",
        directives: { permission },
        data(){
            return {
                activityIssueList: [],
                loading:false,
                total: 0,
                listQuery: {
                    page: 1,
                    limit: 20,
                    id: this.$route.params && this.$route.params.id,
                },
                dialogVisible: false,
                dialogType: 'new',
                openCode:{
                    dialog:false,
                    form:{
                        id: '',
                        code: '',
                    },
                    rules:{
                        code:[
                            { required: true, type: "string", message: '开奖号码为三位数字！',len: 3,  trigger: 'blur' },
                        ]
                    }
                }
            };
        },
        components: { Pagination },
        computed: {},
        methods:{
            async getActivityIssue(){
                this.loading =  false;
                let result = await getActivityIssue(this.listQuery);
                if( result.data.code == 1 ){
                    this.total = result.data.data.total;
                    this.activityIssueList = result.data.data.activity_issue;
                }else{
                    this.$message.error(result.data.message);
                }
                this.loading =  false;
            },
            handleDelete( scope ){
                this.$confirm('此操作将永久删除该奖期么？ 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then( async() => {
                    let result = await deleteActivityIssue( scope.row.id )
                    if( result.data.code == 1 ){
                        this.$message({
                            type: 'success',
                            message: '删除成功!'
                        });
                        this.getActivityIssue();
                    }else{
                        this.$message.error(result.data.message);
                    }
                }).catch((e) => {
                    console.log(e);
                });
            },
            handleOpenCode( id ){
                this.openCode.form.id = id;
                this.openCode.form.code = '';
                this.openCode.dialog = true;
            },
            confirmOpenCode(){
                this.$refs['openCode'].validate((valid) => {
                    if (valid) {
                        putOpenCode( this.openCode.form ).then( response => {
                            let type = 'error';
                            if( response.data.code == 1 ){
                                type = 'success';
                                this.openCode.dialog = false;
                                this.getActivityIssue();
                            }

                            this.$message({
                                message: response.data.msg,
                                type
                            });
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });

            }
        },
        created() {
            this.getActivityIssue();
        }
    }
</script>

<style scoped>

</style>
