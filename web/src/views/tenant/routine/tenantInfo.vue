<template>
    <div class="default-main">
        <div class="admin-info">
            <el-image :src="state.tenantInfo.logo" class="avatar">
                <template #error>
                    <div class="image-slot">
                        <Icon size="30" color="#c0c4cc" name="el-icon-Picture"/>
                    </div>
                </template>
            </el-image>
            <div class="admin-info-base" v-if="state.tenantInfo.name">
                <div class="admin-nickname">{{ state.tenantInfo.name }}</div>
            </div>
            <el-table v-if="state.tableData[0]" class="info-table" :data="state.tableData" :stripe="true" :header-cell-style="{textAlign: 'center', color: '#333'}" :cell-style="{textAlign: 'center'}" border>
                <!--                <el-table-column prop="contact_name" label="联系人" />-->
                <!--                <el-table-column prop="mobile" label="联系电话" />-->
                <el-table-column prop="expire_time" label="到期时间" />
            </el-table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { index, postData } from '/@/api/tenant/routine/TenantInfo'
import { ElForm, FormItemRule } from 'element-plus'
import { onResetForm } from '/@/utils/common'
import { uuid } from '../../../utils/random'
import { buildValidatorData } from '/@/utils/validate'
import { fileUpload } from '/@/api/common'
import { useTenantAdminInfo } from '/@/stores/tenantAdminInfo'
import { timeFormat } from '/@/components/table'

const { t } = useI18n()
const formRef = ref<InstanceType<typeof ElForm>>()

const adminInfoStore = useTenantAdminInfo()

const state: {
    tenantInfo: anyObj
    formKey: string
    buttonLoading: boolean
    tableData: any
} = reactive({
    tenantInfo: {},
    formKey: uuid(),
    buttonLoading: false,
    tableData: []
})

index().then((res) => {
    state.tenantInfo = res.data.info
    state.tableData = [
        {
            contact_name: state.tenantInfo.contact_name,
            mobile: state.tenantInfo.mobile,
            expire_time: state.tenantInfo.expire_time_text
        }
    ];
    // 重新渲染表单以记录初始值
    state.formKey = uuid()
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'routine/tenantInfo',
})
</script>

<style scoped lang="scss">
.info-table {
    width: 60%;
    margin: 20px auto 0;
}

.admin-info {
    width: 100%;
    height: calc(100vh - 130px);
    background-color: var(--ba-bg-color-overlay);
    border-radius: var(--el-border-radius-base);
    border-top: 3px solid #409eff;

    .avatar {
        width: 110px;
        height: 110px;
        display: block;
        margin: 60px auto 10px;
    }

    .image-slot {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .admin-info-base {
        .admin-nickname {
            font-size: 22px;
            color: var(--el-text-color-primary);
            text-align: center;
            padding: 8px 0;
        }
    }

    .unlock-code {
        color: #000;
        width: 200px;
        margin: 10px auto;
        font-size: 16px;
        display: flex;
        padding: 6px 12px;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        border: 1px solid #4290f7;
        border-radius: 8px;
        background: rgba(66, 144, 247, 0.05);
        color: var(--el-text-color-regular);

        span {
            font-size: 18px;
            font-weight: bold;
            color: var(--el-color-primary);
        }
    }
}
</style>
