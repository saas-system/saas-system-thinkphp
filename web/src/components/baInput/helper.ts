import { FieldData } from './index'

export const npuaFalse = () => {
    return {
        null: false,
        primaryKey: false,
        unsigned: false,
        autoIncrement: false,
    }
}

/**
 * 所有 Input 支持的类型对应的数据字段类型等数据（默认/示例设计）
 */
export const fieldData: FieldData = {
    string: {
        type: 'varchar',
        length: 200,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    password: {
        type: 'varchar',
        length: 32,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    number: {
        type: 'int',
        length: 10,
        precision: 0,
        default: '0',
        ...npuaFalse(),
    },
    radio: {
        type: 'enum',
        length: 0,
        precision: 0,
        default: '',
        ...npuaFalse(),
    },
    checkbox: {
        type: 'set',
        length: 0,
        precision: 0,
        default: '',
        ...npuaFalse(),
    },
    switch: {
        type: 'tinyint',
        length: 1,
        precision: 0,
        default: '1',
        ...npuaFalse(),
        unsigned: true,
    },
    textarea: {
        type: 'varchar',
        length: 255,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    array: {
        type: 'varchar',
        length: 255,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    datetime: {
        type: 'bigint',
        length: 16,
        precision: 0,
        default: 'null',
        ...npuaFalse(),
        null: true,
        unsigned: true,
    },
    year: {
        type: 'year',
        length: 4,
        precision: 0,
        default: 'null',
        ...npuaFalse(),
        null: true,
    },
    date: {
        type: 'date',
        length: 0,
        precision: 0,
        default: 'null',
        ...npuaFalse(),
        null: true,
    },
    time: {
        type: 'time',
        length: 0,
        precision: 0,
        default: 'null',
        ...npuaFalse(),
        null: true,
    },
    select: {
        type: 'enum',
        length: 0,
        precision: 0,
        default: '',
        ...npuaFalse(),
    },
    selects: {
        type: 'varchar',
        length: 100,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    remoteSelect: {
        type: 'int',
        length: 10,
        precision: 0,
        default: '0',
        ...npuaFalse(),
        unsigned: true,
    },
    remoteSelects: {
        type: 'varchar',
        length: 100,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    editor: {
        type: 'text',
        length: 0,
        precision: 0,
        default: 'null',
        ...npuaFalse(),
        null: true,
    },
    city: {
        type: 'varchar',
        length: 100,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    image: {
        type: 'varchar',
        length: 200,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    images: {
        type: 'varchar',
        length: 255,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    file: {
        type: 'varchar',
        length: 200,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    files: {
        type: 'varchar',
        length: 255,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    icon: {
        type: 'varchar',
        length: 50,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
    color: {
        type: 'varchar',
        length: 30,
        precision: 0,
        default: 'empty string',
        ...npuaFalse(),
    },
}

export const stringToArray = (val: string | string[]) => {
    if (typeof val === 'string') {
        return val == '' ? [] : val.split(',')
    } else {
        return val as string[]
    }
}
