/** Canonical API paths (relative to axios baseURL `/api/v1`). */

export const analyticsPaths = {
    track: '/public/analytics/track',
    trackBatch: '/public/analytics/track/batch',
    trackVisit: '/public/analytics/track-visit',
    overview: '/manage/analytics/overview',
    visits: '/manage/analytics/visits',
    topPages: '/manage/analytics/top-pages',
    topContent: '/manage/analytics/top-content',
    devices: '/manage/analytics/devices',
    browsers: '/manage/analytics/browsers',
    countries: '/manage/analytics/countries',
    referrers: '/manage/analytics/referrers',
    realtime: '/manage/analytics/realtime',
    export: '/manage/analytics/export',
    cleanup: '/manage/analytics/cleanup',
    purgeAll: '/manage/analytics/purge-all',
} as const;

export const systemPaths = {
    emailTemplates: '/manage/system/email-templates',
    emailTemplate: (id: string) => `/manage/system/email-templates/${id}`,
    publicConsoleTheme: '/public/system/console-theme',
    settings: '/manage/system/settings',
    settingsGroup: (group: string) => `/manage/system/settings/group/${group}`,
    testStorage: '/manage/system/settings/test-storage',
} as const;

export const dataModelPaths = {
    types: '/manage/infra/models/types',
    openApiIndex: '/manage/infra/models/types/openapi-index',
    openApiBySlug: (slug: string) => `/manage/infra/models/types/by-slug/${slug}/openapi`,
    type: (id: string) => `/manage/infra/models/types/${id}`,
    typeBySlug: (slug: string) => `/manage/infra/models/types/by-slug/${slug}`,
    validationRules: (id: string) => `/manage/infra/models/types/${id}/validation-rules`,
} as const;

export const dynamicRecordPaths = {
    index: (slug: string) => `/dynamic/${slug}`,
    record: (slug: string, id: string) => `/dynamic/${slug}/${id}`,
} as const;

export const infraPaths = {
    fileManager: '/manage/infra/file-manager',
    fileManagerUpload: '/manage/infra/file-manager/upload',
    fileManagerDownload: '/manage/infra/file-manager/download',
    fileManagerDelete: '/manage/infra/file-manager/delete',
    fileManagerFolder: '/manage/infra/file-manager/folder',
    fileManagerMove: '/manage/infra/file-manager/move',
    fileManagerTrash: '/manage/infra/file-manager/trash',
    fileManagerRestore: '/manage/infra/file-manager/restore',
    fileManagerTrashEmpty: '/manage/infra/file-manager/trash/empty',
    fileManagerTrashPermanent: '/manage/infra/file-manager/trash/permanent',
} as const;

export const platformPaths = {
    publicCatalog: '/public/platform/catalog',
} as const;


