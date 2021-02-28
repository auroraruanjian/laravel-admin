<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-button type="primary" @click="handleReceipted" size="small">返回</el-button>
                <el-button type="primary" @click="getAllRecord" size="small">刷新</el-button>
            </div>

            <el-table :data="deposits" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="header-center" label="金额" prop="amount"></el-table-column>
                <el-table-column align="header-center" label="订单编号" prop="id"></el-table-column>
                <el-table-column align="header-center" label="订单类型"></el-table-column>
                <el-table-column align="header-center" label="订单状态" prop="status">
                    <template slot-scope="scope" >
                        <el-tag type="info" v-if="scope.row.status==0">支付中</el-tag>
                        <el-tag type="warning" v-else-if="scope.row.status==1">已审核</el-tag>
                        <el-tag type="success" v-else-if="scope.row.status==2">充值成功</el-tag>
                        <el-tag type="danger" v-else>充值失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="创建时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="备注" prop="created_at"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
                        <el-button type="warning" @click="handleReceipted(scope.row.id)" v-if="scope.row.status==0" size="small">已收款</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllRecord" />
        </div>
    </div>
</template>

<script>
import permission from '@/directive/permission/index.js' // 权限判断指令
import Pagination from '@/components/Pagination' // Secondary package based on el-pagination
import { getAllRecord,postChanagestatus } from '@/api/deposits'
import { mapGetters } from 'vuex'

export default {
    name: "DepositIndex",
    data(){
        return {
            deposits: [],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
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
        this.getAllRecord();
    },
    methods:{
        async getAllRecord(){
            this.loading =  true;

            let data = this.listQuery;
            let result = await getAllRecord(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.deposits = result.data.data.deposits;
            }else{
                this.$message.error(result.data.msg);
            }
            this.loading =  false;
        },
        handleReceipted( id ){
            this.$confirm('是否已确定收款?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(async () => {
                let result = await postChanagestatus({
                    id:id,
                    status:true,
                });
                let type = 'error';
                if(  result.data.code == 1 ){
                    type = 'success';
                }

                this.getAllRecord();

                this.$message({
                    type: type,
                    message: result.data.msg
                });
            });
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
