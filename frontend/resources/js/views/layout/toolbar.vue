<template>
    <div class="toolbar">
        <div class="container">
            <div class="tbl">

            </div>
            <div class="tbr">
                <span class="username"><em class="img-icon-user3"></em>你好，<b>{{ username }}</b></span>
                <span ><em class="img-icon-yue">已用抽奖次数:</em><b>111</b></span>
                <span ><em class="img-icon-yue">剩余抽奖次数:</em><b>111</b></span>

                <span class="btns" noborder>
                    <el-button class="fundsbtn cz" @click="$eventBus.$emit('showActivityRecord',true)">抽奖记录</el-button>
                </span>
                <span @click="logout" noborder>退出</span>
            </div>
        </div>

        <ActivityRecord></ActivityRecord>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        name: "toolbar",
        data(){
            return {
                activity_record_visible:false,
            };
        },
        methods:{
            logout() {
                this.$confirm('确认退出吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('user/logout').then((response) => {
                        if (response.data.code == 1) {
                            this.$message(response.data.msg);
                        }
                        this.$router.push({path:'/login'});
                    }).catch((e) => {
                        console.log(e);
                    });
                }).catch(() => {});
            },
        },
        computed: {
            ...mapGetters(['id', 'nickname', 'username']),
        },
        components:{
            ActivityRecord:() => import('../activity/record.vue'),
        }
    }
</script>


<style lang="scss">

    .toolbar{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 36px;
        background-color: #393939;
        color: #fff;
        font-size: 12px;
        z-index: 100;

        .container {
            display: flex;
            justify-content: space-between;
            height: 100%;
            .tbl{
                -webkit-box-flex: 0;
                -ms-flex: 0 0 22%;
                flex: 0 0 22%;
                padding-right:10px;
            }
            .tbr {
                display: flex;
                > span {
                    display: flex;
                    align-items: center;
                    margin: 0 12px;
                    transition: all .3s;
                    line-height: 2;
                    position: relative;
                    cursor: pointer;
                }
                b {
                    color: #f4586f;
                }
            }
            .btns{
                cursor: default;
                button{
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    border: none;
                    font-size: 12px;
                }
                .fundsbtn{
                    height: 23px;
                    padding: 0px 11px;
                    background-color: #ba0000;
                }
            }
        }
    }
</style>

