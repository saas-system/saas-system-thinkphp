import createAxios from '/@/utils/axios'

const controllerUrl = '/admin/tenant.WalletFlow/'
export const actionUrl = new Map([
    ['export', controllerUrl + 'export'],
])

export function exportFlowApi(data: anyObj = {}) {
    return createAxios(
        {
            url: actionUrl.get('export'),
            method: 'post',
            data: data
        },
        {
            showSuccessMessage: true
        }
    )
}
