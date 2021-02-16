<template>
    <div class="app-container" v-loading="loading">
        <div class="container">
            <div class="handle-box">
                <el-button type="primary" @click="handleAddMerchant" v-permission="'merchant/create'" size="small">创建商户</el-button>
            </div>

            <el-table :data="merchant_list" style="width: 100%;margin-top:30px;" border >
                <el-table-column align="center" label="ID" prop="id"></el-table-column>
                <el-table-column align="header-center" label="商户名称" prop="account"></el-table-column>
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

            <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getAllMerchant" />
        </div>

        <el-dialog :visible.sync="dialogVisible" :title="dialogType==='edit'?'Edit Merchant':'New Merchant'">
            <el-form :model="Merchant" label-width="15%" label-position="right">
                <el-form-item label="商户">
                    <el-input v-model="Merchant.client_id" placeholder="商户" />
                </el-form-item>
                <el-form-item label="散户名">
                    <el-input v-model="Merchant.username" placeholder="散户名" />
                </el-form-item>
                <el-form-item label="昵称">
                    <el-input v-model="Merchant.nickname" placeholder="昵称" />
                </el-form-item>
                <el-form-item label="密码">
                    <el-input v-model="Merchant.password" placeholder="密码" type="password" />
                </el-form-item>
                <el-form-item label="是否启用">
                    <el-switch
                        v-model="Merchant.status"
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
import { getAllMerchant,editMerchant,addMerchant,getMerchant,deleteMerchant } from '@/api/payment_method'
import { mapGetters } from 'vuex'


const defaultMerchant = {
    id:'',
    client_id:'1',
    username: '',
    //user_group_id: '1',
    nickname: '',
    password:'',
    status:true,
};

export default {
    name: "ChildAgentIndex",
    data(){
        return {
            Merchant: Object.assign({}, defaultMerchant),
            merchant_list: [],
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
        this.getAllMerchant();
    },
    methods:{

    },
    watch: {
        parent_id(){
            this.getAllMerchant();
        }
    }
}
</script>

<style scoped>

</style>
