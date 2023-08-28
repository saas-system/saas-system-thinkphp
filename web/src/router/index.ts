import {createRouter, createWebHashHistory} from 'vue-router'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'
import {staticRoutes, adminBaseRoute, tenantBaseRoute} from '/@/router/static'
import {loading} from '/@/utils/loading'
import langAutoLoadMap from '/@/lang/autoload'
import {mergeMessage} from '/@/lang/index'
import {useConfig} from '/@/stores/config'
import {isAdminApp, isTenantApp} from '/@/utils/common'
import {uniq} from 'lodash-es'

const router = createRouter({
    history: createWebHashHistory(),
    routes: staticRoutes,
})

router.beforeEach((to, from, next) => {
    NProgress.configure({showSpinner: false})
    NProgress.start()
    if (!window.existLoading) {
        loading.show()
        window.existLoading = true
    }

    // 按需动态加载页面的语言包-start
    let loadPath: string[] = []
    const config = useConfig()
    if (to.path in langAutoLoadMap) {
        loadPath.push(...langAutoLoadMap[to.path as keyof typeof langAutoLoadMap])
    }
    let prefix = ''
    if (isAdminApp(to.fullPath)) {
        prefix = './backend/' + config.lang.defaultLang
        // 去除 path 中的 /platform
        const adminPath = to.path.slice(to.path.indexOf(adminBaseRoute.path) + adminBaseRoute.path.length)
        if (adminPath) loadPath.push(prefix + adminPath + '.ts')

    } else if (isTenantApp(to.fullPath)) {
        prefix = './tenant/' + config.lang.defaultLang
        // 去除 path 中的 /tenant
        const tenantPath = to.path.slice(to.path.indexOf(tenantBaseRoute.path) + tenantBaseRoute.path.length)
        if (tenantPath) loadPath.push(prefix + tenantPath + '.ts')

    }

    // 根据路由 name 加载的语言包
    if (to.name) {
        loadPath.push(prefix + '/' + to.name.toString() + '.ts')
    }

    if (!window.loadLangHandle.publicMessageLoaded) window.loadLangHandle.publicMessageLoaded = []
    const publicMessagePath = prefix + '.ts'
    if (!window.loadLangHandle.publicMessageLoaded.includes(publicMessagePath)) {
        loadPath.push(publicMessagePath)
        window.loadLangHandle.publicMessageLoaded.push(publicMessagePath)
    }

    // 去重
    loadPath = uniq(loadPath)

    for (const key in loadPath) {
        loadPath[key] = loadPath[key].replaceAll('${lang}', config.lang.defaultLang)
        if (loadPath[key] in window.loadLangHandle) {
            window.loadLangHandle[loadPath[key]]().then((res: { default: anyObj }) => {
                const pathName = loadPath[key].slice(loadPath[key].lastIndexOf(prefix) + (prefix.length + 1), loadPath[key].lastIndexOf('.'))
                mergeMessage(res.default, pathName)
            })
        }
    }
    // 动态加载语言包-end

    next()
})

// 路由加载后
router.afterEach(() => {
    if (window.existLoading) {
        loading.hide()
    }
    NProgress.done()
})

export default router
