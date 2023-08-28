import createAxios from '/@/utils/axios'

const controllerUrl = '/tenant/user.User/'
export const actionUrl = new Map([
    // 用户
    ['findUserInfo', controllerUrl + 'findUserInfo'],   // 获取用户的基本信息
])

// 获取用户的基本信息
export function findUserInfoApi(id: any) {
    return createAxios(
        {
            url: actionUrl.get('findUserInfo'),
            method: 'get',
            params: {
                'id': id
            }
        },
        {
            showSuccessMessage: true,
        }
    )
}

