<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
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
                    <FormItem :label="t('tenant.config.tenant_id')" type="remoteSelect" v-model="baTable.form.items!.tenant_id" prop="tenant_id" :input-attr="{ pk: 'tenant.id', field: 'name', 'remote-url': '/admin/tenant.Tenant/index' }" :placeholder="t('Please select field', { field: t('tenant.config.tenant_id') })" />
                    <FormItem :label="t('tenant.config.mini_app_id')" type="string" v-model="baTable.form.items!.mini_app_id" prop="mini_app_id" :placeholder="t('Please input field', { field: t('tenant.config.mini_app_id') })" />
                    <FormItem :label="t('tenant.config.mini_secret_id')" type="string" v-model="baTable.form.items!.mini_secret_id" prop="mini_secret_id" :placeholder="t('Please input field', { field: t('tenant.config.mini_secret_id') })" />
                    <FormItem :label="t('tenant.config.uni_app_id')" type="string" v-model="baTable.form.items!.uni_app_id" prop="uni_app_id" :placeholder="t('Please input field', { field: t('tenant.config.uni_app_id') })" />
                    <FormItem :label="t('tenant.config.tenant_pre')" type="string" v-model="baTable.form.items!.tenant_pre" prop="tenant_pre" :placeholder="t('Please input field', { field: t('tenant.config.tenant_pre') })" />
                    <FormItem :label="t('tenant.config.number_pre')" type="number" prop="number_pre" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.number_pre" :placeholder="t('Please input field', { field: t('tenant.config.number_pre') })" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
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

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    tenant_id: [buildValidatorData({ name: 'select', title: t('tenant.config.tenant_id') })],
    mini_app_id: [buildValidatorData({ name: 'required', title: t('tenant.config.mini_app_id') })],
    mini_secret_id: [buildValidatorData({ name: 'required', title: t('tenant.config.mini_secret_id') })],
    uni_app_id: [buildValidatorData({ name: 'required', title: t('tenant.config.uni_app_id') })],
    tenant_pre: [buildValidatorData({ name: 'required', title: t('tenant.config.tenant_pre') })],
    number_pre: [buildValidatorData({ name: 'required', title: t('tenant.config.number_pre') })],
})
</script>

<style scoped lang="scss"></style>
