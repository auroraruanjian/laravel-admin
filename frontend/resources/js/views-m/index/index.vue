<template>
    <div class="page page_index">
        <van-nav-bar
            :title="title"
            right-text="退出"
            class="nav"
            @click-left="$eventBus.$emit('ui_toggleMenu',true)"
            @click-right="$eventBus.$emit('ui_toggleRightMenu',true)">
            <template #left>
                <van-icon name="bars" size="1.8em"/>
            </template>
            <template #right>
                <van-icon name="column" size="1.8em"/>
            </template>
        </van-nav-bar>

        <MenuBar></MenuBar>
        <ActivityRecord></ActivityRecord>

        <section class="van-section">
            <van-button type="primary" :disabled="disabled_status" @click="draw(false)">点击抽奖</van-button>
            <van-button type="primary" :disabled="disabled_status" v-if="!keepDrawStatus" @click="keepDraw">连抽</van-button>
            <van-button type="primary" v-if="keepDrawStatus" @click="stopKeepDraw">停止连抽</van-button>
        </section>
    </div>
</template>

<script>
    import { getDraw } from '@/api/activity';
    import { mapState } from 'vuex';

    export default {
        name: "index",
        components: {
            MenuBar: () => import('../common/MenuBar.vue'),
            ActivityRecord: () => import('../activity/record.vue'),
        },
        computed: {
            ...mapState({
                draw_time: state => state.user.draw_time,
                draw_count: state => state.user.draw_count,
            })
        },
        data(){
            return {
                title:'',
                disabled_status:false,
                keepDrawStatus:false, // 停止连抽
                keepDrawTime:1,
            };
        },
        methods:{
            async draw( keep = false ){
                if( this.draw_time <= this.draw_count ){
                    this.$toast.fail('您的抽奖机会已用完!');
                    this.stopKeepDraw();
                    return;
                }

                if( keep ){
                    this.$notify({ type: 'danger', message: '开始第['+this.keepDrawTime+']次抽奖!' ,duration:1000});
                }

                this.disabled_status = true;
                await getDraw().then( response => {
                    if( response.data.code == 1 ){
                        this.$store.commit('user/SET_DRAW',{draw_count:this.draw_count+1});
                        this.$toast.success(response.data.msg);
                    }else{
                        this.$toast.fail(response.data.msg);
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

<style lang="scss" scoped>
    .nav {
        position: relative;
        height: 56px;
        line-height: 56px;
        text-align: center;
        background-color: #fff;
        border-bottom: 1px solid #ddd;

        .van-icon{color: #323233;}
    }
    .van-section{
        box-sizing: border-box;
        min-height: calc(100vh - 56px);
        padding-bottom: 20px;
    }
    .van-section{
        background-color: #f2f2f2;
    }
</style>
