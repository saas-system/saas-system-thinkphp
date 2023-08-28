import createAxios from '/@/utils/axios'

export function getVar(id: number) {
    return createAxios({
        url: '/admin/sms.Variable/getVar',
        method: 'get',
        params: {
            id: id,
        },
    })
}
