import { RouteRecordRaw } from 'vue-router'

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
        // 管理员登录页
        path: '/platform/login',
        name: 'adminLogin',
        component: () => import('/@/views/backend/login.vue'),
        meta: {
            title: pageTitle('adminLogin'),
        },
    },
    {
        // 租户登录页
        path: '/tenant/login',
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
        path: '/platform:path(.*)*',
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
        path: '/tenant:path(.*)*',
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

// console.log(' HI: ', import.meta.env);
// const mode = import.meta.env.MODE
//
// const compont = 'backend';
// if (mode == 'tenant') {
//     staticRoutes.push({
//         // 首页
//         path: '/',
//         name: '/',
//         component: () => import('/@/layouts/tenant/index.vue'),
//         meta: {
//             title: pageTitle('home'),
//         },
//     })
// }else{
//     staticRoutes.push({
//         // 首页
//         path: '/',
//         name: '/',
//         component: () => import('/@/layouts/backend/index.vue'),
//         meta: {
//             title: pageTitle('home'),
//         },
//     })
// }

/*
 * 后台基础静态路由
 */
const adminBaseRoute: RouteRecordRaw = {
    path: '/platform',
    name: 'platform',
    component: () => import('/@/layouts/backend/index.vue'),
    redirect: '/platform/loading',
    meta: {
        title: pageTitle('platform'),
    },
    children: [
        {
            path: 'loading/:to?',
            name: 'adminMainLoading',
            component: () => import('/@/layouts/common/components/loading.vue'),
            meta: {
                title: pageTitle('Loading'),
            },
        },
    ],
}

/*
 * 租户端基础静态路由
 */
const tenantBaseRoute: RouteRecordRaw = {
    path: '/tenant',
    name: 'tenant',
    component: () => import('/@/layouts/tenant/index.vue'),
    redirect: '/tenant/loading',
    meta: {
        title: pageTitle('tenant'),
    },
    children: [
        {
            path: 'loading/:to?',
            name: 'tenantMainLoading',
            component: () => import('/@/layouts/common/components/loading.vue'),
            meta: {
                title: pageTitle('Loading'),
            },
        },
    ],
}


staticRoutes.push(adminBaseRoute)
staticRoutes.push(tenantBaseRoute)

export { staticRoutes, adminBaseRoute, tenantBaseRoute }
