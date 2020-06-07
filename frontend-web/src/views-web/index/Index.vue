<template>
    <div class="home">
        <img alt="Vue logo" src="../../assets/logo.png">
        <el-link type="primary" v-if="typeof username=='undefined' || username==null || username.length==0" tag="li" @click="$router.push({path:'\login'})">登录</el-link>
        <el-link type="primary" v-else @click="logout">退出</el-link>
    </div>
</template>

<script>
    import { mapGetters,mapState } from 'vuex';

    export default {
        name: "Index",
        computed: {
            ...mapGetters(['username']),
        },
        data(){
            return {};
        },
        methods:{
            logout(){
                this.$confirm('确认退出吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('user/logout').then((response) => {
                        if (response.data.code == 1) {
                            this.$message(response.data.msg);
                        }
                        //this.$router.push({path:'/'});
                    }).catch((e) => {
                        console.log(e);
                    });
                }).catch(() => {});
            },
        }
    }
</script>

<style scoped>

</style>
