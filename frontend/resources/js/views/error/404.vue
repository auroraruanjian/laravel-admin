<template>
    <div class="page_404">
        <div class="con">
            <div class="logo">
                <img src="/template/jubo/images/logo2.png">
            </div>
            <div class="other">
                <span><em>ERROR:404 </em> 很抱歉，您要访问的页面不存在！</span>
                <span><small>请检查您输入的网址是否正确，请点击以下按钮返回主页或者上一页</small></span>
                <div>
                    <el-button @click="$router.push('/')" type="primary">返回首页</el-button>
                    <el-button @click="$router.go(-1)">返回上一页</el-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        computed: {
        },
        created() {
            this.$store.commit('SET_APPBLUE', false);
        },
        methods: {
        }
    };
</script>

<style lang="scss">
    .page_404{
        min-width: 1280px;
        background: url('/template/jubo/images/layoutbg.jpg') repeat;
        height: 100%;
        display: flex;
        justify-content: center;
        .con{
            margin-top: 150px;
            .logo img{
                width: 200px;
            }
            .other{
                margin-top: 20px;
                display: flex;
                flex-direction: column;
                >span{
                    color: #333;
                    font-size: 18px;
                    em{
                        font-size: 38px;
                    }
                    small{
                        font-size: 15px; 
                        opacity: .7;
                    }
                    &+span{
                        margin-top: 10px;
                    }
                }
                >div{
                    margin-top: 30px;
                }
            }
        }
    }
</style>
