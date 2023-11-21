import type { RouteRecordRaw } from 'vue-router'
import { adminBaseRoutePath } from '/@/router/static/adminBase'
import { tenantBaseRoutePath } from '/@/router/static/tenantBase'

const pageTitle = (name: string): string => {
    return `pagesTitle.${name}`
}

/*
 * 静态路由
 */
const staticRoutes: Array<RouteRecordRaw> = [
    {
        // 首页
        path: '/',
        name: '/',
        component: () => import('/@/layouts/tenant/index.vue'),
        meta: {
            title: pageTitle('home'),
        },
    },
    {
        // 管理员登录页 - 不放在 adminBaseRoute.children 因为登录页不需要使用后台的布局
        path: adminBaseRoutePath + '/login',
        name: 'adminLogin',
        component: () => import('/@/views/backend/login.vue'),
        meta: {
            title: pageTitle('adminLogin'),
        },
    },
    {
        // 租户登录页
        path: tenantBaseRoutePath + '/login',
        name: 'tenantLogin',
        component: () => import('/@/views/tenant/login.vue'),
        meta: {
            title: pageTitle('tenantLogin'),
        },
    },
    {
        path: '/:path(.*)*',
        redirect: '/404',
    },
    {
        // 404
        path: '/404',
        name: 'notFound',
        component: () => import('/@/views/common/error/404.vue'),
        meta: {
            title: pageTitle('notFound'), // 页面不存在
        },
    },
    {
        // 后台找不到页面了-可能是路由未加载上
        path: adminBaseRoutePath + ':path(.*)*',
        redirect: (to) => {
            return {
                name: 'adminMainLoading',
                params: {
                    to: JSON.stringify({
                        path: to.path,
                        query: to.query,
                    }),
                },
            }
        },
    },
    {
        // 租户端找不到页面了-可能是路由未加载上
        path: tenantBaseRoutePath + ':path(.*)*',
        redirect: (to) => {
            return {
                name: 'tenantMainLoading',
                params: {
                    to: JSON.stringify({
                        path: to.path,
                        query: to.query,
                    }),
                },
            }
        },
    },
    {
        // 无权限访问
        path: '/401',
        name: 'noPower',
        component: () => import('/@/views/common/error/401.vue'),
        meta: {
            title: pageTitle('noPower'),
        },
    },
]


const staticFiles: Record<string, Record<string, RouteRecordRaw>> = import.meta.glob('./static/*.ts', { eager: true })
for (const key in staticFiles) {
    if (staticFiles[key].default) staticRoutes.push(staticFiles[key].default)
}
export default staticRoutes
