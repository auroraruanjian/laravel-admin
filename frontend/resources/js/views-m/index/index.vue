<template>
    <div class="page_index">
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
            <div class="draw_game">
                <div class="draw_top_area">
                    <div class="draw_open_code" :class="activity_draw.draw_open_code_class" :style="{marginTop:drawCodeMarginTop+'px'}">
                        <span class="code_bg" v-for="(item,key) in activity_draw.open_code_position">
                            <em class="code" :class="item.code_class" :style="item.style" @webkitTransitionEnd.stop.prevent="endGame(key)"></em>
                        </span>
                    </div>
<!--                    <van-button class="hand_option" v-if="keepDrawStatus" @click="stopKeepDraw" :class="activity_draw.hand_option"></van-button>-->
<!--                    <van-button class="hand_option" v-else @click="draw(false)" :class="activity_draw.hand_option"></van-button>-->
                </div>
                <div class="draw_content_area">
                    <div class="content_title">{{activity.title}}</div>
                    <div class="activity_content" >
                        <div v-html="activity.content"></div>
                        <div class="draw_button">
                            <van-button type="primary" :disabled="disabled_status" @click="draw(false)">点击抽奖</van-button>
                            <van-button type="primary" :disabled="disabled_status" v-if="!keepDrawStatus" @click="keepDraw">连抽</van-button>
                            <van-button type="primary" v-if="keepDrawStatus" @click="stopKeepDraw">停止连抽</van-button>
                        </div>
                    </div>

                </div>
                <div class="draw_footer_area">
                    <van-swipe :loop="true" :autoplay="1000" :width="250" style="height: 150px">
                        <van-swipe-item v-for="(item,key) in activity.prize_level" :key="key">
                            <img style="width:100%;height:100%;" v-if='item.prize_img!=null' :src="activity.file_path + item.prize_img">
                            <h3 >{{item.name}}</h3>
                        </van-swipe-item>
                    </van-swipe>

<!--                        <el-carousel :interval="4000" type="card" height="142px">-->
<!--                            <el-carousel-item >-->
<!--                                -->
<!--                                -->
<!--                            </el-carousel-item>-->
<!--                        </el-carousel>-->
                </div>
            </div>
        </section>

        <van-popup
            v-model="draw_open_status"
            closeable
            position="bottom"
            :overlay="false"
            :lock-scroll="false"
            round
            class="popup_user_list"
        >
            <vue-seamless-scroll :data="draw_user_list" :class-option="classOption" class="seamless-warp">
                <ul class="item">
                    <li v-for="item in draw_user_list">
                        {{item}}
                    </li>
                </ul>
            </vue-seamless-scroll>
        </van-popup>
        <van-button class="close_draw_user_list" v-if="!draw_open_status" icon="arrow-up" @click="draw_open_status=true">排行榜</van-button>
    </div>
</template>

