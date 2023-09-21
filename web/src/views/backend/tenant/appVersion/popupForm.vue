<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        @open="openForm"
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
                    <FormItem label="版本名称" type="string" v-model="baTable.form.items!.version" prop="version" :placeholder="t('Please input field', { field: '版本名称' })" />
                    <FormItem label="版本号" type="number" prop="version_code" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.version_code" :placeholder="t('Please input field', { field: '版本号' })" />
                    <FormItem label="包大小" type="string" v-model="baTable.form.items!.size" prop="size" :placeholder="t('Please input field', { field: '包大小' })" />
                    <FormItem label="更新内容" type="textarea" v-model="baTable.form.items!.content" prop="content" :placeholder="t('Please input field', { field: '更新内容' })" />
                    <FormItem label="下载地址" type="file" v-model="baTable.form.items!.url" prop="url" :placeholder="t('Please input field', { field: '下载地址' })" />
                    <FormItem label="强制更新" type="switch" v-model="baTable.form.items!.enforce" prop="enforce" :data="{ content: { 1: '是', 0: '否' } }"/>
                    <FormItem label="状态" type="switch" v-model="baTable.form.items!.status" prop="status" :data="{ content: { 1: '启用', 0: '停用' } }"/>
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
    version: [buildValidatorData({ name: 'required', title: '版本名称' })],
    version_code: [buildValidatorData({ name: 'required', title: '版本号' })],
    size: [buildValidatorData({ name: 'required', title: '包大小' })],
    content: [buildValidatorData({ name: 'required', title: '更新内容' })],
    url: [buildValidatorData({ name: 'upload', title: '下载地址' })],
    enforce: [buildValidatorData({ name: 'required', title: '强制更新' })],
    status: [buildValidatorData({ name: 'required', title: '状态' })],
})

const openForm = () => {
}
</script>

<style scoped lang="scss"></style>
