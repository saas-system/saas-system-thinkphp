import createAxios from '/@/utils/axios'

const controllerUrl = '/tenant/routine.TenantInfo/'

export const actionUrl = new Map([
    ['index', controllerUrl + 'index'],
    ['edit', controllerUrl + 'edit'],
])

export function index() {
    return createAxios({
        url: actionUrl.get('index'),
        method: 'get',
    })
}

export function postData(data: anyObj) {
    return createAxios(
        {
            url: actionUrl.get('edit'),
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
