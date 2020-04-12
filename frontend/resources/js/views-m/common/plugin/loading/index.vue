<template>
    <transition
    enter-active-class="animated fadeIn fasterX"
    leave-active-class="animated fadeOut fasterX">
        <div class="loadingMask" :style="style" v-show="show" @click="callback">
            <v-ons-progress-circular v-show="circular" indeterminate></v-ons-progress-circular>
            <span v-show="text">{{ text }}</span>
        </div>
    </transition>
</template>

<script>
    export default {
        data() {
            return {
                circular:true,
                text:'',
                show:false,
                callback:()=>{},
                style:{
                    backgroundColor:'rgba(255,255,255,.6)',
                }
            }
        }
    }
</script>
<style lang="scss">
    .loadingMask{
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 1;
        >span{
            padding-top: .15rem;
        }
    }
    ons-splitter-content > .loadingMask{
        z-index: 10002;
    }
</style>
