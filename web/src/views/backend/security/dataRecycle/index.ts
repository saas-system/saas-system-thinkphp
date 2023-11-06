import baTableClass from '/@/utils/baTable'
import type { baTableApi } from '/@/api/common'
import { add } from '/@/api/backend/security/dataRecycle'
import { uuid } from '/@/utils/random'


export class dataRecycleClass extends baTableClass {
    constructor(api: baTableApi, table: BaTable, form: BaTableForm = {}, before: BaTableBefore = {}, after: BaTableAfter = {}) {
        super(api, table, form, before, after)
    }

    // 重写编辑
    requestEdit = (id: string) => {
        this.runBefore('requestEdit', { id })
        this.form.loading = true
        this.form.items = {}
        return this.api
            .edit({
                id: id,
            })
            .then((res) => {
                this.form.extend = Object.assign(this.form.extend!, {
                    tableList: res.data.tables,
                    controllerList: res.data.controllers,
                    appList: res.data.apps,
                })


                this.form.loading = false
                this.form.items = res.data.row
                this.runAfter('requestEdit', { res })
            })
    }

    /**
     * 重写打开表单方法
     */
    toggleForm = (operate = '', operateIds: string[] = []) => {
        // console.log('open')
        this.runBefore('toggleForm', { operate, operateIds })
        if (this.form.ref) {
            this.form.ref.resetFields()
        }
        // console.log(operate)

        if (operate == 'Edit') {
            if (!operateIds.length) {
                return false
            }
            this.requestEdit(operateIds[0])
        } else if (operate == 'Add') {
            this.form.loading = true
            add('admin').then((res) => {
                this.form.extend = Object.assign(this.form.extend!, {
                    tableList: res.data.tables,
                    controllerList: res.data.controllers,
                    appList: res.data.apps,
                })
                this.form.items = Object.assign({}, this.form.defaultItems)
                this.form.loading = false
            })
        }

        this.form.operate = operate
        this.form.operateIds = operateIds
        this.runAfter('toggleForm', { operate, operateIds })
    }

    /**
     * app变更逻辑
     */
    onAppChange = () => {
        this.form.extend = Object.assign(this.form.extend!, {
            controllerLoading: true,
            controllerList: {},
            controllerSelectKey: uuid(),
        })

        this.form.items!.controller = ''
        add(this.form.items!.app).then((res) => {
            this.form.extend = Object.assign(this.form.extend!, {
                controllerLoading: false,
                controllerList: res.data.controllers,
                controllerSelectKey: uuid(),
            })
        })
    }
}
