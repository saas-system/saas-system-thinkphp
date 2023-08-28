import createAxios from '/@/utils/axios'
import { authMenu } from '/@/api/tenant/controllerUrls'

export function getMenuRules() {
    return createAxios({
        url: authMenu + 'index',
        method: 'get',
    })
}
