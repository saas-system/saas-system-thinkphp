import createAxios from '/@/utils/axios'

export function dashboard() {
    return createAxios({
        url: '/tenant/Dashboard/dashboard',
        method: 'get',
    })
}
