<template>
    <component :is="config.layout.layoutMode"></component>
</template>

<script setup lang="ts">
import {useConfig} from '/@/stores/config'
import {useNavTabs} from '/@/stores/tenantNavTabs'
import {useSiteConfig} from '/@/stores/siteConfig'
import {useTenantAdminInfo} from '/@/stores/tenantAdminInfo'
import {useRoute} from 'vue-router'
import Default from '/@/layouts/tenant/container/default.vue'
import Classic from '/@/layouts/tenant/container/classic.vue'
import Streamline from '/@/layouts/tenant/container/streamline.vue'
import Double from '/@/layouts/tenant/container/double.vue'
import {onMounted, onBeforeMount} from 'vue'
import {Session} from '/@/utils/storage'
import {index} from '/@/api/tenant'
import { handleTenantRoute,getFirstRoute, routePush } from '/@/utils/router'
import router from '/@/router/index'
import {tenantBaseRoutePath} from '/@/router/static/tenantBase'
import {useEventListener} from '@vueuse/core'
import {BEFORE_RESIZE_LAYOUT} from '/@/stores/constant/cacheKey'
import {isEmpty} from 'lodash-es'

const navTabs = useNavTabs()
const config = useConfig()
const route = useRoute()
const siteConfig = useSiteConfig()
const adminInfo = useTenantAdminInfo()

onMounted(() => {
    // 判断是否登录
    if (!adminInfo.token) return router.push({name: 'tenantLogin'})

    init()
    onSetNavTabsMinWidth()
    useEventListener(window, 'resize', onSetNavTabsMinWidth)
})
onBeforeMount(() => {
    onAdaptiveLayout()
    useEventListener(window, 'resize', onAdaptiveLayout)
})

const init = () => {
    /**
     * 后台初始化请求，获取站点配置，动态路由等信息
     */
    index().then((res) => {
        siteConfig.dataFill(res.data.siteConfig)
        siteConfig.setInitialize(true)
        if (!isEmpty(res.data.adminInfo)) {
            adminInfo.dataFill(res.data.adminInfo)
            siteConfig.setUserInitialize(true)
        }


        if (res.data.menus) {
            handleTenantRoute(res.data.menus)

            // 预跳转到上次路径
            if (route.params.to) {
                const lastRoute = JSON.parse(route.params.to as string)
                if (lastRoute.path != tenantBaseRoutePath) {
                    let query = !isEmpty(lastRoute.query) ? lastRoute.query : {}
                    routePush({path: lastRoute.path, query: query})
                    return
                }
            }

            // 跳转到第一个菜单
            let firstRoute = getFirstRoute(navTabs.state.tabsViewRoutes)
            if (firstRoute) routePush(firstRoute.path)
        }
    })
}

const onAdaptiveLayout = () => {
    let defaultBeforeResizeLayout = {
        layoutMode: config.layout.layoutMode,
        menuCollapse: config.layout.menuCollapse,
    }
    let beforeResizeLayout = Session.get(BEFORE_RESIZE_LAYOUT)
    if (!beforeResizeLayout) Session.set(BEFORE_RESIZE_LAYOUT, defaultBeforeResizeLayout)

    const clientWidth = document.body.clientWidth
    if (clientWidth < 1024) {
        config.setLayout('menuCollapse', true)
        config.setLayout('shrink', true)
        config.setLayoutMode('Classic')
    } else {
        let beforeResizeLayoutTemp = beforeResizeLayout || defaultBeforeResizeLayout

        config.setLayout('menuCollapse', beforeResizeLayoutTemp.menuCollapse)
        config.setLayout('shrink', false)
        config.setLayoutMode(beforeResizeLayoutTemp.layoutMode)
    }
}

// 在实例挂载后为navTabs设置一个min-width，防止宽度改变时闪现滚动条
const onSetNavTabsMinWidth = () => {
    const navTabs = document.querySelector('.nav-tabs') as HTMLElement
    if (!navTabs) {
        return
    }
    const navBar = document.querySelector('.nav-bar') as HTMLElement
    const navMenus = document.querySelector('.nav-menus') as HTMLElement
    const minWidth = navBar.offsetWidth - (navMenus.offsetWidth + 20)
    navTabs.style.width = minWidth.toString() + 'px'
}
</script>

<!-- 只有在 components 选项中的组件可以被动态组件使用-->
<script lang="ts">
export default {
    components: {Default, Classic, Streamline, Double},
}
</script>
