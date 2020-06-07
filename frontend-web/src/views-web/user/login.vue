<template>
    <div class="page_login singlePage">
        <div class="login_main">
            <div class="lform">
                <el-form :model="loginForm" ref="loginForm" label-width="0" @submit.native.prevent>
                    <div class="inputbox">
                        <el-form-item prop="username">
                            <img class="imgcion" src="/img/ico/ico-user.png">
                            <el-input class="transparent noborder" placeholder="请输入用户名" v-model="loginForm.username" clearable></el-input>
                        </el-form-item>
                        <el-form-item prop="password">
                            <img class="imgcion" src="/img/ico/ico-lock.png">
                            <el-input class="transparent noborder" placeholder="请输入密码" v-model="loginForm.password" type="password" clearable></el-input>
                        </el-form-item>
                        <el-form-item>
                            <div class="gouxuanbox">
                                <div><el-checkbox v-model="rememberpw" @change="rememberPassword">记住密码</el-checkbox></div>
                            </div>
                        </el-form-item>
                        <el-form-item class="loginbtn">
                            <el-button native-type="submit" @click="onSubmit" type="primary" :loading="loading" round>立即登录</el-button>
                        </el-form-item>
                    </div>
                </el-form>
            </div>
            <div class="lfbottom">
                <span>建议使用1366*768以上分辨率，并使用谷歌浏览器、火狐浏览器、IE9.0等高速浏览器浏览本站</span>
                <img src="/img/ico/loginbico.png">
            </div>
        </div>
    </div>
</template>

<script>
    import { getlocalStorage, setlocalStorage } from '@/utils/common.js';

    export default {
        components: {
        },
        data() {
            return {
                loading:false,
                captchaUrl: '/captcha?' + Math.random(),
                loginForm: {
                    username: '',
                    password: '',
                    captcha: '',
                    code: '',
                    real_name:''
                },
                rules:{},
                rememberpw:true,
            };
        },
        created(){
            this.loginForm.username = getlocalStorage('username','');
            this.loginForm.password = getlocalStorage('remember_password','');
            let remember = getlocalStorage('remember', '');
            if (remember !== '') {
                this.rememberpw = remember;
            }
        },
        watch:{
        },
        methods: {
            rememberPassword(){
                setlocalStorage('remember', this.rememberpw);
            },
            refreshCaptcha(){
                this.captchaUrl = '/captcha?' + Math.random();
                this.loginForm.captcha = '';
            },
            onSubmit() {
                let username = this.loginForm.username;
                let password = this.rememberpw ? this.loginForm.password : '';
                this.loading = true;
                let _this = this;
                this.$store.dispatch('user/LoginByUsername', this.loginForm).then(response => {
                    this.loading = false;
                    if (response.data.code == 1) {
                        setlocalStorage('username',username);
                        setlocalStorage('remember_password',password);

                        _this.$router.push({ path: '/' });
                    } else {
                        this.refreshCaptcha();
                        this.$alert(response.data.msg,'提示',{type:'error'});
                    }
                }).catch(err => {
                    this.loading = false;
                    this.loginForm.code = '';
                    this.refreshCaptcha();
                    let errMsg = '用户名或密码不正确！';
                    try {
                        if (err.response.data.errors.captcha) {
                            errMsg = '验证码不正确！';
                        } else if (err.response.data.errors.username) {
                            errMsg = err.response.data.errors.username[0];
                        }
                    } catch (e) {
                        errMsg = JSON.stringify(err.response.data);
                    }
                    this.$alert(errMsg,'提示',{type:'error'});
                });
            },
            validateInput (input) {
                if (input && input.length >= 2 && input.length <= 6) {
                    return true;
                } else return false;
            },
        }
    };
</script>

