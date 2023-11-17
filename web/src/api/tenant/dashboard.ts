import createAxios from '/@/utils/axios'

export const url = '/tenant/Dashboard/'

export function dashboard() {
    return createAxios({
        url: url + 'index',
        method: 'get',
    })
}
