<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info"
                  show-icon/>

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('user.user.quick Search Fields') })">
        </TableHeader>
        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef"/>

        <!-- 表单 -->
        <PopupForm/>

    </div>
</template>

<script setup lang="ts">
import {ref, provide, onMounted, reactive} from 'vue'
import baTableClass from '/@/utils/baTable'
import {defaultOptButtons} from '/@/components/table'
import {baTableApi} from '/@/api/common'
import {useI18n} from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import {auth} from "/@/utils/common";
import {ElMessageBox} from "element-plus";
import {useTenantAdminInfo} from "/@/stores/tenantAdminInfo";

const {t} = useI18n()
const tableRef = ref()
let optButtons = defaultOptButtons(['edit'])

const state: {
    census: any[],
    type: string,
    date: string,
} = reactive({
    census: [],
    type: 'all',
    date: '',
})

const defaultTime: [Date, Date] = [
    new Date(2000, 1, 1, 0, 0, 0),
    new Date(2000, 1, 1, 23, 59, 59),
]

// 自定义一个新的按钮
let newButton: OptButton[] = [
]

optButtons = newButton.concat(optButtons)

const baTable = new baTableClass(
    new baTableApi('/tenant/user.User/'),
    {
        pk: 'id',
        column: [
            {label: t('user.user.id'), prop: 'id', align: 'center', width: 70, operator: '=', sortable: 'custom'},
            {
                label: t('user.user.is_virtual'),
                width: 100,
                prop: 'is_virtual',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                replaceValue: {0: t('user.user.is_virtual 0'), 1: t('user.user.is_virtual 1')}
            },
            {
                label: t('user.user.nickname'),
                width: 120,
                prop: 'nickname',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {label: t('user.user.avatar'), prop: 'avatar', align: 'center', render: 'image', operator: false},
            // { label: t('user.user.real_name'), prop: 'real_name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: t('user.user.gender'),
                prop: 'gender',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                replaceValue: {0: t('user.user.gender 0'), 1: t('user.user.gender 1'), 2: t('user.user.gender 2')}
            },
            {
                label: t('user.user.mobile'),
                width: 140,
                prop: 'mobile',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {
                label: t('user.user.id_card'),
                width: 180,
                prop: 'id_card',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {
                label: t('user.user.address'),
                width: 200,
                prop: 'address',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false
            },
            {
                label: t('user.user.last_login_ip'),
                show: false,
                prop: 'last_login_ip',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false
            },
            {
                label: t('user.user.last_login_time'),
                prop: 'last_login_time',
                align: 'center',
                render: 'datetime',
                operator: false,
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss'
            },
            {
                label: t('user.user.last_login_ip_addr'),
                show: false,
                prop: 'last_login_ip_addr',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false
            },
            {
                label: t('user.user.register_ip'),
                show: false,
                prop: 'register_ip',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false
            },
            {
                label: t('user.user.register_origin'),
                show: false,
                prop: 'register_origin',
                align: 'center',
                operator: false,
                sortable: false,
                replaceValue: {
                    1: t('user.user.register_origin 1'),
                    2: t('user.user.register_origin 2'),
                    3: t('user.user.register_origin 3')
                }
            },
            {
                label: t('user.user.register_ip_addr'),
                show: false,
                prop: 'register_ip_addr',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false
            },
            {
                label: t('user.user.platform'),
                show: false,
                prop: 'platform',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false
            },
            {
                label: t('user.user.openid'),
                show: false,
                prop: 'openid',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false
            },
            {
                label: t('user.user.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss'
            },
            {
                label: t('Operate'),
                align: 'center',
                width: 120,
                render: 'buttons',
                buttons: optButtons,
                operator: false,
                fixed: 'right'
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {card_number: '', gender: '0', integral: 0, competitive_point: 0, master_score: 0, status: '1'},
    },
    {
        getIndex() {
        }
    }
)

provide('baTable', baTable)
provide('userTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})

// 自定义筛选统计
const searchDate = (type: string = 'all', date: string = '') => {
    state.type = type;
}

// 选定时间
const changeDate = (date: any = '') => {
}

// 限定时间
const today = new Date();
const maxDate = (date: anyObj) => {
    return (date.getTime() > today.getTime())
}

</script>

<script lang="ts">
import {defineComponent} from 'vue'

export default defineComponent({
    name: 'user/user',
})
</script>

<style scoped lang="scss">
.statistics {
    width: 100%;
    height: 50px;
    margin: 5px 0;
    display: flex;
    background: #ffffff;
    align-items: center;
    justify-content: space-between;

    .statistics_search {
        display: none;
        margin-left: 15px;
        align-items: center;
    }

    .tableCensus {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-end;

        .census_item {
            height: 100%;
            display: flex;
            color: #333333;
            margin: 0 20px;
            font-size: 16px;
            font-weight: bold;
            line-height: 24px;
            align-items: center;
        }
    }
}
</style>
