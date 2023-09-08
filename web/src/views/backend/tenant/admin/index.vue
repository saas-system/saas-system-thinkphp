<template>
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        @open="openTable"
        :model-value="['admin'].includes(tenantTable.form.operate!)"
        @close="tenantTable.form.operate = ''"
        width="80%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                管理员管理 - {{ tenantTable.form!.items!.name }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon/>

            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh','add','comSearch', 'quickSearch', 'columnDisplay']"
                :quick-search-placeholder="t('Quick search placeholder', { fields: '手机号' })"
            />

            <!-- 表格 -->
            <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
            <Table ref="tableRef"/>

            <!-- 表单 -->
            <PopupForm/>
        </el-scrollbar>
    </el-dialog>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, inject } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { TableColumnCtx } from "element-plus/es/components/table/src/table-column/defaults";

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['edit'])
const tenantTable = inject('tenantTable') as baTableClass

const baTable = new baTableClass(
    new baTableApi('/admin/tenant.Admin/'),
    {
        pk: 'id',
        column: [
            { label: 'ID', prop: 'id', align: 'center', width: 70, operator: false, sortable: 'custom' },
            {
                label: '租户',
                prop: 'tenant_id',
                render: 'tags',
                align: 'center',
                comSearchRender: 'remoteSelect',
                remote: {
                    // 主键，下拉 value
                    pk: 'tenant.id',
                    // 字段，下拉 label
                    field: 'name',
                    // 远程接口URL
                    remoteUrl: '/admin/tenant.Tenant/index',
                    // 额外的请求参数
                    params: {},
                },
                renderFormatter: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    return row.tenant ? row.tenant.name : '';
                }
            },
            { label: '用户名', prop: 'username', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: '昵称', prop: 'nickname', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: '手机号', prop: 'mobile', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: '头像', prop: 'avatar', align: 'center', render: 'image', operator: false },
            { label: '邮箱', prop: 'email', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: '登录失败次数', prop: 'login_failure', align: 'center', operator: 'RANGE', sortable: false },
            { label: '登录时间', prop: 'last_login_time', align: 'center', render: 'datetime', operator: false, sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: '登录IP', prop: 'last_login_ip', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: '状态', prop: 'status', align: 'center', render: 'switch', operator: '=', sortable: false, replaceValue: { 1: '启用', 0: '禁用' } },
            { label: '创建时间', prop: 'createtime', align: 'center', render: 'datetime', operator: false, sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false }
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { status: '1' },
    }
)

provide('baTable', baTable)

const openTable = () => {
    baTable.table.ref = tableRef.value
    baTable.form.extend.tenant_id = tenantTable.form.items!.id
    baTable.initComSearch({ 'tenant_id': tenantTable.form.items!.id })
    // 获取表格数据
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
}
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'tenant/Admin',
})
</script>

<style scoped lang="scss"></style>
