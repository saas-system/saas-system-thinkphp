<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate ? true : false"
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
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem
                        :label="t('sms.template.title')"
                        type="string"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :input-attr="{ placeholder: t('Please input field', { field: t('sms.template.title') }) }"
                    />
                    <FormItem
                        :label="t('sms.template.code')"
                        type="string"
                        v-model="baTable.form.items!.code"
                        prop="code"
                        :attr="{
                            blockHelp: '可在业务代码中使用唯一标识调取本模板发送短信',
                        }"
                        :input-attr="{
                            placeholder: t('Please input field', { field: t('sms.template.code') }),
                        }"
                    />
                    <FormItem
                        :label="t('sms.template.content')"
                        type="textarea"
                        v-model="baTable.form.items!.content"
                        prop="content"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :attr="{
                            blockHelp: '可使用模板变量：${var_name}',
                        }"
                        :input-attr="{ placeholder: '有的服务商需要使用短信内容来发送短信，请按需填写' }"
                    />
                    <FormItem
                        :label="t('sms.template.template')"
                        type="string"
                        v-model="baTable.form.items!.template"
                        prop="template"
                        :attr="{
                            blockHelp: '有的服务商需要使用模板ID来发送短信，请按需填写',
                        }"
                        :input-attr="{ placeholder: t('Please input field', { field: t('sms.template.template') }) }"
                    />
                    <FormItem
                        :label="t('sms.template.variables')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.variables"
                        prop="variables"
                        :input-attr="{
                            multiple: true,
                            field: 'title',
                            'remote-url': '/admin/sms.variable/index',
                            placeholder: t('Please select field', { field: t('sms.template.variables') }),
                        }"
                        :attr="{
                            blockHelp: '您可以完整的填写短信内容，也可以直接在此选择模板使用到的变量',
                        }"
                    />
                    <FormItem
                        :label="t('State')"
                        v-model="baTable.form.items!.status"
                        type="radio"
                        :data="{ content: { '0': t('Disable'), '1': t('Enable') }, childrenAttr: { border: true } }"
                    />
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
    code: [buildValidatorData({ name: 'required', title: t('sms.template.code') })],
})
</script>

<style scoped lang="scss"></style>
