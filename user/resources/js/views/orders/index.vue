<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-row :gutter="20">
                    <el-form :inline="true" :model="form" size="small">
                        <el-col :span="8">
                            <el-form-item label="日期查询">
                                <el-date-picker
                                    style="width: 350px;"
                                    v-model="form.time"
                                    type="datetimerange"
                                    range-separator="至"
                                    start-placeholder="开始时间"
                                    end-placeholder="结束时间">
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label="帐变类型">
                                <el-select v-model="form.order_type_id" placeholder="帐变类型">
                                    <el-option label="全部" value=""></el-option>
                                    <el-option v-for="(item,key) in order_type" :key="key" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label="用户名查询">
                                <el-input v-model="form.username" placeholder="请输入用户名"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="4">
                            <el-form-item>
                                <el-button type="primary" @click="getAllRecord">查询</el-button>
                            </el-form-item>
                        </el-col>
                    </el-form>
                </el-row>
            </div>

            <el-table :data="orders" style="width: 100%;margin-top:30px;" border fixed>
                <el-table-column align="header-center" label="编号" prop="id"></el-table-column>
                <el-table-column align="header-center" label="账变类型" prop="order_type_name" width="190">
                    <template slot-scope="scope" >
                        <el-tag>{{ scope.row.operation==1?"[+]":(scope.row.operation==2?"[-]":"[h]") }} {{ scope.row.order_type_name }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="收入">
                    <template slot-scope="scope" >
                        <el-tag type="success"  v-if="scope.row.operation == 1">
                            {{ new Intl.NumberFormat(['en-US'], {minimumFractionDigits:4}).format(scope.row.amount) }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="支出">
                    <template slot-scope="scope" >
                        <el-tag type="danger" v-if="(scope.row.operation == 2 || (scope.row.hold_operation == 2 && scope.row.operation == 0))">
                            {{ new Intl.NumberFormat(['en-US'], {minimumFractionDigits:4}).format(scope.row.amount) }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="账变后余额/冻结">
                    <template slot-scope="scope" >
                        {{ scope.row.balance }} / {{ scope.row.hold_balance }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="账变前余额/冻结">
                    <template slot-scope="scope" >
                        {{ scope.row.pre_balance }} / {{ scope.row.pre_hold_balance }}
                    </template>
                </el-table-column>
                <el-table-column align="header-center" label="账变时间" prop="created_at"></el-table-column>
                <el-table-column align="header-center" label="备注" prop="comment"></el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope" >
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
import { getAllRecord } from '@/api/orders'
import { mapGetters } from 'vuex'

export default {
    name: "DepositIndex",
    data(){
        return {
            orders: [],
            total: 0,
            listQuery: {
                page: 1,
                limit: 20
            },
            loading:false,
            form:{
                time:[

                ],
                order_type_id:'',
                username:'',
            },
            order_type:[],
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

            let data = Object.assign(this.listQuery,this.form);
            let result = await getAllRecord(data);

            if( result.data.code == 1 ){
                this.total = result.data.data.total;
                this.orders = result.data.data.orders;
                this.order_type = result.data.data.order_type;
            }else{
                this.$message.error(result.data.msg);
            }
            this.loading =  false;
        },
        /*
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
                let type = 'danger';
                if(  result.data.code == 1 ){
                    type = 'success';
                }
                this.$message({
                    type: type,
                    message: result.data.msg
                });
            });
        },
         */
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
