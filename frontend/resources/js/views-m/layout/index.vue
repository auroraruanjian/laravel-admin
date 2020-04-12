<template>
    <div class="page page_layout">
        <transition name="slide-right">
            <router-view
                class="routeTransition"
                :key="$route.path">
            </router-view>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "index",
        computed: {
        },
        data(){
            return {};
        },
        created(){
            console.log('layout');
        }
    }
</script>

<style lang="scss">
    body{
        min-width: 100vw;
        margin: 0;
        overflow-x: auto;
        color: #323233;
        font-size: 16px;
        font-family: PingFang SC, 'Helvetica Neue', Arial, sans-serif;
        background-color: #f7f8fa;
        -webkit-font-smoothing: antialiased
    }
</style>
