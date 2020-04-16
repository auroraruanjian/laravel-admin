<template>
    <div id="app">
        <router-view ></router-view>
    </div>
</template>

<script>
    export default {
        name: "app-m"
    }
</script>


<style lang="scss">
    $m_t1_text_color:#777;

    body{
        font-family: 'PingFang SC', -apple-system, 'Helvetica Neue', 'Helvetica', 'Arial', 'Lucida Grande', sans-serif;
        font-weight: 400;
        color: $m_t1_text_color;
        font-size: 15px;
    }
    .page {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        background-color: #FFFFFF;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        overflow: hidden;
        overflow-y: scroll;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        &::-webkit-scrollbar {
            display: none;
        }
    }
</style>
