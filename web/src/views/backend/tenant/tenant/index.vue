<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info"
                  show-icon/>

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('tenant.tenant.quick Search Fields') })"
        >
            <el-button v-if="auth('exportTenant')" v-blur class="table-header-operate" type="primary" @click="handleExportTenant">
                导出租户
            </el-button>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef"/>

        <!-- 表单 -->
        <PopupForm/>

        <!-- 配置 -->
        <ConfigForm/>

        <!-- 管理员 -->
        <Admin/>

    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, reactive, inject } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import ConfigForm from './configForm.vue'
import Admin from '../admin/index.vue'

import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { ElMessageBox } from "element-plus";
import { clearDataApi, initDataApi, getTenantConfigApi, exportTenantApi } from "/@/api/backend/tenant/tenant";
import { auth } from "/@/utils/common";
import { useAdminInfo } from '/@/stores/adminInfo'

const { t } = useI18n()
const tableRef = ref()
const adminInfo = useAdminInfo()
let optButtons = defaultOptButtons(['edit'])
const state = reactive({
    province_id: '',
    city_id: '',
})

// 自定义一个新的按钮
let newButton: OptButton[] = [
    {
        // 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮
        render: 'tipButton',
        // 按钮名称
        name: 'config',
        // 鼠标放置时的 title 提示
        title: t('tenant.tenant.config'),
        // 直接在按钮内显示的文字，title 有值时可为空
        text: t('tenant.tenant.config'),
        // 按钮类型，请参考 element plus 的按钮类型
        type: 'primary',
        // 按钮 icon
        icon: '',
        class: 'table-row-info',
        // tipButton 禁用 tip
        disabledTip: false,
        // 按钮是否显示，请返回布尔值
        display: (row: TableRow, field: TableColumn) => {
            return auth('index', '/platform/tenant/config')
        },
        // 自定义点击事件
        click: (row: TableRow, field: TableColumn) => {
            if (!row) return
            baTable.form.loading = true
            getTenantConfigApi(row.id).then((res) => {
                if (res.data) {
                    baTable.form!.items = res.data
                } else {
                    baTable.form!.items = { tenant_id: row.id };
                }
                baTable.form.extend = { tenant_name: row.name }
                baTable.form.loading = false
                baTable.form.operate = 'config'
            })

        }
    }, {
        // 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮
        render: 'tipButton',
        // 按钮名称
        name: 'admin',
        // 鼠标放置时的 title 提示
        title: t('tenant.tenant.admin'),
        // 直接在按钮内显示的文字，title 有值时可为空
        text: t('tenant.tenant.admin'),
        // 按钮类型，请参考 element plus 的按钮类型
        type: 'primary',
        // 按钮 icon
        icon: '',
        class: 'table-row-info',
        // tipButton 禁用 tip
        disabledTip: false,
        // 按钮是否显示，请返回布尔值
        display: (row: TableRow, field: TableColumn) => {
            return auth('index', '/platform/tenant/admin')
        },
        // 自定义点击事件
        click: (row: TableRow, field: TableColumn) => {
            if (!row) return
            baTable.form.operate = 'admin'
            baTable.form!.items = row
        }
    }, {
        // 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮
        render: 'tipButton',
        // 按钮名称
        name: 'clear',
        // 鼠标放置时的 title 提示
        title: t('tenant.tenant.clear_data'),
        // 直接在按钮内显示的文字，title 有值时可为空
        text: t('tenant.tenant.clear_data'),
        // 按钮类型，请参考 element plus 的按钮类型
        type: 'danger',
        // 按钮 icon
        icon: '',
        class: 'table-row-info',
        // tipButton 禁用 tip
        disabledTip: false,
        display: (row: TableRow, field: TableColumn) => {
            return adminInfo.super && adminInfo.debug;
        },
        // 自定义点击事件
        click: (row: TableRow, field: TableColumn) => {
            if (!row) return
            baTable.form.items = row;
            clearDataDialog();
        }
    }, {
        // 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮
        render: 'tipButton',
        // 按钮名称
        name: 'init',
        // 鼠标放置时的 title 提示
        title: t('tenant.tenant.init_data'),
        // 直接在按钮内显示的文字，title 有值时可为空
        text: t('tenant.tenant.init_data'),
        // 按钮类型，请参考 element plus 的按钮类型
        type: 'danger',
        // 按钮 icon
        icon: '',
        class: 'table-row-info',
        // tipButton 禁用 tip
        disabledTip: false,
        display: (row: TableRow, field: TableColumn) => {
            return adminInfo.super && adminInfo.debug;
        },
        // 自定义点击事件
        click: (row: TableRow, field: TableColumn) => {
            if (!row) return
            baTable.form.items = row;
            initDataDialog();
        }
    }
]

optButtons = newButton.concat(optButtons)

