<template>
    <div class="default-main">
        <el-collapse class="collapse" v-model="state.collapseActiveName">
            <el-collapse-item class="collapse-item" :title="t('sms.config.Basic SMS configuration')" name="base">
                <el-form @keyup.enter="onSubmitBase()" :model="state.base" label-position="top" :label-width="200">
                    <FormItem type="number" :label="t('sms.config.Send timeout (seconds)')" v-model.number="state.base.timeout" />
                    <FormItem
                        type="radio"
                        :label="t('sms.config.Send Policy')"
                        v-model.number="state.base.strategy"
                        :data="{
                            content: {
                                order: t('sms.config.Sequential service provider sends'),
                                random: t('sms.config.Sent with the service provider'),
                            },
                            childrenAttr: { border: true },
                        }"
                    />
                    <FormItem
                        type="selects"
                        :label="t('sms.config.Available service providers')"
                        v-model.number="state.base.gateways"
                        :placeholder="
                            t('sms.config.The selected service provider needs to configure information at the bottom of this page (required)')
                        "
                        :data="{ content: gateways, childrenAttr: { border: true } }"
                    />
                    <el-button v-blur :loading="state.base.loading" @click="onSubmitBase()" type="primary">
                        {{ t('Save') }}
                    </el-button>
                </el-form>
            </el-collapse-item>
            <el-collapse-item class="collapse-item" :title="t('sms.config.Service Provider Configuration')" name="gateway">
                <el-form @keyup.enter="onSubmitGateway()" :model="state.gatewayConfig" label-position="top" :label-width="200">
                    <FormItem
                        type="select"
                        :label="t('sms.config.Service provider')"
                        v-model.number="state.gateway.name"
                        :placeholder="t('sms.config.Select a service provider to start configuration')"
                        :data="{ content: gateways, childrenAttr: { border: true } }"
                        :input-attr="{
                            onChange: onSelectGateway,
                        }"
                    />
                    <div v-loading="state.gateway.loading">
                        <template v-if="!isEmpty(state.gatewayConfig) && !state.gateway.loading">
                            <div v-for="(item, key) in state.gatewayConfig" :key="key">
                                <FormItem
                                    type="string"
                                    :label="
                                        te('sms.config.' + state.gateway.name + '/' + key)
                                            ? t('sms.config.' + state.gateway.name + '/' + key)
                                            : t('sms.config.common/' + key)
                                    "
                                    v-model="state.gatewayConfig[key]"
                                    :placeholder="
                                        te('sms.config.' + state.gateway.name + '/placeholder/' + key)
                                            ? t('sms.config.' + state.gateway.name + '/placeholder/' + key)
                                            : ''
                                    "
                                />
                            </div>
                        </template>
                    </div>
                    <el-button
                        v-blur
                        v-if="!isEmpty(state.gatewayConfig)"
                        :loading="state.gateway.submitLoading"
                        @click="onSubmitGateway()"
                        type="primary"
                    >
                        {{ t('Save') }}
                    </el-button>
                </el-form>
            </el-collapse-item>
        </el-collapse>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { getConfigKey, saveConfig } from '/@/api/backend/sms/config'
import FormItem from '/@/components/formItem/index.vue'
import { isEmpty } from 'lodash-es'

const { t, te } = useI18n()
const state = reactive({
    collapseActiveName: ['base', 'gateway'],
    base: {
        loading: false,
        timeout: 0,
        strategy: '',
        gateways: [],
    },
    gateway: {
        name: '',
        loading: false,
        submitLoading: false,
    },
    gatewayConfig: {},
})

const onSubmitBase = () => {
    state.base.loading = true
    saveConfig('base', state.base).finally(() => {
        state.base.loading = false
    })
}

const onSubmitGateway = () => {
    state.gateway.submitLoading = true
    saveConfig('gateway', state.gatewayConfig, state.gateway.name).finally(() => {
        state.gateway.submitLoading = false
    })
}

const onSelectGateway = () => {
    state.gateway.loading = true
    getConfigKey(state.gateway.name)
        .then((res) => {
            state.gatewayConfig = res.data.config
        })
        .finally(() => {
            state.gateway.loading = false
        })
}

const gateways = {
    aliyun: t('sms.config.aliyun'),
    qcloud: t('sms.config.qcloud'),
    qiniu: t('sms.config.qiniu'),
    yunpian: t('sms.config.yunpian'),
}

const init = () => {
    state.base.loading = true
    getConfigKey('base')
        .then((res) => {
            state.base.timeout = res.data.timeout
            state.base.gateways = res.data.gateways
            state.base.strategy = res.data.strategy == 'Overtrue\\EasySms\\Strategies\\OrderStrategy' ? 'order' : 'random'
        })
        .finally(() => {
            state.base.loading = false
        })
}
init()
</script>

<style scoped lang="scss">
.collapse {
    width: 50%;
    padding: 20px;
    border-radius: var(--el-border-radius-base);
    background-color: var(--ba-bg-color-overlay);
}
.collapse-item :deep(.el-collapse-item__content) {
    padding-bottom: 15px;
}
@media screen and (max-width: 1024px) {
    .collapse {
        width: 100% !important;
    }
}
</style>
