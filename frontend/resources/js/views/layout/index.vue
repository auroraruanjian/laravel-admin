<template>
    <div class="app-wrapper">
        <section class="app-main">
            <transition name="fade-transform" mode="out-in">
                <router-view :key="key" />
            </transition>
        </section>
    </div>
</template>

<script>
    export default {
        name: "index",
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

<style scoped>

</style>