const baTable = new baTableClass(
    new baTableApi('/admin/tenant.Tenant/'),
    {
        pk: 'id',
        column: [
            {
                label: t('tenant.tenant.id'),
                prop: 'id',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                width: 200,
                show: false
            },
            {
                label: t('tenant.tenant.name'),
                prop: 'name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                width: 160
            },
            {
                label: t('tenant.tenant.short_name'),
                prop: 'short_name',
                align: 'center',
                width: 120,
                show: false,
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {
                label: t('tenant.tenant.contact_name'),
                prop: 'contact_name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {
                label: t('tenant.tenant.mobile'),
                prop: 'mobile',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                width: 140
            },
            { label: t('tenant.tenant.logo'), prop: 'logo', align: 'center', render: 'image', width: 80, operator: false },
            { label: t('tenant.tenant.mini_logo'), prop: 'config.mini_logo', align: 'center', render: 'image', width: 120, operator: false },
            {
                label: t('tenant.tenant.province_id'),
                prop: 'province_id',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: '=',
                render: 'tags',
                show: false,
                renderFormatter: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    return row.province ? row.province.name : '';
                },
                comSearchRender: 'remoteSelect',
                remote: {
                    // 主键，下拉 value
                    pk: 'id',
                    // 字段，下拉 label
                    field: 'name',
                    remoteUrl: '/admin/ajax/getAreaList',
                    // 额外的请求参数
                    params: {},
                }
            },
            {
                label: t('tenant.tenant.city_id'),
                prop: 'city_id',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                render: 'tags',
                show: false,
                renderFormatter: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    return row.city ? row.city.name : '';
                },
                comSearchRender: 'remoteSelect',
                remote: {
                    // 主键，下拉 value
                    pk: 'id',
                    // 字段，下拉 label
                    field: 'name',
                    remoteUrl: '/admin/ajax/getAreaList',
                    // 额外的请求参数
                    params: {
                        province: state.province_id,
                    },
                }
            },
            {
                label: t('tenant.tenant.district_id'),
                prop: 'district_id',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                render: 'tags',
                show: false,
                renderFormatter: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    return row.district ? row.district.name : '';
                },
                comSearchRender: 'remoteSelect',
                remote: {
                    // 主键，下拉 value
                    pk: 'id',
                    // 字段，下拉 label
                    field: 'name',
                    remoteUrl: '/admin/ajax/getAreaList',
                    // 额外的请求参数
                    params: {
                        province: state.province_id,
                        city: state.city_id,
                    },
                }
            },
            {
                label: t('tenant.tenant.address'),
                prop: 'address',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                show: false
            },
            {
                label: t('tenant.tenant.tenant_pre'),
                prop: 'config.tenant_pre',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false,
                show: true
            },
            {
                label: t('tenant.tenant.status'),
                prop: 'status',
                align: 'center',
                operator: '=',
                sortable: false,
                render: 'customTemplate',
                comSearchRender: 'select',
                replaceValue: { 0: t('tenant.tenant.status 0'), 1: t('tenant.tenant.status 1') },
                customTemplate: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    let str = '';
                    let color = '#409eff';
                    switch (parseInt(value)) {
                        case 0:
                            color = '#f56c6c';
                            str = t('tenant.tenant.status 0');
                            break;
                        case 1:
                            color = '#409eff';
                            str = t('tenant.tenant.status 1');
                            break;
                    }

                    return `<span style="color:${color};">${str}</span>`;
                }
            },
            {
                label: t('tenant.tenant.expire_time'),
                prop: 'expire_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd'
            },
            {
                label: t('tenant.tenant.create_time'),
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
                "min-width": 550,
                render: 'buttons',
                buttons: optButtons,
                operator: false,
                fixed: 'right'
            },
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'create_time', order: 'desc' },
    },
    {
        defaultItems: { phone: 0, status: '1', expire_time: null },
    }
)

provide('baTable', baTable)
provide('tenantTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})

const clearDataDialog = () => {
    ElMessageBox.prompt('正在危险操作，请在下方输入"确认清除"后，点击清除！', '清除数据 - ' + baTable.form.items.name, {
        confirmButtonText: '清除',
        cancelButtonText: '取消',
        inputPattern: /^\u786e\u8ba4\u6e05\u9664$/,
        inputErrorMessage: '请按要求输入！',
    }).then(({ value }) => {
        clearDataApi(baTable.form.items.id).then((res) => {
            if (res.code === 1) {
                baTable.form.items = {};
            }
        })
    }).catch(() => {
        baTable.form.items = {};
    })
}

const initDataDialog = () => {
    ElMessageBox.prompt('正在危险操作，请在下方输入"确认初始化"后，点击初始化！', '初始化数据 - ' + baTable.form.items.name, {
        confirmButtonText: '初始化',
        cancelButtonText: '取消',
        inputPattern: /^\u786e\u8ba4\u521d\u59cb\u5316$/,
        inputErrorMessage: '请按要求输入！',
    }).then(({ value }) => {
        initDataApi(baTable.form.items.id).then((res) => {
            if (res.code === 1) {
                baTable.form.items = {};
            }
        })
    }).catch(() => {
        baTable.form.items = {};
    })
}

const handleExportTenant = () => {
    baTable.form.submitLoading = true
    let fileName = '导出租户 - ' + currentTime() + '.xlsx'
    exportTenantApi(baTable.table!.filter, fileName).then(() => {
        baTable.form.submitLoading = false
    })
}

/**
 * 获取当前时间
 */
function currentTime(): string {
    let date = new Date();
    let year = date.getFullYear();
    let month = date.getMonth();
    let dateArr = [
        date.getMonth() + 1,
        date.getDate(),
        date.getHours(),
        date.getMinutes(),
        date.getSeconds(),
    ];
    //如果格式是MM则需要此步骤，如果是M格式则此循环注释掉
    for (let i = 0; i < dateArr.length; i++) {
        if (dateArr[i] >= 1 && dateArr[i] <= 9) {
            dateArr[i] = "0" + dateArr[i];
        }
    }
    return year + "" + dateArr[0] + "" + dateArr[1] + "" + dateArr[2] + "" + dateArr[3] + "" + dateArr[4];
}
</script>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'tenant/tenant',
})
</script>

<style scoped lang="scss"></style>
