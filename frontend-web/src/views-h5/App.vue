<template>
    <div id="app">
        <router-view/>
    </div>
</template>

<script>
</script>

<style lang="scss">
    @import '../assets/css/reset.css';
    @import '../assets/css/animate.css';
    @import '../../node_modules/normalize.css/normalize.css';

    $m_t1_text_color:#777;

    html{
        font-size: 37.5px;
    }
    body{
        font-family: 'PingFang SC', -apple-system, 'Helvetica Neue', 'Helvetica', 'Arial', 'Lucida Grande', sans-serif;
        font-weight: 400;
        color: $m_t1_text_color;
        font-size: 15px;
    }
    .page {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        background-color: #e41b47;
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
