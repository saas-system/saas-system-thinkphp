import { defineStore } from 'pinia'
import { TENANT_ADMIN_INFO } from '/@/stores/constant/cacheKey'
import { AdminInfo } from '/@/stores/interface'

export const useTenantAdminInfo = defineStore('tenantAdminInfo', {
    state: (): AdminInfo => {
        return {
            id: 0,
            username: '',
            nickname: '',
            avatar: '',
            last_login_time: '',
            token: '',
            refreshToken: '',
            // 是否是superAdmin，用于判定是否显示终端按钮等，不做任何权限判断
            super: false,
        }
    },
    actions: {
        dataFill(state: AdminInfo) {
            this.$state = { ...this.$state, ...state }
        },
        removeToken() {
            this.token = ''
            this.refreshToken = ''
        },
        setToken(token: string, type: 'token' | 'refreshToken') {
            this[type] = token
        },
        getToken(type: 'auth' | 'refresh' = 'auth') {
            return type === 'auth' ? this.token : this.refreshToken
        },
        setSuper(val: boolean) {
            this.super = val
        },
    },
    persist: {
        key: TENANT_ADMIN_INFO,
    },
})
