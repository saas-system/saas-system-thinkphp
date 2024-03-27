import createAxios from '/@/utils/axios'

const controllerUrl = 'admin/tenant.Tenant/'
const controllerConfigUrl = 'admin/tenant.Config/'

export const actionUrl = new Map([
    ['getTenantConfig', controllerUrl + 'getTenantConfig'],
    ['clearData', controllerUrl + 'clearData'],
    ['initData', controllerUrl + 'initData'],
    ['exportTenant', controllerUrl + 'exportTenant'], // 导出租户
])

export function getTenantConfigApi(id: number) {
    return createAxios(
        {
            url: actionUrl.get('getTenantConfig'),
            method: 'get',
            params: {
                id: id
            }
        },
        {
            showSuccessMessage: false,
        }
    )
}

export function clearDataApi(id: number) {
    return createAxios(
        {
            url: actionUrl.get('clearData'),
            method: 'post',
            data: {
                id: id
            }
        },
        {
            showSuccessMessage: true
        }
    )
}

export function initDataApi(id: number) {
    return createAxios(
        {
            url: actionUrl.get('initData'),
            method: 'post',
            data: {
                id: id
            }
        },
        {
            showSuccessMessage: true
        }
    )
}

/**
 * 更新租户配置
 */
export function updateTenantConfig(data: object) {
    return createAxios(
        {
            url: controllerConfigUrl +'edit',
            method: 'post',
            data: data
        },
        {
            showSuccessMessage: true,
        }
    )
}

// 导出租户列表
export function exportTenantApi(data: anyObj, fileName = '') {
    return createAxios(
        {
            url: actionUrl.get('exportTenant'),
            method: 'get',
            params: data,
            responseType: 'blob',
        },
        {
            loading: false,
            showSuccessMessage: false,
            fileName,
        }
    )
}
