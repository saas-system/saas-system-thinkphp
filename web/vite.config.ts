import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import { UserConfig, ConfigEnv, ProxyOptions, loadEnv } from 'vite'
import { isProd } from '/@/utils/vite'
import { svgBuilder } from '/@/components/icon/svg/index'
import * as process from "process";

const pathResolve = (dir: string): any => {
    return resolve(__dirname, '.', dir)
}

// https://vitejs.cn/config/
const viteConfig = ({ mode }: ConfigEnv): UserConfig => {
    const { VITE_PORT, VITE_OPEN, VITE_BASE_PATH, VITE_OUT_DIR, VITE_PROXY_URL } = loadEnv(mode, process.cwd())

    const alias: Record<string, string> = {
        '/@': pathResolve('./src/'),
        assets: pathResolve('./src/assets'),
        'vue-i18n': isProd(mode) ? 'vue-i18n/dist/vue-i18n.cjs.prod.js' : 'vue-i18n/dist/vue-i18n.cjs.js',
    }

    let proxy: Record<string, string | ProxyOptions> = {}
    if (VITE_PROXY_URL) {
        proxy = {
            '/': {
                target: VITE_PROXY_URL,
                changeOrigin: true,
            },
        }
    }

    return {
        plugins: [vue(), svgBuilder('./src/assets/icons/')],
        root: process.cwd(),
        resolve: { alias },
        base: VITE_BASE_PATH,
        server: {
            host: process.cwd(),
            open: VITE_OPEN != 'false',
            proxy: proxy,
        },
        build: {
            cssCodeSplit: false,
            sourcemap: false,
            outDir: VITE_OUT_DIR,
            emptyOutDir: true,
            chunkSizeWarningLimit: 1500,
            rollupOptions: {
                output: {
                    manualChunks: {
                        // 分包配置，配置完成自动按需加载
                        vue: ['vue', 'vue-router', 'pinia', 'vue-i18n', 'element-plus'],
                        echarts: ['echarts'],
                    },
                }
            },
        },
        css: {
            postcss: {
                plugins: [
                    {
                        postcssPlugin: 'internal:charset-removal',
                        AtRule: {
                            charset: (atRule) => {
                                if (atRule.name === 'charset') {
                                    atRule.remove()
                                }
                            },
                        },
                    },
                ],
            },
        },
    }
}

export default viteConfig
