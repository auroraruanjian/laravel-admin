<template>
    <div class="my-outbox" ref='outbox' @mouseenter="stop" @mouseleave="start">
        <div class="my-inbox" ref='movebox'>
            <p class="my-listbox" v-for="(item,index) in sendVal" :key='index'>
                {{item}}
            </p>
        </div>
    </div>
</template>

<script>
    export default {
        name:'my-marquee',
        props:{
            sendVal:Array
        },
        data() {
            return {
                isShow:true,
                interval:null,
            }
        },
        methods:{
            start(){
                let _this = this;
                let moveTarget = this.$refs.movebox;
                let outbox = this.$refs.outbox;

                // console.log(outbox.offsetHeight , (moveTarget.offsetHeight);
                //
                if(outbox.offsetHeight > (moveTarget.offsetHeight /2)){
                    // this.isShow = false;
                    // return
                    // 生成重复数据
                    let old_val = this.sendVal;
                    for(let i in 2){
                        for(let x in old_val){
                            this.sendVal.push(old_val[x]);
                        }
                    }
                }
                let initTop = 0;
                this.interval = setInterval(function(){
                    initTop ++;
                    console.log(initTop);
                    if(initTop >= moveTarget.offsetHeight / 3 ){
                        initTop = 0;
                    }
                    moveTarget.style = 'margin-top:-'+initTop+'px';
                    if(initTop==0) _this.stop();
                },30)
            },
            stop(){
                clearInterval(this.interval);
                this.interval = null;
            }
        },
        mounted:function(){
            this.start();
        },
    }
</script>

<style lang="scss" scoped>
    .my-outbox{
        color: #000;
        height: 309px;
        margin: 0px 8px;
        background: #FEE7B1;
        overflow: hidden;
        border: 2px solid #C46302;
        text-align: center;

        .my-listbox{
            line-height: 26px;
        }
    }
</style>
