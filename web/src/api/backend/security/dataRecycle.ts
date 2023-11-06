import createAxios from '/@/utils/axios'

export const url = '/admin/security.DataRecycle/'

export function add(appName?: string) {
    return createAxios({
        url: url + 'add?app=' + appName,
        method: 'get',
    })
}
