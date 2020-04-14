<template>
    <div class="page_index bgn" :style="{minHeight:windowHeight+'px'}">
        <div class="container index-container">
            <div class="draw_game">
                <div class="draw_top_area">
                    <div class="draw_open_code" :class="activity_draw.draw_open_code_class">
                        <span class="code_bg" v-for="(item,key) in activity_draw.open_code_position">
                            <em class="code" :class="item.code_class" :style="item.style" @webkitTransitionEnd.stop.prevent="endGame(key)"></em>
                        </span>
                    </div>
                    <el-button class="hand_option" @click="draw(false)" :class="activity_draw.hand_option"></el-button>
                </div>
                <div class="draw_content_area">
                    <div class="content_title">这是活动标题</div>
                    <div class="activity_content">这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容这是活动内容</div>
                </div>
                <div class="draw_footer_area">
                    <el-row class="draw_user_list">
                        <el-col :span="12"><p v-for="item in 20" :key="item">恭喜dddd中 一等奖 iphone</p></el-col>
                        <el-col :span="12"><p v-for="item in 20" :key="item">恭喜dddd中 一等奖 iphone</p></el-col>
                    </el-row>
                    <div class="draw_button">
                        <el-button class="draw_btn" type="primary" plain :disabled="disabled_status" @click="draw(false)">{{disabled_status?'抽奖中':'点击抽奖'}}</el-button>
                        <el-button class="keepdraw_btn" type="primary" plain :disabled="disabled_status" v-if="!keepDrawStatus" @click="keepDraw">连抽</el-button>
                        <el-button class="keepdraw_btn" type="primary" v-if="keepDrawStatus" @click="stopKeepDraw">停止连抽</el-button>
                    </div>
                </div>
            </div>
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
                activity_draw:{
                    hand_option:'hand_up',
                    draw_open_code_class:'draw_open_code5',
                    //open_code:[0,0,0,0,0],
                    open_code_position:[
                        {code:0,last_code:0,pos:0,style:{},code_class:''},
                        {code:0,last_code:0,pos:0,style:{},code_class:''},
                        {code:0,last_code:0,pos:0,style:{},code_class:''},
                        {code:0,last_code:0,pos:0,style:{},code_class:''},
                        {code:0,last_code:0,pos:0,style:{},code_class:''},
                    ],
                    open_code:'',
                    animate_running:false,
                    animate_interval:300,
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
            }
        },
        created(){

        },
        methods:{
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
            padding-bottom: 40px;

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

            .draw_user_list{
                height: 190px;
                overflow: hidden;
                background: #cebaa0;
                border: 1px solid #6f1010;
                border-radius: 8px;
                width: 100%;
                padding: 16px;
                color: #84121e;
                font-size: 16px;
                text-align: left;

                >.el-col{
                    width: 50%;
                    height: 156px;
                    overflow: hidden;
                }
            }

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
                        background-color: #f7b34c;
                        box-shadow: 0px 6px 1px 0px #c08937;
                        cursor: not-allowed;
                        color: #f9f7f3;
                    }
                }
                .draw_btn{
                    margin-right: 100px;
                }
                .keepdraw_btn{

                }
            }
        }
    }
</style>
