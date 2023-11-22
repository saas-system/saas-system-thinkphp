<template>
    <div class="login">
        <div class="login-box">
            <div class="word">
                <div class="logo_text_1">欢迎使用</div>
                <div class="logo_text_2">后台管理系统</div>
            </div>
            <div class="content">
                <el-form @keyup.enter="onSubmitPre()" ref="formRef" :rules="rules" size="large" :model="form">
                    <div class="login-title">平台端 - 登录</div>
                    <div class="form-item-title"><span>*</span> 账号</div>
                    <el-form-item prop="username">
                        <el-input
                            ref="usernameRef"
                            type="text"
                            clearable
                            v-model="form.username"
                            :placeholder="t('login.Please enter an account')"
                        />
                    </el-form-item>
                    <div class="form-item-title"><span>*</span> 密码</div>
                    <el-form-item prop="password">
                        <el-input
                            ref="passwordRef"
                            v-model="form.password"
                            type="password"
                            :placeholder="t('login.Please input a password')"
                            show-password
                        />
                    </el-form-item>
                    <el-checkbox v-model="form.keep" :label="t('login.Hold session')" size="default"></el-checkbox>
                    <el-form-item>
                        <el-button
                            :loading="state.submitLoading"
                            class="submit-button"
                            round
                            type="primary"
                            size="large"
                            @click="onSubmitPre()"
                        >
                            {{ t('login.Sign in') }}
                        </el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onBeforeUnmount, reactive, ref, nextTick } from 'vue'
import type { FormInstance, InputInstance } from 'element-plus'
import { useI18n } from 'vue-i18n'
import { useConfig } from '/@/stores/config'
import { useAdminInfo } from '/@/stores/adminInfo'
import { login } from '/@/api/backend'
import { uuid } from '/@/utils/random'
import { buildValidatorData } from '/@/utils/validate'
import router from '/@/router'
import clickCaptcha from '/@/components/clickCaptcha'
import toggleDark from "/@/utils/useDark"
let timer: number

const config = useConfig()
const adminInfo = useAdminInfo()
toggleDark(config.layout.isDark)

const formRef = ref<FormInstance>()
const usernameRef = ref<InputInstance>()
const passwordRef = ref<InputInstance>()
const state = reactive({
    showCaptcha: false,
    submitLoading: false,
})
const form = reactive({
    username: '',
    password: '',
    keep: false,
    captchaId: uuid(),
    captchaInfo: '',
})

const { t } = useI18n()

// 表单验证规则
const rules = reactive({
    username: [buildValidatorData({
        name: 'required',
        message: t('login.Please enter an account')
    }), buildValidatorData({ name: 'account' })],
    password: [buildValidatorData({
        name: 'required',
        message: t('login.Please input a password')
    }), buildValidatorData({ name: 'password' })],
})

const focusInput = () => {
    if (form.username === '') {
        usernameRef.value!.focus()
    } else if (form.password === '') {
        passwordRef.value!.focus()
    }
}

onMounted(() => {

    login('get')
        .then((res) => {
            state.showCaptcha = res.data.captcha
            nextTick(() => focusInput())
        })
        .catch((err) => {
            console.log(err)
        })
})

onBeforeUnmount(() => {
    clearTimeout(timer)
})

const onSubmitPre = () => {
    formRef.value?.validate((valid) => {
        if (valid) {
            if (state.showCaptcha) {
                clickCaptcha(form.captchaId, (captchaInfo: string) => onSubmit(captchaInfo))
            } else {
                onSubmit()
            }
        }
    })
}

const onSubmit = (captchaInfo = '') => {
    state.submitLoading = true
    form.captchaInfo = captchaInfo
    login('post', form)
        .then((res) => {
            adminInfo.dataFill(res.data.userInfo)
            router.push({ path: res.data.routePath })
        })
        .finally(() => {
            state.submitLoading = false
        })
}
</script>

<style scoped lang="scss">
.login {
    display: flex;
    width: 100vw;
    height: 100vh;
    min-width: 1100px;
    align-items: center;
    justify-content: center;
    background: url(/@/assets/login-bg.png) no-repeat center center;
    background-size: 100% 100%;

    .login-box {
        padding: 0;
        display: flex;
        height: 500px;
        justify-content: space-between;
        background: rgba(0, 113, 188, 0.2);

        .word {
            width: 460px;
            color: #ffffff;
            padding: 80px 40px;

            .logo_img {
                width: 117px;
                height: 117px;
            }

            .logo_text_1 {
                padding-top: 260px;
                font-size: 30px;
                line-height: 30px;
                font-weight: bold;
            }

            .logo_text_2 {
                font-size: 40px;
                padding-top: 20px;
                line-height: 50px;
                font-weight: bold;
            }
        }

        .content {
            width: 580px;
            padding: 80px 100px 60px 80px;
            background: #ffffff;

            .login-title {
                font-size: 30px;
                line-height: 30px;
                margin-bottom: 36px;
            }

            .form-item-title {
                color: #333333;
                font-size: 14px;
                line-height: 14px;
                margin-bottom: 14px;

                span {
                    color: #ff0000;
                }
            }

            .submit-button {
                width: 100%;
                font-size: 16px;
                margin-top: 15px;
                letter-spacing: 6px;
                border-radius: var(--el-input-border-radius, var(--el-border-radius-base));
                --el-button-bg-color: var(--el-color-primary);
            }
        }
    }
}
</style>
