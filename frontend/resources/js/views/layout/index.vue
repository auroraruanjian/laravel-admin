<template>
    <div class="app-wrapper layout">
        <toolbar ></toolbar>

        <section class="app-main">
            <transition name="fade-transform" mode="out-in">
                <router-view :key="key" />
            </transition>
        </section>
    </div>
</template>

<script>
    import toolbar from './toolbar.vue';

    export default {
        name: "index",
        components: {
            toolbar,
        },
        computed: {
            key() {
                return this.$route.path
            }
        },
        data(){
            return {};
        },
        created(){
            console.log('layout');
        }
    }
</script>

<style lang="scss" >
    .layout {
        min-height: 100%;
        min-width: 1240px;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .container {
        width: 1200px;
        margin: 0 auto;
    }
</style>
