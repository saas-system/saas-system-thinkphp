<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info"
                  show-icon/>

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('common.slide.quick Search Fields') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef"/>

        <!-- 表单 -->
        <PopupForm/>
    </div>
</template>

<script setup lang="ts">
import {ref, provide, onMounted} from 'vue'
import baTableClass from '/@/utils/baTable'
import {defaultOptButtons} from '/@/components/table'
import {baTableApi} from '/@/api/common'
import {useI18n} from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import {TableColumnCtx} from 'element-plus/es/components/table/src/table-column/defaults'


const {t} = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['weigh-sort', 'edit', 'delete'])
const baTable = new baTableClass(
    new baTableApi('/tenant/common.Slide/'),
    {
        pk: 'id',
        column: [
            {type: 'selection', align: 'center', operator: false},
            {label: t('common.slide.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom'},
            {
                label: t('common.slide.category'),
                prop: 'category_id',
                render: 'tags',
                align: 'center',
                comSearchRender: 'remoteSelect',
                remote: {
                    // 主键，下拉 value
                    pk: 'id',
                    // 字段，下拉 label
                    field: 'name',
                    // 远程接口URL
                    remoteUrl: '/tenant/common.SlideCategory/index',
                    // 额外的请求参数
                    params: {},
                },
                renderFormatter: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    return row.category.name;
                }
            },
            {
                label: t('common.slide.position'),
                prop: 'position',
                align: 'center',
                render: 'tag',
                sortable: false,
                replaceValue: {1: '商城页', 2: '比赛页', 3: '比赛中'}
            },
            {
                label: t('common.slide.title'),
                prop: 'title',
                width: 160,
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {label: t('common.slide.image'), prop: 'image', align: 'center', render: 'image', operator: false},
            {
                label: t('common.slide.link'),
                prop: 'link',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {
                label: t('common.slide.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                sortable: false,
                replaceValue: {1: t('common.slide.status 1'), 0: t('common.slide.status 0')}
            },
            {label: t('common.slide.weigh'), prop: 'weigh', align: 'center', operator: false, sortable: 'custom'},
            {
                label: t('common.slide.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss'
            },
            {label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false},
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: {prop: 'weigh', order: 'desc'},
    },
    {
        defaultItems: {status: 1, weigh: 0, delete_time: null},
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
import {defineComponent} from 'vue'

export default defineComponent({
    name: 'common/slide',
})
</script>

<style scoped lang="scss"></style>
