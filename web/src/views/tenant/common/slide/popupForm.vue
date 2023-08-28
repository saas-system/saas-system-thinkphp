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
                    <FormItem :label="t('common.slide.category')" type="remoteSelect" v-model="baTable.form.items!.category_id" prop="category_id" :input-attr="{ pk: 'id', field: 'name', 'remote-url': '/tenant/common.SlideCategory/index' }" :placeholder="t('Please select field', { field: t('common.slide.category') })" />
                    <FormItem
                        :label="t('common.slide.position')"
                        type="radio"
                        v-model="baTable.form.items!.position"
                        :input-attr="{ size: 'large' }"
                        :data="{ childrenAttr: { border: true }, content: { 1: '商城页', 2: '赛事页', 3: '比赛中' } }"
                    />
                    <FormItem :label="t('common.slide.title')" type="string" v-model="baTable.form.items!.title" prop="title" :placeholder="t('Please input field', { field: t('common.slide.title') })" />
                    <FormItem :label="t('common.slide.image')" type="image" v-model="baTable.form.items!.image" prop="image" />
                    <FormItem :label="t('common.slide.link')" type="string" v-model="baTable.form.items!.link" prop="link" :placeholder="t('Please input field', { field: t('common.slide.link') })" />
                    <FormItem v-if="baTable.form.operate != 'add'" :label="t('common.slide.weigh')" type="number" prop="weigh" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.weigh" :placeholder="t('Please input field', { field: t('common.slide.weigh') })" />
                    <FormItem :label="t('common.slide.status')" type="switch" v-model="baTable.form.items!.status" prop="status" :input-attr="{ size: 'large' }" />
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
    category_id: [buildValidatorData({ name: 'select', title: t('common.slide.category') })],
    title: [buildValidatorData({ name: 'required', title: t('common.slide.title') })],
    image: [buildValidatorData({ name: 'upload', title: t('common.slide.image') })],
})
</script>

<style scoped lang="scss"></style>
