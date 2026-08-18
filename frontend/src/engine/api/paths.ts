/** Canonical API paths (relative to axios baseURL `/api/v1`). */

export const publishingPaths = {
    contents: '/manage/publishing/contents',
    content: (id: string) => `/manage/publishing/contents/${id}`,
    publicContents: '/public/publishing/contents',
    publicContent: (slug: string) => `/public/publishing/contents/${slug}`,
    publicContentRelated: (slug: string) => `/public/publishing/contents/${slug}/related`,
    publicContentComments: (contentId: string) => `/public/publishing/contents/${contentId}/comments`,
    settings: '/manage/publishing/settings',
    comments: '/manage/publishing/comments',
    contentTemplates: '/manage/publishing/content-templates',
    seo: '/manage/publishing/seo',
} as const;

export const libraryPaths = {
    tags: '/manage/library/tags',
    tagStatistics: '/manage/library/tags/statistics',
    tag: (id: string) => `/manage/library/tags/${id}`,
    customFields: '/manage/library/custom-fields',
    customField: (id: string) => `/manage/library/custom-fields/${id}`,
    fieldGroups: '/manage/library/field-groups',
    fieldGroup: (id: string) => `/manage/library/field-groups/${id}`,
    categories: '/manage/library/categories',
    category: (id: string) => `/manage/library/categories/${id}`,
    publicCategories: '/public/library/categories',
    publicCategory: (id: string) => `/public/library/categories/${id}`,
} as const;

export const layoutPaths = {
    menus: '/manage/layout/menus',
    menu: (id: string) => `/manage/layout/menus/${id}`,
    menuUsage: (id: string) => `/manage/layout/menus/${id}/usage`,
    menuRestore: (id: string) => `/manage/layout/menus/${id}/restore`,
    menuForceDelete: (id: string) => `/manage/layout/menus/${id}/force-delete`,
    publicMenuByLocation: (location: string) => `/public/layout/menus/location/${location}`,
    widgets: '/manage/layout/widgets',
    widget: (id: string) => `/manage/layout/widgets/${id}`,
    publicWidgetsByLocation: (location: string) => `/public/layout/widgets/location/${location}`,
    redirects: '/manage/layout/redirects',
    redirect: (id: string) => `/manage/layout/redirects/${id}`,
    redirectStatistics: '/manage/layout/redirects/statistics',
    pluginThemeSlots: '/manage/layout/plugin-theme-slots',
    themes: '/manage/layout/themes',
    themeLocations: '/manage/layout/themes/active/locations',
    publicThemeActive: '/public/layout/themes/active',
} as const;

export const mediaPaths = {
    index: '/manage/media',
    upload: '/manage/media/upload',
    statistics: '/manage/media/statistics',
    filters: '/manage/media/filters',
    bulk: '/manage/media/bulk',
    emptyTrash: '/manage/media/empty-trash',
    file: (id: string) => `/manage/media/${id}`,
    restore: (id: string) => `/manage/media/${id}/restore`,
    usage: (id: string) => `/manage/media/${id}/usage`,
    thumbnail: (id: string) => `/manage/media/${id}/thumbnail`,
    resize: (id: string) => `/manage/media/${id}/resize`,
    edit: (id: string) => `/manage/media/${id}/edit`,
    folders: '/manage/folders',
    folder: (id: string) => `/manage/folders/${id}`,
} as const;

export const formsPaths = {
    index: '/manage/forms',
    bulkAction: '/manage/forms/bulk-action',
    form: (id: string) => `/manage/forms/${id}`,
    formFields: (formId: string | number) => `/manage/forms/${formId}/fields`,
    formField: (formId: string | number, fieldId: string | number) => `/manage/forms/${formId}/fields/${fieldId}`,
    reorderFields: (formId: string | number) => `/manage/forms/${formId}/reorder-fields`,
    submissions: (formId: string | number) => `/manage/forms/${formId}/submissions`,
    submissionsExport: (formId: string | number) => `/manage/forms/${formId}/submissions/export`,
    submissionsStatistics: (formId: string | number) => `/manage/forms/${formId}/submissions/statistics`,
} as const;

export const formSubmissionPaths = {
    submission: (id: string) => `/manage/form-submissions/${id}`,
    exportPdf: (id: string) => `/manage/form-submissions/${id}/export-pdf`,
} as const;

export const newsletterPaths = {
    subscribe: '/public/newsletter/subscribe',
    subscribers: '/manage/newsletter/subscribers',
    subscriber: (id: string) => `/manage/newsletter/subscribers/${id}`,
    subscriberForce: (id: string) => `/manage/newsletter/subscribers/${id}/force`,
    subscriberRestore: (id: string) => `/manage/newsletter/subscribers/${id}/restore`,
    subscribersExport: '/manage/newsletter/subscribers/export',
    subscribersBulk: '/manage/newsletter/subscribers/bulk',
} as const;

export const searchPaths = {
    public: '/public/search',
    indexHealth: '/manage/search/index-health',
    manageStats: '/manage/search/stats',
    manageQueries: '/manage/search/queries',
    deleteQuery: (id: string) => `/manage/search/queries/${id}`,
    clearQueries: '/manage/search/queries/clear',
    reindex: '/manage/search/reindex',
} as const;

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
    plugins: '/manage/system/plugins',
    pluginActivate: (id: string | number) => `/manage/system/plugins/${id}/activate`,
    pluginDeactivate: (id: string | number) => `/manage/system/plugins/${id}/deactivate`,
    pluginSettings: (id: string | number) => `/manage/system/plugins/${id}/settings`,
} as const;

export const aiPaths = {
    providers: '/manage/ai/providers',
    models: (provider: string) => `/manage/ai/models/${provider}`,
    generate: '/manage/ai/generate',
    draftPublishing: '/manage/ai/draft-publishing',
    suggestTaxonomy: '/manage/ai/suggest-taxonomy',
    taxonomyBatches: '/manage/ai/taxonomy-batches',
    taxonomyBatch: (id: string) => `/manage/ai/taxonomy-batches/${id}`,
    usageStats: '/manage/ai/usage-stats',
} as const;

export const cckPaths = {
    types: '/manage/infra/cck/types',
    openApiIndex: '/manage/infra/cck/types/openapi-index',
    openApiBySlug: (slug: string) => `/manage/infra/cck/types/by-slug/${slug}/openapi`,
    type: (id: string) => `/manage/infra/cck/types/${id}`,
    typeBySlug: (slug: string) => `/manage/infra/cck/types/by-slug/${slug}`,
    validationRules: (id: string) => `/manage/infra/cck/types/${id}/validation-rules`,
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

export const memberPaths = {
    registrationPolicy: '/public/member/registration-policy',
    login: '/public/member/login',
    register: '/public/member/register',
    logout: '/public/member/logout',
    profile: '/public/member/profile',
    billing: '/public/member/billing',
    settings: '/public/member/settings',
    notifications: '/public/member/notifications',
    notificationsReadAll: '/public/member/notifications/read-all',
    aksaraLaunch: '/public/member/aksara/launch',
    exambroLaunch: '/public/member/exambro/launch',
} as const;

export const platformPaths = {
    publicCatalog: '/public/platform/catalog',
    publicSubscriptionFeatures: '/public/subscription/features',
} as const;



