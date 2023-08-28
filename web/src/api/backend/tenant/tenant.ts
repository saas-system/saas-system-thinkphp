import createAxios from '/@/utils/axios'

const controllerUrl = 'admin/tenant.Tenant/'
export const actionUrl = new Map([
    ['getTenantConfig', controllerUrl + 'getTenantConfig'],
    ['clearData', controllerUrl + 'clearData'],
    ['initData', controllerUrl + 'initData'],
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
