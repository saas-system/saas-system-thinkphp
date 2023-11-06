import createAxios from '/@/utils/axios'

export const url = '/admin/security.SensitiveData/'

export function add(appName?: string) {
    return createAxios({
        url: url + 'add?app=' + appName,
        method: 'get',
    })
}
