<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('sms.variable.quick Search Fields') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, h, resolveComponent, markRaw } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { getVar } from '/@/api/backend/sms/variable'
import { ElMessageBox } from 'element-plus'

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['edit', 'delete'])

const renderValue = {
    render(context: TableRenderPublicInstance) {
        if (context.$attrs.renderValue) {
            return h('span', context.$attrs.renderValue)
        }
        const calcVar = () => {
            getVar(context.$attrs.renderRow['id']).then((res) => {
                ElMessageBox.alert(res.data, '计算结果', {
                    confirmButtonText: '确定',
                })
            })
        }
        return h(resolveComponent('el-link'), { type: 'primary', onClick: calcVar }, () => '点击计算')
    },
}
const baTable = new baTableClass(
    new baTableApi('/admin/sms.variable/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('sms.variable.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('sms.variable.title'), prop: 'title', align: 'center' },
            { label: t('sms.variable.name'), prop: 'name', align: 'center' },
            {
                label: t('sms.variable.value_source'),
                prop: 'value_source',
                align: 'center',
                render: 'tag',
                replaceValue: {
                    literal: t('sms.variable.value_source literal'),
                    func: t('sms.variable.value_source func'),
                    sql: t('sms.variable.value_source sql'),
                },
            },
            {
                label: t('sms.variable.value'),
                prop: 'value',
                align: 'center',
                render: 'customRender',
                customRender: markRaw(renderValue),
                showOverflowTooltip: true,
            },
            { label: t('sms.variable.sql'), prop: 'sql', align: 'center', showOverflowTooltip: true },
            { label: t('sms.variable.namespace'), prop: 'namespace', align: 'center', showOverflowTooltip: true },
            { label: t('sms.variable.class'), prop: 'class', align: 'center' },
            { label: t('sms.variable.func'), prop: 'func', align: 'center' },
            { label: t('sms.variable.param'), prop: 'param', align: 'center' },
            {
                label: t('State'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { '0': 'danger', '1': 'success' },
                replaceValue: { '0': t('Disable'), '1': t('Enable') },
            },
            {
                label: t('sms.variable.updatetime'),
                prop: 'updatetime',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            {
                label: t('sms.variable.createtime'),
                prop: 'createtime',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: { value_source: 'literal', status: '1' },
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
    name: 'sms/variable',
})
</script>

<style scoped lang="scss"></style>