<script>
    import { mapGetters,mapState } from 'vuex';
    import { getDraw,getActivity,getRankList } from '@/api/activity';

    const cubic = value => Math.pow(value, 3);
    const easeInOutCubic = value => value < 0.5
        ? cubic(value * 2) / 2
        : 1 - cubic((1 - value) * 2) / 2;

    export default {
        name: "index",
        components: {
            MenuBar: () => import('../common/MenuBar.vue'),
            ActivityRecord: () => import('../activity/record.vue'),
        },
        computed: {
            ...mapState({
                nickname: state => state.user.nickname,
                draw_time: state => state.user.draw_time,
                draw_count: state => state.user.draw_count,
            }),
            animate_time(){
                return this.activity_draw.open_code_position.length * this.activity_draw.animate_interval + 5000;
            },
            windowHeight(){
                return window.innerHeight - 36;
            },
            drawCodeMarginTop(){
                return window.innerWidth/5 + 80;
            }
        },
        data(){
            return {
                title:'',
                page_loading:false,
                disabled_status:false,
                keepDrawStatus:false, // 停止连抽
                keepDrawTime:1,
                activity_draw:{
                    hand_option:'hand_up',
                    draw_open_code_class:'draw_open_code5',
                    //open_code:[0,0,0,0,0],
                    open_code_position:[],
                    open_code:'',
                    animate_running:false,
                    animate_interval:300,
                },
                activity_id:1,
                activity:{
                    ident:'',
                    title:'',
                    describe:'',
                    content:'',
                    status:'',
                    start_at:'',
                    end_at:'',
                    prize_level:[],
                    file_path:'',
                },
                scrollTopInterval:'',
                draw_open_status:true,
                draw_user_list:[],
                classOption:{
                    autoPlay: false,
                },
                code_height:18.321
            };
        },
        created(){
            this.init();
            this.getRankList();

            let _this = this;
            setTimeout(function(){
                _this.draw_open_status = false;
            },3000);
        },
        methods:{
            init(){
                this.page_loading = true;
                getActivity( this.activity_id ).then( response => {
                    this.page_loading = false;
                    if( response.data.code == 1 ){
                        this.activity = response.data.data;
                        this.activity_draw.open_code_position = [];
                        for( let i=0;i<response.data.data.code_len;i++ ){
                            this.activity_draw.open_code_position.push({code:0,last_code:0,pos:0,style:{},code_class:''});
                        }
                        if( response.data.data.code_len >= 6 ) this.activity_draw.draw_open_code_class = 'draw_open_code6';
                    }else{
                        this.$notify({ type: 'danger', message: response.data.msg });
                    }
                });
            },
            async draw( keep = false ){
                // 动画没结束，不允许重复点击
                if( this.activity_draw.animate_running ) return;

                if( this.draw_time <= this.draw_count ){
                    this.$notify({ type: 'danger', message: '您的抽奖机会已用完!' });
                    this.stopKeepDraw();
                    return;
                }

                if( keep ){
                    this.$notify({ type: 'success', message: '开始第['+this.keepDrawTime+']次抽奖!',duration:1500, });
                }
                if( !keep || this.keepDrawTime==1 ){
                    this.backToTop();
                }

                this.activity_draw.hand_option = 'hand_down';

                this.disabled_status = true;
                await getDraw().then( response => {
                    if( response.data.code == 1 ){
                        this.$store.commit('user/SET_DRAW',{draw_count:this.draw_count+1});

                        // 赋值，启动动画
                        for(let i in this.activity_draw.open_code_position){
                            this.activity_draw.open_code_position[i].code = response.data.data.code[i];
                        }
                        this.activity_draw.open_code = response.data.data.code.join('');
                        this.run();

                        // 动画结束后
                        setTimeout(()=>{
                            this.activity_draw.animate_running = false;
                            this.activity_draw.hand_option = 'hand_up';

                            this.$toast({
                                message:'恭喜你，抽奖号码为：'+this.activity_draw.open_code,
                                duration:2000,
                                type:'success'
                            });

                            this.disabled_status = false;
                        },this.animate_time);

                        if( keep && this.keepDrawStatus ){
                            this.keepDrawTime++;
                            setTimeout(()=>{
                                if( this.keepDrawStatus ){
                                    this.draw( keep );
                                }
                            },this.animate_time);
                        }else{
                            this.stopKeepDraw();
                        }
                    }else{
                        this.$notify({ type: 'danger', message: response.data.msg });
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
                this.keepDrawTime = 0;
            },
            run(){
                let _this = this;
                _this.activity_draw.animate_running = true;
                for(let i in this.activity_draw.open_code_position){

                    setTimeout(()=>{
                        _this.activity_draw.open_code_position[i].code_class = 'code_animation';

                        // 计算当前号码和新号码偏移值
                        let offset = _this.activity_draw.open_code_position[i].code - _this.activity_draw.open_code_position[i].last_code;
                        _this.activity_draw.open_code_position[i].last_code = _this.activity_draw.open_code_position[i].code;

                        let number = parseFloat(_this.activity_draw.open_code_position[i].pos) + (this.code_height*20) + offset*this.code_height;

                        _this.activity_draw.open_code_position[i].style = {
                            backgroundPosition:'0px -'+number.toFixed(5)+'vw'
                        }
                    },i*this.activity_draw.animate_interval);
                }
            },
            endGame(index){
                //console.log('这是动画结束回调:'+index);
                // 重置数据为最小值，防止连抽数值过高
                this.activity_draw.open_code_position[index].code_class = '';
                this.activity_draw.open_code_position[index].pos = (this.activity_draw.open_code_position[index].code * this.code_height).toFixed(5);

                console.log(this.activity_draw.open_code_position[index].pos);
                this.activity_draw.open_code_position[index].style = {
                    backgroundPosition:'0px -'+this.activity_draw.open_code_position[index].pos+'vw'
                }
            },
            backToTop(){
                const el = document.documentElement;
                const beginTime = Date.now();
                const beginValue = el.scrollTop;
                const rAF = window.requestAnimationFrame || (func => setTimeout(func, 16));
                const frameFunc = () => {
                    const progress = (Date.now() - beginTime) / 500;
                    if (progress < 1) {
                        el.scrollTop = beginValue * (1 - easeInOutCubic(progress));
                        rAF(frameFunc);
                    } else {
                        el.scrollTop = 0;
                    }
                };
                rAF(frameFunc);
            },
            getRankList(){
                getRankList().then( response => {
                    if( response.data.code == 1 ){
                        for( let i in response.data.data ){
                            let username = response.data.data[i].username;
                            let prize_level_name = response.data.data[i].prize_level_name;

                            this.draw_user_list.push('恭喜'+username+'获得'+prize_level_name);
                        }
                        this.classOption.autoPlay = true;
                    }
                });
            }
        }
    }
</script>

<style lang="scss" >
    .nav {
        position: fixed;
        height: 56px;
        line-height: 56px;
        text-align: center;
        background-color: transparent;
        top:0px;
        width: 100vw;

        .van-icon{color: #323233;}
        &:after{
            border:none;
        }
        .van-nav-bar__left, .van-nav-bar__right{
            background: #fff;
            width: 40px;
            height: 40px;
            padding: 0px;
            text-align: center;
            border-radius: 50%;
            top: 5px;

             >i{
                font-size: 1.8em;
                color: #e41b47;
                margin: 0 auto;
            }
        }
        .van-nav-bar__left{
            left: 7px;
        }
        .van-nav-bar__right{
            right: 7px;
        }
    }
    .van-section{
        box-sizing: border-box;
        min-height: calc(100vh - 56px);
        padding-bottom: 20px;
    }
    .van-section{
    }
    .page_index{
        background: url(/img/m/bg.png) no-repeat top center;
        background-size: 100%;
        background-color: #e41b47;
    }

    .draw_game{
        text-align: center;
        .draw_top_area{
            margin: 0 auto;
            position: relative;
            display: flex;
            justify-content: center;

            .draw_open_code{
                overflow: hidden;

                &.draw_open_code5{
                    /*background: url(/img/activity/open_code_bg5.png) no-repeat;*/
                }
                &.draw_open_code6{
                    /*background: url(/img/activity/open_code_bg6.png) no-repeat;*/
                }

                .code_bg{
                    display: inline-block;
                    padding: 13px 0px;
                    margin: 0px auto;
                    background: #ffffffd4;
                    border: 2px solid #d1002e;
                    border-left: 0px;
                    &:first-child{
                        border-left: 2px solid #d1002e;
                    }

                    .code_animation{
                        transition-property: background-position;
                        transition-duration: 5s;
                        transition-timing-function: ease;
                        /* Firefox 4 */
                        -moz-transition-property:background-position;
                        -moz-transition-duration:5s;
                        -moz-transition-timing-function:ease;
                        /* Safari 和 Chrome */
                        -webkit-transition-property:background-position;
                        -webkit-transition-duration:5s;
                        -webkit-transition-timing-function:ease;
                        /* Opera */
                        -o-transition-property:background-position;
                        -o-transition-duration:5s;
                        -o-transition-timing-function:ease;
                    }

                    .code{
                        background: url(/img/activity/code.png) repeat-y scroll left top;
                        width: 15vw;
                        height: 18vw;
                        float: left;
                        background-size: 220%;
                    }
                }
            }

            .hand_option{
                width: 70px;
                height: 210px;
                cursor: pointer;
                background: url(/img/activity/handle_bg.png) no-repeat;
                right: -68px;
                position: absolute;
                border: none;
                transition: 0s;
                top: 0px;

                &.hand_up{
                    background-position: 0px 0px;
                }
                &.hand_down{
                    background-position:-140px 0px;
                }
            }
        }
        .draw_content_area{
            margin: 0 auto;
            font-size: 18px;
            line-height: 35px;

            .content_title{
                margin: 13px auto;
                width: 95vw;
                background: #ffffffbf;
                border: 1px solid #bd1036;
                border-radius: 8px;
                display: inline-block;
                font-weight: bold;
                line-height: 40px;
                color: #ff0000;
                padding: 0.8vw 1.2vw ;
            }
            .activity_content{
                margin: 13px auto 13px;
                border-radius: 8px;
                display: inline-block;
                width: 92.8vw;
                padding: 2.2vw;
                color: #000;
                font-size: 16px;
                text-align: left;
                background: #ffffffbf;
                border: 1px solid #bd1036;

                >div>p{
                    margin-block-start: 0.3em;
                    margin-block-end: 0.5em;
                }

                .draw_button{
                    text-align: center;
                }
            }
        }
        .draw_footer_area{
            width: 92.8vw;
            padding: 2.2vw;
            margin: 0 auto;
            text-align: left;
            color: #000;
            position: relative;
            background: #ffffffbf;
            border: 1px solid #bd1036;
            border-radius: 8px;


            .activity_prize_level{
                background: #582e29;
                border-radius: 8px;
                padding: 17px 17px 0px;

                .el-carousel__item {
                    h3{
                        font-size: 15px;
                        margin: 0;
                        position: absolute;
                        color: #fff;
                        background: #3d3d3d;
                        padding: 5px 8px;
                        opacity: 0.7;
                        width: 100%;
                        bottom: 0px;
                        text-align: center;
                        font-weight: bold;
                        transition: opacity 0.3s;
                    }
                    &:hover h3{
                        opacity: 0.98;
                    }
                }


                .el-carousel__item:nth-child(2n) {
                    background-color: #99a9bf;
                }

                .el-carousel__item:nth-child(2n+1) {
                    background-color: #d3dce6;
                }

                .el-carousel__item--card.is-in-stage{
                    border-radius: 5px;
                }
            }
        }
    }
    .popup_user_list{
        height:20vh;
        background: #ffffffd1;
        overflow:hidden;
        text-align: center;
        &.van-popup--bottom.van-popup--round{
            border-radius: 10px 10px 0 0;
        }

        .seamless-warp{
            width:90vw;
            margin:0 auto;

            .item >li{
                color:#e51d1d;
            }
        }

        >.van-icon{
            background: #d84444;
            border-radius: 23%;
            padding: 1px;
            top: 5px;
            color: #fff;
        }
    }
    .close_draw_user_list{
        border: 0px;
        position: fixed;
        bottom: 0px;
        left: 50%;
        margin-left: -51px;
        background: #ffffffc2;
        color: #f15555;
        font-size: 15px;
        height: 37px;
        line-height: 37px;
        border-radius: 5px 5px 0 0;
    }
</style>
