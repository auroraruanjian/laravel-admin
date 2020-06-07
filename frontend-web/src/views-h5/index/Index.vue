<template>
    <div>
        <img alt="Vue logo" src="../../assets/logo.png">
        <van-button v-if="typeof username=='undefined' || username==null || username.length==0" type="primary" @click="$router.push({path:'\login'})">登录</van-button>
        <van-button v-else @click="logout">退出</van-button>
    </div>
</template>

<script>
    import { mapGetters,mapState } from 'vuex';

    export default {
        name: "index",
        computed: {
            ...mapGetters(['username']),
        },
        data(){
            return {};
        },
        methods:{
            logout(){
                this.$dialog.confirm({
                    title: '提示',
                    message: '确认退出登录么？',
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('user/logout').then((response) => {
                        if (response.data.code == 1) {
                            this.$toast.success(response.data.msg);
                        }
                        //this.$router.push({path:'/main'});
                    }).catch((e) => {
                        console.log(e);
                    });
                }).catch(() => {
                    // on cancel
                });
            },
        }
    }
</script>

<style scoped>

</style>
