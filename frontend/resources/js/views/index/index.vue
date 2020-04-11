<template>
    <div class="page_index bgn">
        <div class="container index-container">
            aaaa
        </div>
    </div>
</template>

<script>
    import { mapGetters,mapState } from 'vuex'

    export default {
        name: "index",
        data(){
            return {};
        },
        computed: {
            ...mapGetters([
                'username',
            ]),
            ...mapState({
                nickname: state => state.user.nickname,
            }),
        },
        created(){
            console.log('rrunnnnn');
        }
    }
</script>

<style scoped>

</style>
