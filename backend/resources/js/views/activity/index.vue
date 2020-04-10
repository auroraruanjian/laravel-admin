<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <!--
            <div class="handle-box">
                <el-button type="primary" icon="el-icon-circle-plus-outline" v-permission="'activity/create'" @click="$router.push({path:'/activity/create'})" size="small">新建活动</el-button>
            </div>
            -->

            <el-table :data="activityList" style="width: 100%;margin-top:30px;" border>
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="标识符" prop="ident"></el-table-column>
                <el-table-column align="header-center" label="标题" prop="title"></el-table-column>
                <el-table-column align="header-center" label="状态">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status==0" type="success">开启</el-tag>
                        <el-tag v-else type="warning">显示</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="创建时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="修改时间" prop="updated_at"></el-table-column>
                <el-table-column align="center" label="Operations">
                    <template slot-scope="scope">
                        <el-button type="primary" size="small" @click="$router.push({path:`/activity/edit/${scope.row.id}`})" v-permission="'activity/edit'">编辑</el-button>
                        <el-button type="primary" size="small" @click="$router.push({path:`/activityIssue/index/${scope.row.id}`})" v-permission="'activityIssue/index'">奖期管理</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getActivitys" />
        </div>
    </div>
</template>

<script>
    import permission from '@/directive/permission/index.js' // 权限判断指令
    import { getActivitys } from '@/api/activity'
    import Pagination from '@/components/Pagination' // Secondary package based on el-pagination

    export default {
        name: "activity_index",
        directives: { permission },
        data(){
            return {
                activityList: [],
                loading:false,
                total: 0,
                listQuery: {
                    page: 1,
                    limit: 20
                },
            };
        },
        components: { Pagination },
        computed: {},
        methods:{
            async getActivitys(){
                this.loading =  false;
                let result = await getActivitys(this.listQuery);
                if( result.data.code == 1 ){
                    this.total = result.data.data.total;
                    this.activityList = result.data.data.activitys;
                }else{
                    this.$message.error(result.data.message);
                }
                this.loading =  false;
            },
        },
        created() {
            this.getActivitys();
        }
    }
</script>

<style scoped>

</style>
