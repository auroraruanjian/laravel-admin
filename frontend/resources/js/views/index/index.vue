<template>
    <div class="page_index bgn">
        <div class="container index-container">
            <el-button type="primary" :disabled="disabled_status" @click="draw(false)">点击抽奖</el-button>
            <el-button type="primary" :disabled="disabled_status" v-if="!keepDrawStatus" @click="keepDraw">连抽</el-button>
            <el-button type="primary" v-if="keepDrawStatus" @click="stopKeepDraw">停止连抽</el-button>
        </div>
    </div>
</template>

<script>
    import { mapGetters,mapState } from 'vuex';
    import { getDraw } from '@/api/activity';

    export default {
        name: "index",
        data(){
            return {
                disabled_status:false,
                keepDrawStatus:false, // 停止连抽
                keepDrawTime:1,
            };
        },
        computed: {
            ...mapGetters([
                'username',
            ]),
            ...mapState({
                nickname: state => state.user.nickname,
                draw_time: state => state.user.draw_time,
                draw_count: state => state.user.draw_count,
            }),
        },
        created(){
            console.log('rrunnnnn');
        },
        methods:{
            async draw( keep = false ){
                if( this.draw_time <= this.draw_count ){
                    this.$message.error('您的抽奖机会已用完!');
                    this.stopKeepDraw();
                    return;
                }

                if( keep ){
                    this.$message('开始第['+this.keepDrawTime+']次抽奖!');
                }

                this.disabled_status = true;
                await getDraw().then( response => {
                    if( response.data.code == 1 ){
                        this.$store.commit('user/SET_DRAW',{draw_count:this.draw_count+1});
                        this.$notify({
                            title: '抽奖结果',
                            type:'success',
                            message: response.data.msg
                        });
                    }else{
                        this.$message.error(response.data.msg);
                    }

                    if( keep && this.keepDrawStatus ){
                        this.keepDrawTime++;
                        setTimeout(()=>{
                            if( this.keepDrawStatus ){
                                this.draw( keep );
                            }
                        },3000);
                    }else{
                        this.disabled_status = false;
                    }
                });
            },
            keepDraw(){
                this.keepDrawStatus = true;
                this.keepDrawTime = 1;
                this.draw( true );
            },
            stopKeepDraw(){
                this.keepDrawStatus = false;
                this.disabled_status = false;
                this.keepDrawTime = 0;
            },
        }
    }
</script>

<style scoped>

</style>
