<template>
    <div class="default-main">
        <el-row :gutter="30">
            <el-col class="lg-mb-20" :xs="24" :sm="24" :md="24" :lg="10">
                <div class="admin-info">
                    <el-upload
                        class="avatar-uploader"
                        action=""
                        :show-file-list="false"
                        @change="onAvatarBeforeUpload"
                        :auto-upload="false"
                        accept="image/gif, image/jpg, image/jpeg, image/bmp, image/png, image/webp">
                        <el-image :src="state.tenantInfo.logo" class="avatar">
                            <template #error>
                                <div class="image-slot">
                                    <Icon size="30" color="#c0c4cc" name="el-icon-Picture" />
                                </div>
                            </template>
                        </el-image>
                    </el-upload>
                    <div class="admin-info-base">
                        <div class="admin-nickname">{{ state.tenantInfo.name }}</div>
                    </div>
                    <div class="unlock-code" v-if="state.tenantInfo.unlock_code">
                        TVAPP解锁码：<span>{{ state.tenantInfo.unlock_code || '' }}</span>
                    </div>

                    <div class="admin-info-form">
                        <el-form
                            @keyup.enter="onSubmit(formRef)"
                            :key="state.formKey"
                            label-position="top"
                            :rules="rules"
                            ref="formRef"
                            :model="state.tenantInfo">

                            <el-form-item :label="t('routine.tenantInfo.short_name')" prop="short_name">
                                <el-input disabled
                                          :placeholder="t('Please input field', { field: t('routine.tenantInfo.short_name') })"
                                          v-model="state.tenantInfo.short_name"
                                ></el-input>
                            </el-form-item>
                            <el-form-item :label="t('routine.tenantInfo.full_address')" prop="full_address">
                                <el-input disabled v-model="state.tenantInfo.full_address"></el-input>
                            </el-form-item>

                            <el-form-item :label="t('routine.tenantInfo.expire_time_text')" prop="expire_time_text">
                                <el-input disabled :placeholder="t('routine.tenantInfo.expire_time_text')" v-model="state.tenantInfo.expire_time_text"></el-input>
                            </el-form-item>

                            <el-form-item :label="t('routine.tenantInfo.mobile')" prop="mobile">
                                <el-input disabled
                                          :placeholder="t('Please input field', { field: t('routine.tenantInfo.mobile') })"
                                          v-model="state.tenantInfo.mobile"
                                ></el-input>
                            </el-form-item>

                            <el-form-item :label="t('routine.tenantInfo.contact_name')" prop="contact_name">
                                <el-input
                                    :placeholder="t('Please input field', { field: t('routine.tenantInfo.contact_name') })"
                                    v-model="state.tenantInfo.contact_name"
                                ></el-input>
                            </el-form-item>

                            <el-form-item>
                                <el-button type="primary" :loading="state.buttonLoading" @click="onSubmit(formRef)">{{
                                    t('routine.tenantInfo.Save changes')
                                }}</el-button>
<!--                                <el-button @click="onResetForm(formRef)">{{ t('Reset') }}</el-button>-->
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </el-col>

        </el-row>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { index,postData } from '/@/api/tenant/routine/TenantInfo'
import { ElForm, FormItemRule } from 'element-plus'
import { onResetForm } from '/@/utils/common'
import { uuid } from '../../../utils/random'
import { buildValidatorData } from '/@/utils/validate'
import { fileUpload } from '/@/api/common'
import { useTenantAdminInfo } from '/@/stores/tenantAdminInfo'
import { timeFormat } from '/@/components/table'

const { t } = useI18n()
const formRef = ref<InstanceType<typeof ElForm>>()

const adminInfoStore = useTenantAdminInfo()

const state: {
    tenantInfo: anyObj
    formKey: string
    buttonLoading: boolean
} = reactive({
    tenantInfo: {},
    formKey: uuid(),
    buttonLoading: false,
})

index().then((res) => {
    state.tenantInfo = res.data.info
    // 重新渲染表单以记录初始值
    state.formKey = uuid()
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({

})

const onAvatarBeforeUpload = (file: any) => {
    let fd = new FormData()
    fd.append('file', file.raw)
    fileUpload(fd).then((res) => {
        if (res.code == 1) {
            postData({
                id: state.tenantInfo.id,
                logo: res.data.file.url,
            }).then(() => {
                state.tenantInfo.logo = res.data.file.full_url
            })
        }
    })
}

const onSubmit = (formEl: InstanceType<typeof ElForm> | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            let data = { ...state.tenantInfo }
            delete data.name
            delete data.logo
            delete data.mobile
            state.buttonLoading = true
            postData(data)
                .then(() => {
                    state.buttonLoading = false
                })
                .catch(() => {
                    state.buttonLoading = false
                })
        }
    })
}
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'routine/tenantInfo',
})
</script>

<style scoped lang="scss">
.admin-info {
    background-color: var(--ba-bg-color-overlay);
    border-radius: var(--el-border-radius-base);
    border-top: 3px solid #409eff;
    .avatar-uploader {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 60px auto 10px auto;
        border-radius: 50%;
        box-shadow: var(--el-box-shadow-light);
        border: 1px dashed var(--el-border-color);
        cursor: pointer;
        overflow: hidden;
        width: 110px;
        height: 110px;
    }
    .avatar-uploader:hover {
        border-color: var(--el-color-primary);
    }
    .avatar {
        width: 110px;
        height: 110px;
        display: block;
    }
    .image-slot {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
    .admin-info-base {
        .admin-nickname {
            font-size: 22px;
            color: var(--el-text-color-primary);
            text-align: center;
            padding: 8px 0;
        }
        .admin-other {
            color: var(--el-text-color-regular);
            font-size: 14px;
            text-align: center;
            line-height: 20px;
        }
    }
    .admin-info-form {
        padding: 30px;
    }

    .unlock-code {
        margin-top: 10px;
        font-size: 16px;
        display: flex;
        padding: 0 10px;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        color: var(--el-text-color-regular);

        span {
            font-weight: bold;
            color: var(--el-color-primary);
        }
    }
}
.el-card :deep(.el-timeline-item__icon) {
    font-size: 10px;
}
@media screen and (max-width: 1200px) {
    .lg-mb-20 {
        margin-bottom: 20px;
    }
}
</style>
