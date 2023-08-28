import createAxios from '/@/utils/axios'

const controllerUrl = 'admin/tenant.Printer/'
export const actionUrl = new Map([
    ['printerTest', controllerUrl + 'printerTest'],
])

export function printerTestApi(sn: string) {
    return createAxios(
        {
            url: actionUrl.get('printerTest'),
            method: 'post',
            data: {
                sn: sn
            }
        },
        {
            showSuccessMessage: true
        }
    )
}
