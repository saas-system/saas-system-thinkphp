<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('sms.template.quick Search Fields') })"
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
import { ElMessageBox } from 'element-plus'
import { sendSms } from '/@/api/common'

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['edit', 'delete'])
optButtons.push({
    render: 'tipButton',
    name: 'test_send',
    title: 'sms.template.test_send',
    text: '',
    type: 'success',
    icon: 'el-icon-MagicStick',
    class: 'table-row-test',
    disabledTip: false,
    click: (row: TableRow, filed: TableColumn) => {
        ElMessageBox.prompt(t('sms.template.Please enter the receiver mobile'), t('sms.template.test_send'), {
            confirmButtonText: t('确认发送'),
            cancelButtonText: t('Cancel'),
            inputPattern: /^1\d{10}$/,
            inputErrorMessage: t('sms.template.Please enter the correct mobile number'),
            beforeClose: (action, instance, done) => {
                if (action === 'confirm') {
                    instance.confirmButtonLoading = true
                    instance.confirmButtonText = t('发送中')
                    sendSms(instance.inputValue, row['code'])
                        .then(() => {
                            done()
                        })
                        .catch(() => {
                            done()
                        })
                } else {
                    done()
                }
            },
        })
    },
})

const baTable = new baTableClass(
    new baTableApi('/admin/sms.template/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('sms.template.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('sms.template.title'), prop: 'title', align: 'center', operator: 'LIKE' },
            { label: t('sms.template.code'), prop: 'code', align: 'center', operator: 'LIKE' },
            { label: t('sms.template.template'), prop: 'template', align: 'center' },
            { label: t('sms.template.content'), prop: 'content', align: 'center', showOverflowTooltip: true, operator: 'LIKE' },
            { label: t('sms.template.variables'), prop: 'variable_text', render: 'tags', operator: false, align: 'center' },
            {
                label: t('State'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { '0': 'danger', '1': 'success' },
                replaceValue: { '0': t('Disable'), '1': t('Enable') },
            },
            {
                label: t('sms.template.updatetime'),
                prop: 'updatetime',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            {
                label: t('sms.template.createtime'),
                prop: 'createtime',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: { status: '1' },
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
    name: 'sms/template',
})
</script>

<style scoped lang="scss"></style>
