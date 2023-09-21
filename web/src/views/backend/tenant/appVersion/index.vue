<template>

    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon/>

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: 'ID' })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef"/>

        <!-- 表单 -->
        <PopupForm/>
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

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/tenant.appVersion/'),
    {
        pk: 'id',
        column: [
            { label: 'ID', prop: 'id', align: 'center', width: 70, operator: '=', sortable: 'custom' },
            { label: '版本名称', prop: 'version', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: '版本号',
                prop: 'version_code',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            { label: '包大小', prop: 'size', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: '更新内容',
                prop: 'content',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                show: false
            },
            { label: '下载地址', prop: 'url', render: 'url', align: 'center', operator: false, sortable: false },
            {
                label: '强制更新',
                prop: 'enforce',
                align: 'center',
                render: 'switch',
                operator: '=',
                sortable: false,
                replaceValue: { 1: '是', 0: '否' }
            },
            {
                label: '状态',
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: '=',
                sortable: false,
                replaceValue: { 1: '启用', 0: '停用' }
            },
            {
                label: '添加时间',
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss'
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { version_code: null, enforce: 0, status: 1 },
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
    name: 'tenant/appVersion',
})
</script>

<style scoped lang="scss"></style>
