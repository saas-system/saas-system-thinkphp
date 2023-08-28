<template>
    <el-header v-if="!navTabs.state.tabFullScreen" class="layout-header">
        <component :is="config.layout.layoutMode + 'NavBar'"></component>
    </el-header>
</template>
<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/tenantNavTabs'
import DefaultNavBar from '/@/layouts/tenant/components/navBar/default.vue'
import ClassicNavBar from '/@/layouts/tenant/components/navBar/classic.vue'
import StreamlineNavBar from '/@/layouts/tenant/components/menus/menuHorizontal.vue'
import DoubleNavBar from '/@/layouts/tenant/components/navBar/double.vue'
const config = useConfig()
const navTabs = useNavTabs()
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'layout/header',
    components: { DefaultNavBar, ClassicNavBar, StreamlineNavBar, DoubleNavBar },
})
</script>

<style scoped lang="scss">
.layout-header {
    height: auto;
    padding: 0;
}
</style>
