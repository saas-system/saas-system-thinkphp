<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['config'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                租户配置 - {{ baTable.form.extend.tenant_name }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem v-show="false" :label="'租户'" type="string" v-model="baTable.form.items!.tenant_id" prop="tenant_id" :placeholder="t('Please input field', { field: '租户' })"/>
                    <FormItem :label="'微信小程序ID'" type="string" v-model="baTable.form.items!.mini_app_id" prop="mini_app_id" :placeholder="t('Please input field', { field: '微信小程序ID' })"/>
                    <FormItem :label="'微信小程序秘钥'" type="string" v-model="baTable.form.items!.mini_secret_id" prop="mini_secret_id" :placeholder="t('Please input field', { field: '微信小程序秘钥' })"/>
                    <FormItem :label="'小程序二维码'" type="image" v-model="baTable.form.items!.mini_logo" prop="mini_logo" />
                    <FormItem :label="'租户前缀'" type="string" v-model="baTable.form.items!.tenant_pre" prop="tenant_pre" :placeholder="t('Please input field', { field: '租户前缀' })"/>
                    <FormItem :label="'卡号前缀'" type="number" prop="number_pre" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.number_pre" :placeholder="t('Please input field', { field: '卡号前缀' })"/>
                    <FormItem label="过期提醒人员" type="remoteSelects" v-model="baTable.form.items!.remind_admin_ids" prop="remind_admin_ids"
                              :input-attr="{ pk: 'tenant_admin.id', field: 'nickname', 'remote-url': 'admin/tenant.Admin/index',disabled:false, params: { tenant_id:baTable.form.items!.tenant_id}}"
                              :placeholder="t('Please select field', { field: '管理员' })" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="submitForm(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElForm, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { updateTenantConfig } from "/@/api/backend/tenant/tenant";

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    tenant_id: [buildValidatorData({ name: 'required', title: '租户' })],
    mini_app_id: [buildValidatorData({ name: 'required', title: '微信小程序ID' })],
    mini_secret_id: [buildValidatorData({ name: 'required', title: '微信小程序秘钥' })],
    tenant_pre: [buildValidatorData({ name: 'required', title: '租户前缀' })],
    number_pre: [buildValidatorData({ name: 'required', title: '卡号前缀' })],
})


/**
 * 提交表单
 * @param formEl 表单组件ref
 */
const submitForm = (formEl: InstanceType<typeof ElForm> | undefined = undefined) => {
    const submitCallback = () => {
        baTable.form.submitLoading = true
        // 更新租户配置
        updateTenantConfig(baTable.form.items || {}).then(() => {
            baTable.toggleForm('')
        }).finally(() => {
            baTable.form.submitLoading = false
        })
    }

    if (formEl) {
        baTable.form.ref = formEl
        formEl.validate((valid) => {
            if (valid) {
                submitCallback()
            }
        })
    } else {
        submitCallback()
    }
}

</script>

<style scoped lang="scss"></style>
