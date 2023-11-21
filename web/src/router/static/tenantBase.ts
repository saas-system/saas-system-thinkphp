import type { RouteRecordRaw } from 'vue-router'
/**
 * 后台基础路由路径
 */
export const tenantBaseRoutePath = '/tenant'
/*
 * 后台基础静态路由
 */
const tenantBaseRoute: RouteRecordRaw = {
    path: tenantBaseRoutePath,
    name: 'admin',
    component: () => import('/@/layouts/backend/index.vue'),
    // 直接重定向到 loading 路由
    redirect: tenantBaseRoutePath + '/loading',
    meta: {
        title: `pagesTitle.admin`,
    },
    children: [
        {
            path: 'loading/:to?',
            name: 'tenantMainLoading',
            component: () => import('/@/layouts/common/components/loading.vue'),
            meta: {
                title: `pagesTitle.loading`,
            },
        },
    ],
}
export default tenantBaseRoute
