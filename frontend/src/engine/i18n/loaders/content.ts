export default async () => {
    const [library, publishing, forms, media, layout, builder] = await Promise.all([
        import('@/modules/Content/Library/locales'),
        import('@/modules/Content/Publishing/locales'),
        import('@/modules/Content/Forms/locales'),
        import('@/modules/Content/Media/locales'),
        import('@/modules/Content/Layout/locales'),
        import('@/modules/Content/Layout/locales/builder'),
    ]);
    return {
        en: {
            library: library.default.en,
            publishing: publishing.default.en,
            forms: forms.default.en,
            media: media.default.en,
            layout: layout.default.en,
            builder: builder.default.en,
        },
        id: {
            library: library.default.id,
            publishing: publishing.default.id,
            forms: forms.default.id,
            media: media.default.id,
            layout: layout.default.id,
            builder: builder.default.id,
        },
        su: {
            library: library.default.su,
            publishing: publishing.default.su,
            forms: forms.default.su,
            media: media.default.su,
            layout: layout.default.su,
            builder: builder.default.su,
        },
    };
};
