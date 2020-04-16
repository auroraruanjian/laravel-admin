<template>
    <div class="page page_login">

        <div class="main">
<!--            <div class="logo"><img src="" /></div>-->
            <div class="loginMain">
                <div class="form-input-item">
                    <label><van-icon name="manager" /></label>
                    <input placeholder="请输入账号" type="text" v-model="loginForm.username" autocomplete="off"/>
                </div>
                <div class="form-input-item">
                    <label><van-icon name="lock" /></label>
                    <input placeholder="请输入密码" type="password" v-model="loginForm.password" autocomplete="new-password"/>
                </div>
                <div class="form-text-item">
                    <van-checkbox shape="square" @change="rememberPassword" v-model="remember">记住密码</van-checkbox>
                </div>
                <div class="form-btn-item">
                    <van-button type="primary" size="large" @click="normalLogin">登录</van-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { getlocalStorage, setlocalStorage } from '@/utils/common.js';

    export default {
        name: "login",
        data() {
            return {
                typing: false,
                isLoading: false,
                captchaUrl: '/captcha?' + Math.random(),
                loginForm: {
                    username: '',
                    password: '',
                    captcha: '',
                    code: '',
                    real_name: ''
                },
                remember:false,
            }
        },
        created(){
            this.loginForm.username = getlocalStorage('username','');
            this.loginForm.password = getlocalStorage('remember_password','');
            let remember = getlocalStorage('remember', '');
            if (remember !== '') {
                this.rememberpw = remember;
            }
        },
        methods:{
            rememberPassword(){
                setlocalStorage('remember', this.rememberpw);
            },
            refreshCaptcha(){
                this.captchaUrl = '/captcha?' + Math.random();
                this.loginForm.captcha = '';
            },
            normalLogin() {
                if (this.loginForm.username == '') {
                    this.$root.alertMessage('请输入用户名！', '提示', 'warning');
                    return false;
                }
                if (this.loginForm.password == '') {
                    this.$root.alertMessage('请输入密码！', '提示', 'warning');
                    return false;
                }
                this.onSubmit();
                //this.dialogFormVisible = true;
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
                        _this.$notify({ type: 'danger', message: response.data.msg ,duration:1000});
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
                        console.log(err);
                        errMsg = err.response.data.msg;
                    }
                    this.$root.alertMessage(errMsg,'提示',{type:'error'});
                });
            },
            validateInput (input) {
                if (input && input.length >= 2 && input.length <= 6) {
                    return true;
                } else return false;
            },
        }
    }
</script>

<style lang="scss">
    .page_login{
        background: url('/img/m/login_bg_s.jpg') no-repeat center top;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
        &.hasApptip{
            top: 0;
        }
        .main {
            position: relative;
            background: rgba(0, 0, 0, 0.4);
            padding: 14.25px;
            border: 5.25px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            min-width: 70%;
            max-width: 80%;
            margin-top:-100px;
            .logo {
                position: absolute;
                left: 50%;
                transform: translate3d(-50%,-100%,0);
                top: -20px;
                height: 80px;
                img {
                    height: 100%;
                    display: block;
                }
            }
            .loginMain{
                margin-top: 8px;
            }
            .form-text-item {
                color: white;
                margin-bottom: 15px;
                font-size: 14px;
                display: flex;
                justify-content: flex-end;
            }
            .van-checkbox__label{
                color: white;
            }
            .form-btn-item{
                margin-bottom: 15px;
                display: flex;
                justify-content: space-between;
                >button{
                    margin-right: 10px;
                    &:last-child{
                        margin-right: 0;
                    }
                }
            }
            .form-input-item {
                height: 41.25px;
                display: flex;
                align-items: center;
                padding: 0 11.25px;
                background-color: #fcfbfb;
                margin-bottom: 15px;
                border-radius: 5px;
                .captcha{
                    display: flex;
                    align-items: center;
                    width: 80px;
                    height: 100%;
                    margin-left: 10px;
                    img{
                        width: 100%;
                        display: block;
                    }
                }
                label {
                    position: relative;
                    white-space: nowrap;
                    width: 28px;
                    i {
                        color: #a0a0a0;
                        font-size: 18.75px;
                        margin-top: 5px;
                    }
                }
                input {
                    border: none;
                    padding: 11.25px 0;
                    margin: 0;
                    outline: none;
                    background-color: transparent;
                    font-size: 15px;
                    width: 150px;
                    flex: 1;
                }
            }
            .loginBox {
                display: flex;
                flex-direction: column;
                justify-content: space-around;
                box-sizing: border-box;
                width: 100%;
                margin: 0 0 8px 0;
                button{
                    &:active:before{
                        opacity: 0.2;
                    }
                    &:not(:last-child){
                        margin-bottom: 15px;
                    }
                }
            }
        }
    }
</style>
