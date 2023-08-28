import createAxios from '/@/utils/axios'
import { useAdminInfo } from '/@/stores/adminInfo'

const controllerUrl = '/tenant/index/'

export function index() {
    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    })
}

export function login(method: 'get' | 'post', params: object = {}): ApiPromise {
    return createAxios({
        url: controllerUrl + 'login',
        data: params,
        method: method,
    }) as ApiPromise
}

export function logout() {
    const adminInfo = useAdminInfo()
    return createAxios({
        url: controllerUrl + 'logout',
        method: 'POST',
        data: {
            refreshToken: adminInfo.getToken('refresh'),
        },
    })
}


export function getServerInfo() {
    return createAxios({
        url: '/tenant/common.Common/getServerInfo',
        method: 'get',
    })
}
