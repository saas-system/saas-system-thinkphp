import createAxios from '/@/utils/axios'

export function getConfigKey(name: string) {
    return createAxios({
        url: '/admin/sms.Config/getConfigKey',
        method: 'get',
        params: {
            name: name,
        },
    })
}

export function saveConfig(type: string, data: anyObj, name = '') {
    return createAxios(
        {
            url: '/admin/sms.Config/saveConfig',
            method: 'post',
            params: {
                type: type,
                name: name,
            },
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
