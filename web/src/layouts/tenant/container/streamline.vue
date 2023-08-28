<template>
    <el-container class="layout-container">
        <el-container class="content-wrapper">
            <Header />
            <Main />
        </el-container>
    </el-container>
    <CloseFullScreen v-if="navTabs.state.tabFullScreen" />
</template>

<script setup lang="ts">
import Header from '/@/layouts/tenant/components/header.vue'
import Main from '/@/layouts/tenant/router-view/main.vue'
import CloseFullScreen from '/@/layouts/tenant/components/closeFullScreen.vue'
import { useNavTabs } from '/@/stores/tenantNavTabs'
const navTabs = useNavTabs()
</script>

<style scoped>
.layout-container {
    height: 100%;
    width: 100%;
}
.content-wrapper {
    flex-direction: column;
    width: 100%;
    height: 100%;
}
</style>