<style lang="scss">
    .page_login{
        min-width: 1200px;
        background: url(/img/index-bg.jpg) no-repeat fixed;
        background-size: 100% 100%;
        position: relative;
        -moz-user-select: none;
        -webkit-user-select: none;
        -o-user-select: none;
        -ms-user-select: none;
        user-select: none;
        height: 100%;
        min-height: 750px;
        .login_main{
            height: 100vh;
            position: relative;
            .lform{
                padding-top: 170px;
                margin: 0 auto;
                width: 560px;
                text-align: center;
                .el-form{
                    overflow: hidden;
                    width: 100%;
                    height: 370px;
                    padding: 50px 90px 0px 90px;
                    box-sizing: border-box;
                    margin: 0 auto;
                    border-radius: 5px;
                    background: rgba(255,255,255,0.9);;
                    position: relative;
                    -webkit-box-shadow: 1px 1px 8px rgba(0,0,0,.33), -5px -5px 25px rgba(0,0,0,.33);
                    box-shadow: 1px 1px 8px rgba(0,0,0,.33), -5px -5px 25px rgba(0,0,0,.33);
                }
                .imgcion{
                    position: absolute;
                    z-index: 1;
                    top: 50%;
                    left: 15px;
                    transform: translateY(-50%);
                }
                .el-input__inner{
                    color: #fff;
                }
                >span{
                    display: block;
                    margin: 0 auto 25px;
                    width: 360px;
                    height: 110px;
                    img{
                        width: 100%;
                        display: block;
                    }
                }
                .verificationCodeImage{
                    position: absolute;
                    top: 50%;
                    right: 0;
                    z-index: 1;
                    transform: translateY(-50%);
                    background-color: #fff;
                    border-radius: 6px;
                    display: flex;
                    align-items: center;
                    width: 75px;
                    height: 30px;
                    cursor: pointer;
                    img{
                        width: 100%;
                        display: block;
                    }
                }
                .inputbox{
                    .el-form-item{
                        margin-bottom: 15px;
                    }
                    .el-input{
                        input{
                            padding-left: 40px;
                            width: 100%;
                            height: 50px;
                            border: 1px solid #bab9bd;
                            font-size: 16px;
                            color: #000;
                        }
                        &.hasvc{
                            input{
                                padding-right: 110px;
                            }
                            .el-input__suffix{
                                right: 80px;
                            }
                        }
                    }
                    .el-input__suffix{
                        .el-input__clear{
                            font-size: 20px;
                        }

                    }
                }
                .btnbox{
                    padding-top: 10px;
                    display: flex;
                    > span{
                        flex: 1;
                        &:nth-child(2){
                            margin: 0 5px;
                        }
                    }
                    button{
                        width: 100%;
                        height: 36px;
                        margin: 0 auto 10px;
                        padding: 0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #ffffff;
                        background:#3F94B3;
                        background: -webkit-gradient(linear, left top, left bottom, from(#3F94B3), to(#4EAEC5));
                        background: linear-gradient(#3F94B3, #4EAEC5);
                        font-size: 14px;
                        border: none;
                        &:hover{
                            background-color: #3F94B3;
                            background: -webkit-gradient(linear, left top, left bottom, from(#4EAEC5), to(#3F94B3));
                            background: linear-gradient(#4EAEC5, #3F94B3);
                        }
                        >span{
                            display: flex;
                            align-items: center;
                            img{
                                padding-right: 5px;
                            }
                        }
                    }
                }
                .gouxuanbox{
                    display: flex;
                    justify-content: space-between;
                    line-height: 25px;
                    .el-checkbox{
                        color: inherit;
                        .el-checkbox__label{
                            color: #000000 !important;
                        }
                        .el-checkbox__input.is-focus .el-checkbox__inner{
                            border-color: #F4586E;
                        }
                        .el-checkbox__input.is-checked .el-checkbox__inner{
                            background-color: #F4586E;
                            border-color: #F4586E;
                        }
                    }
                    >div>em{
                        cursor: pointer;
                        color: #000000;
                        &:hover{
                            color: #F4586E;
                        }
                    }
                }
                .loginbtn{
                    text-align: right;
                    button{
                        width: 100%;
                        height: 50px;
                        color: white;
                        font-weight: 400;
                        font-size: 18px;
                        letter-spacing: 3px;
                        border-radius: 5px;
                        background-color: #F35671;
                        background: -webkit-gradient(linear, left top, left bottom, from(#F35671), to(#D2364C));
                        background: linear-gradient(#F35671, #D2364C);
                        border:none;
                        &:active,&:hover{
                            background-color: rgba(#D2364C,.8);
                            background: -webkit-gradient(linear, left top, left bottom, from(#D2364C), to(#F35671));
                            background: linear-gradient(#D2364C, #F35671);
                            border:none;
                        }
                        &.is-loading:before{
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                        }
                    }
                }
            }
            .lfbottom{
                color: #fff;
                font-size: 14px;
                margin-top: 35px;
                text-align: center;
                margin: 0 auto;
                position: fixed;
                bottom: 50px;
                min-width: 1200px;
                left: 0;
                width: 100%;
                span{
                    display: block;
                    color: #565555;
                }
                img{
                    display: inline-block;
                    margin: 15px 0;
                }
            }

            @media screen and (max-height: 800px) {
                .lform{
                    margin-bottom: 60px;
                }
                .lfbottom{
                    position: relative;
                }
            }

        }
    }
    .login_protocol_dialog{
        .el-dialog__header {
            background: transparent;
            height: auto;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 20px 20px 10px 20px;
            .el-dialog__title,
            .el-dialog__close {
                color: inherit;
            }
        }
        .el-dialog__body{
            padding: 10px 20px;
        }
        .el-dialog__footer {
            text-align: right;
        }
    }
    .login_download_dialog{
        .el-tab-pane{
            text-align: center;
        }
    }
</style>
