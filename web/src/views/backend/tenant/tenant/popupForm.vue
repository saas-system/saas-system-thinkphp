<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @open="onDialog"
        @close="baTable.toggleForm"
        width="700px"
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
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem :label="t('tenant.tenant.name')" type="string" v-model="baTable.form.items!.name" prop="name" :placeholder="t('Please input field', { field: t('tenant.tenant.name') })" />
                    <FormItem :label="t('tenant.tenant.short_name')" type="string" v-model="baTable.form.items!.short_name" prop="short_name" :placeholder="t('Please input field', { field: t('tenant.tenant.short_name') })" />
                    <FormItem :label="t('tenant.tenant.contact_name')" type="string" v-model="baTable.form.items!.contact_name" prop="contact_name" :placeholder="t('Please input field', { field: t('tenant.tenant.contact_name') })" />
                    <FormItem :label="t('tenant.tenant.mobile')" type="number" prop="mobile" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.mobile" :placeholder="t('Please input field', { field: t('tenant.tenant.mobile') })" />
                    <FormItem :label="t('tenant.tenant.logo')" type="image" v-model="baTable.form.items!.logo" prop="logo" />
                    <FormItem :label="t('tenant.tenant.area_name')" type="city" v-model="baTable.form.items!.area_ids"  prop="area_ids" />
                    <FormItem :label="t('tenant.tenant.address')" type="string" v-model="baTable.form.items!.address" prop="address" :placeholder="t('Please input field', { field: t('tenant.tenant.address') })" />
                    <FormItem :label="t('tenant.tenant.expire_time')" v-if="baTable.form.operate=='Add'" type="date" :input-attr="{shortcuts: addShortcuts, format: 'YYYY-MM-DD'}" v-model="baTable.form.items!.expire_time" prop="expire_time" :placeholder="t('Please select field', { field: t('tenant.tenant.expire_time') })" />
                    <FormItem :label="t('tenant.tenant.expire_time')" v-else-if="baTable.form.operate=='Edit'" type="date" :input-attr="{onFocus: expireTimeFocus, shortcuts: editShortcuts, format: 'YYYY-MM-DD'}" v-model="baTable.form.items!.expire_time" prop="expire_time" :placeholder="t('Please select field', { field: t('tenant.tenant.expire_time') })" />
                    <FormItem :label="t('tenant.tenant.memo')" type="textarea" v-model="baTable.form.items!.memo" prop="memo" :placeholder="t('Please input field', { field: '备注' })" />
                    <FormItem :label="t('tenant.tenant.status')" type="radio" v-model="baTable.form.items!.status" prop="status" :data="{ content: { 0: t('tenant.tenant.status 0'), 1: t('tenant.tenant.status 1') } }" :placeholder="t('Please select field', { field: t('tenant.tenant.status') })" />
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

const state = reactive({
    count: 0,
    expire_time: null,
})

// 打开重置
const onDialog = () => {
    state.count = 0;
    state.expire_time = null;
}

const expireTimeFocus = () => {
    if (state.count > 0) {
        return false
    }
    state.count++;
    state.expire_time = baTable.form.items!.expire_time
}

// 增加日期的快捷方式
const addShortcuts = [
    {
        text: '今天',
        value: new Date(),
    },
    {
        text: '一年后',
        value: () => {
            const date = new Date()
            date.setFullYear(date.getFullYear() + 1)
            return date
        },
    },
    {
        text: '一个月后',
        value: () => {
            const date = new Date()
            date.setMonth(date.getMonth() + 1)
            return date
        },
    }
]

// 编辑日期的快捷方式
const editShortcuts = [
    ...addShortcuts,
    {
        text: '延期一年',
        value: () => {
            let date = state.expire_time && new Date(state.expire_time).getTime() > new Date().getTime() ? new Date(state.expire_time) : new Date()
            date.setFullYear(date.getFullYear() + 1)
            return date
        }
    },
    {
        text: '恢复默认',
        value: () => {
            const time = state.expire_time;
            return time ? new Date(time) : new Date()
        }
    }
]

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('tenant.tenant.name') })],
    contact_name: [buildValidatorData({ name: 'required', title: t('tenant.tenant.contact_name') })],
    mobile: [buildValidatorData({ name: 'required', title: t('tenant.tenant.mobile') })],
    address: [buildValidatorData({ name: 'required', title: t('tenant.tenant.address') })],
    area_ids: [buildValidatorData({ name: 'required', title: t('tenant.tenant.area_name') })],
    expire_time: [buildValidatorData({ name: 'date', title: t('tenant.tenant.expire_time') }),buildValidatorData({ name: 'required', title: t('tenant.tenant.expire_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('tenant.tenant.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('tenant.tenant.update_time') })],
})


</script>

<style scoped lang="scss"></style>
