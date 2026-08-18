import '@tanstack/vue-table'

declare module '@tanstack/vue-table' {
    interface TableMeta<TData extends import('@tanstack/vue-table').RowData> {
        onRowClick?: (data: TData) => void
    }
}
