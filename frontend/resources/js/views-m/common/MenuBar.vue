<template>
    <van-popup
               class="page_menu"
               v-model="visible"
               position="left"
               :overlay="true"
               duration="0.1"
               :style="{ width: '60%',height:'100%' }">
        <div class="menutop">
            <div class="face">
                <van-image :src="avatar"></van-image>
            </div>

            <div class="account">{{ username }}</div>
            <div class="row">
                <span >总抽奖次数：<em>{{ draw_time }}</em></span>
            </div>
            <div class="row">
                <span >已抽奖次数：<em>{{ draw_count }}</em></span>
            </div>
        </div>

        <van-row class="funds" type="flex">
            <van-button type="danger" block @click="loginOut">
                <van-icon class-prefix="fe-icon" name="chongz" />
                <span>退出登录</span>
            </van-button>
        </van-row>

        <!--
        <van-list class="menulist" :border="false" >
            <van-cell is-link title="抽奖记录" >
                <van-icon slot="icon" class-prefix="fe-icon" name="chart" />
            </van-cell>
        </van-list>
        -->
    </van-popup>
</template>

<script>
    import { mapGetters,mapState } from 'vuex';

    export default {
        name: "MenuBar",
        computed: {
            ...mapGetters(['id', 'nickname', 'username']),
            ...mapState({
                draw_time: state => state.user.draw_time,
                draw_count: state => state.user.draw_count,
            })
        },
        data(){
            return {
                visible: false,
                avatar:'/img/m/no_avatar.jpg',
            };
        },
        created() {
            this.$eventBus.$on('ui_toggleMenu',(val)=>{this.visible = val});
        },
        methods:{
            turnTo(val) {
                if (/^\/funds\//.test(val) && !this.$root.checkUserGroup()) return;
                this.$eventBus.$emit('ui_toggleMenu',false);
                this.$router.push(val);
            },
            loginOut(){
                this.$dialog.confirm({
                    title: '提示',
                    message: '确认退出登录么？',
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('user/logout').then((response) => {
                        if (response.data.code == 1) {
                            this.$toast.success(response.data.msg);
                        }
                        this.$router.push({path:'/login'});
                    }).catch((e) => {
                        console.log(e);
                    });
                }).catch(() => {
                    // on cancel
                });
            }
        }
    }
</script>

<style lang="scss" scoped>
    .page_menu {
        transition: all 0.3s ease-out;
        box-shadow: -3px 0px 14px 3px rgba(0, 0, 0, 0.22);
        width: 60%;
        height: 100%;
        display: flex;
        flex-direction: column;

        .menutop {
            padding: 11.25px 0;
            color: #fff;
            background: linear-gradient(to bottom right, #777 0, #333 75%);
            border-bottom: 1px solid #ba0000;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;

            .face {
                width: 67.5px;
                height: 67.5px;
                border-radius: 100%;
                overflow: hidden;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 3.75px;

                &:active {
                    opacity: .8;
                }
            }
            .account {
                font-size: 16.5px;
                padding: 3.75px 9.375px;
            }

            .row {
                font-size: 13.5px;
                padding: 3.75px 5.625px 0;
                display: flex;
                justify-content: center;
                width: 100%;
                box-sizing: border-box;

                >span {
                    &+span {
                        padding-left: 7.5px;
                    }

                    &:active {
                        opacity: 0.8;
                    }
                }
                em {
                    font-weight: bold;
                    padding-left: 3.75px;
                    font-style: normal;
                    color: red;
                }
            }
        }

        .funds {
            padding: 7.5px 7.5px;
            justify-content: space-between;
            .van-col {
                flex: 0 0 31%;
                button {
                    height: 30px;
                    padding: 3.75px;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    white-space: nowrap;
                    line-height: normal;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                    transition: opacity 0.2s;
                    span {
                        font-size: 13.5px;
                        line-height: 1;
                        height: 1em;
                        i{padding-right: 1.875px;}
                    }
                    &:active{
                        opacity: 0.8;
                    }
                }
            }
        }

        .menulist {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            &::-webkit-scrollbar {
                display: none;
            }
            .van-cell{
                padding-right: 12px;
                .van-switch{
                    pointer-events: none;
                }
                >i:nth-child(1){
                    margin-right: 10px;
                }
            }
        }
    }
</style>
