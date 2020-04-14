<template>
    <div v-loading="page_loading" class="page_index bgn" :style="{minHeight:windowHeight+'px'}">
        <div class="container index-container">
            <div class="draw_game">
                <div class="draw_top_area">
                    <div class="draw_open_code" :class="activity_draw.draw_open_code_class">
                        <span class="code_bg" v-for="(item,key) in activity_draw.open_code_position">
                            <em class="code" :class="item.code_class" :style="item.style" @webkitTransitionEnd.stop.prevent="endGame(key)"></em>
                        </span>
                    </div>
                    <el-button class="hand_option" v-if="keepDrawStatus" @click="stopKeepDraw" :class="activity_draw.hand_option"></el-button>
                    <el-button class="hand_option" v-else @click="draw(false)" :class="activity_draw.hand_option"></el-button>
                </div>
                <div class="draw_content_area">
                    <div class="content_title">{{activity.title}}</div>
                    <div class="activity_content" v-html="activity.content"></div>
                </div>
                <div class="draw_footer_area">
                    <div class="activity_prize_level">
                        <el-carousel :interval="4000" type="card" height="142px">
                            <el-carousel-item v-for="(item,key) in activity.prize_level" :key="key">
                                <img style="width:100%;height:100%;" v-if='item.prize_img!=null' :src="activity.file_path + item.prize_img">
                                <h3 >{{item.name}}</h3>
                            </el-carousel-item>
                        </el-carousel>
                    </div>

                    <div class="draw_button">
                        <el-button class="draw_btn" type="primary" plain :disabled="disabled_status" @click="draw(false)">{{disabled_status?'抽奖中':'点击抽奖'}}</el-button>
                        <el-button class="keepdraw_btn" type="primary" plain :disabled="disabled_status" v-if="!keepDrawStatus" @click="keepDraw">连抽</el-button>
                        <el-button class="keepdraw_btn" type="primary" v-if="keepDrawStatus" @click="stopKeepDraw">停止连抽</el-button>
                    </div>
                </div>
            </div>
        </div>

        <div class="draw_user" v-if="draw_open_status">
            <el-row class="draw_user_title">中奖榜单</el-row>
            <el-row class="draw_user_list">
                <vue-seamless-scroll :data="draw_user_list" :class-option="classOption" class="seamless-warp">
                    <ul class="item">
                        <li v-for="item in draw_user_list">
                            {{item}}
                        </li>
                    </ul>
                </vue-seamless-scroll>
            </el-row>
            <i class="el-icon-error" title="关闭" @click="draw_open_status=false"></i>
        </div>
        <div class="draw_open_btn" v-else @click="draw_open_status=true">排行榜</div>
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
        data(){
            return {
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
                }
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
            animate_time(){
                return this.activity_draw.open_code_position.length * this.activity_draw.animate_interval + 5000;
            },
            windowHeight(){
                return window.innerHeight - 36;
            },
        },
        created(){
            this.init();
            this.getRankList();
        },
        components:{
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
                        this.$message.error(response.data.error);
                    }
                });
            },
            async draw( keep = false ){
                // 动画没结束，不允许重复点击
                if( this.activity_draw.animate_running ) return;

                if( this.draw_time <= this.draw_count ){
                    this.$message.error('您的抽奖机会已用完!');
                    this.stopKeepDraw();
                    return;
                }

                if( keep ){
                    this.$message({
                        showClose: true,
                        message: '开始第['+this.keepDrawTime+']次抽奖!',
                        type: 'warning',
                        duration:1500,
                    });
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

                            this.$notify({
                                title: '抽奖结果',
                                type:'success',
                                message: '恭喜你，抽奖号码为：'+this.activity_draw.open_code,
                                duration:2000,
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
                        this.$message.error(response.data.msg);
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

                        let number = parseInt(_this.activity_draw.open_code_position[i].pos) + 2000 + offset*100;

                        _this.activity_draw.open_code_position[i].style = {
                            backgroundPosition:'0px -'+number+'px'
                        }
                    },i*this.activity_draw.animate_interval);
                }
            },
            endGame(index){
                //console.log('这是动画结束回调:'+index);
                // 重置数据为最小值，防止连抽数值过高
                this.activity_draw.open_code_position[index].code_class = '';
                this.activity_draw.open_code_position[index].pos = this.activity_draw.open_code_position[index].code * 100;
                this.activity_draw.open_code_position[index].style = {
                    backgroundPosition:'0px -'+this.activity_draw.open_code_position[index].pos+'px'
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


<style lang="scss" scoped>
    .page_index {
        height: 100%;
        min-height: 750px;
        background: url(/img/activity_bg.jpg) no-repeat #350b04;
        padding-top: 55px;
    }
    .draw_game{
        text-align: center;
        .draw_top_area{
            height: 219px;
            width: 902px;
            background: url(/img/activity/top_eara.png) no-repeat;
            margin: 0 auto;
            position: relative;

            .draw_open_code{
                width: 451px;
                height: 125px;
                position: absolute;
                left: 262px;
                top: 28px;
                overflow: hidden;
                text-align: center;
                &.draw_open_code5{
                    background: url(/img/activity/open_code_bg5.png) no-repeat;
                    padding: 23px 19px;
                }
                &.draw_open_code6{
                    background: url(/img/activity/open_code_bg6.png) no-repeat;
                    padding: 23px 19px;
                    width: 511px;
                    left: 245px;
                }

                .code_bg{
                    width: 81px;
                    height: 100px;
                    display: inline-block;
                    background: url(/img/activity/number_slot.jpg) no-repeat;
                    padding: 13px 0px;
                    margin: 0px auto;

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
                        width: 80px;
                        height: 100px;
                        float: left;
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
            background: url(/img/content_bg.png) repeat-y;
            padding: 0px 85px;
            width: 800px;
            margin: 0 auto;
            color: #84121e;
            font-size: 18px;
            line-height: 35px;
            padding-bottom: 45px;

            .content_title{
                margin: 13px auto;
                width: 100%;
                background: #cebaa0;
                border: 1px solid #6f1010;
                border-radius: 8px;
                display: inline-block;
                font-weight: bold;
                line-height: 40px;
            }
            .activity_content{
                margin: 13px auto 13px;
                background: #cebaa0;
                border: 1px solid #6f1010;
                border-radius: 8px;
                display: inline-block;
                width: 95%;
                padding: 16px;
                color: #000;
                font-size: 16px;
                text-align: left;
            }
        }
        .draw_footer_area{
            background: url(/img/activity_footer_bg.png) no-repeat;
            padding: 38px 31px 150px 31px;
            width: 840px;
            margin: 0 auto;
            height: 190px;
            text-align: left;
            color: #84121e;
            position: relative;

            .draw_button{
                position: absolute;
                top: -37px;
                left: 50%;
                margin-left: -199px;

                .draw_btn,.keepdraw_btn{
                    width: 139px;
                    border-radius: 12px;
                    height: 40px;
                    font-size: 18px;
                    font-weight: bold;
                    background-color: #e58d02;
                    border: none;
                    color: #fff;
                    box-shadow: 0px 6px 1px 0px #834100;

                    &:hover{
                        background-color: #f2b805;
                        box-shadow: 0px 6px 1px 0px #b06000;
                    }
                    &.is-disabled{
                        cursor: not-allowed;
                        color: #f9f7f3;
                        background-color: #aa987c;
                        box-shadow: 0px 6px 1px 0px #685e50;
                        color: #e8e8e8;
                    }
                }
                .draw_btn{
                    margin-right: 100px;
                }
                .keepdraw_btn{

                }
            }


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

    .draw_user{
        height: 380px;
        overflow: hidden;
        background: #ffffff;
        color: #282828;
        font-size: 16px;
        text-align: left;
        position: fixed;
        top: 50%;
        margin-top: -250px;
        left: 0px;
        width: 227px;
        box-shadow: 0px 0px 6px #fff;

        .draw_user_title{
            height: 30px;
            width: 95%;
            margin: 10px auto 15px;
            background: #cebaa0;
            line-height: 30px;
            text-align: center;
            color: #84121e;
            font-weight: bold;
        }

        .draw_user_list{
            /*width: 95%;*/
            /*margin: 0 auto;*/
            /*text-align: center;*/
            /*height: 311px;*/
            /*overflow: hidden;*/
        }
        .seamless-warp {
            color: #2a2a2a;
            height: 309px;
            margin: 0px 8px;
            background: #FEE7B1;
            overflow: hidden;
            border: 2px solid #C46302;
            text-align: left;
            padding: 0px 5px;
        }

        >i{
            font-size: 22px;
            position: absolute;
            top: 14px;
            right: 10px;
            color: #d54141;
            cursor: pointer;
        }
    }
    .draw_open_btn{
        position: fixed;
        top: 50%;
        margin-top: -100px;
        background: #d18016;
        color: #fff;
        padding: 9px 5px;
        width: 18px;
        text-align: center;
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
        cursor: pointer;
    }
</style>
