import createAxios from '/@/utils/axios'

const controllerUrl = 'admin/tenant.admin/'

export function autoLoginApi(data: object): ApiPromise {
    return createAxios({
        url: controllerUrl + 'autoLogin',
        method: 'post',
        data: data
    }) as ApiPromise
}

