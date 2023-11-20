<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :destroy-on-close="true"
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
                    <FormItem label="租户" type="remoteSelect" v-model="baTable.form.items!.tenant_id" prop="tenant_id" :input-attr="{ pk: 'tenant.id', field: 'name', 'remote-url': 'admin/tenant.Tenant/index',disabled: true }" :placeholder="t('Please select field', { field: '租户' })" />
                    <FormItem
                        label="角色组"
                        v-model="baTable.form.items!.group_arr"
                        prop="group_arr"
                        type="remoteSelect"
                        :key="('group-' + baTable.form.items!.id)"
                        :input-attr="{
                            multiple: true,
                            params: { isTree: true },
                            field: 'name',
                            'remote-url': 'admin/tenant.Group/index?tenant_id=' + baTable.form.items!.tenant_id
                        }"
                    />
                    <FormItem label="用户名" type="string" v-model="baTable.form.items!.username" prop="username" :placeholder="t('Please input field', { field: '用户名' })" />
                    <FormItem label="手机号" type="string" v-model="baTable.form.items!.mobile" prop="mobile" :placeholder="t('Please input field', { field: '手机号' })" />
                    <FormItem label="昵称" type="string" v-model="baTable.form.items!.nickname" prop="nickname" :placeholder="t('Please input field', { field: '昵称' })" />
                    <FormItem label="密码" type="string" v-model="baTable.form.items!.password" prop="password"  placeholder="默认密码为123456，为空则不修改" />
                    <FormItem label="头像" type="image" v-model="baTable.form.items!.avatar" prop="avatar"/>
                    <FormItem label="邮箱" type="string" v-model="baTable.form.items!.email" prop="email" :placeholder="t('Please input field', { field: '邮箱' })" />
                    <FormItem label="状态" type="radio" v-model="baTable.form.items!.status" prop="status" :data="{ content: { 1: '启用', 0: '禁用' } }" />
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
    tenant_id: [buildValidatorData({ name: 'select', title: '租户' })],
    nickname: [buildValidatorData({ name: 'required', title: '昵称' })],
    username: [buildValidatorData({ name: 'required', title: '用户名' })],
    mobile: [buildValidatorData({ name: 'mobile', title: '手机号' })],
})

const openForm = () => {
    baTable.form.items!.tenant_id = baTable.form.extend.tenant_id
}
</script>

<style scoped lang="scss"></style>
