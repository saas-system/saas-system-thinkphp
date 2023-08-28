<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('tenant.config.quick Search Fields') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
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
const baTable = new baTableClass(
    new baTableApi('/admin/tenant.Config/'),
    {
        pk: 'id',
        column: [
            { label: t('tenant.config.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('tenant.config.tenant_id'),
                prop: 'tenant_id',
                render: 'tags',
                align: 'center',
                comSearchRender: 'remoteSelect',
                remote: {
                    // 主键，下拉 value
                    pk: 'id',
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
            { label: t('tenant.config.mini_app_id'), prop: 'mini_app_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('tenant.config.mini_secret_id'), prop: 'mini_secret_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('tenant.config.uni_app_id'), prop: 'uni_app_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('tenant.config.tenant_pre'), prop: 'tenant_pre', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('tenant.config.number_pre'), prop: 'number_pre', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('tenant.config.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { number_pre: 88 },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'tenant/config',
})
</script>

<style scoped lang="scss"></style>
