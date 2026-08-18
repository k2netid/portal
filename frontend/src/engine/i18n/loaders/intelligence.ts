export default async () => {
    const [newsletter, search, ai, analytics] = await Promise.all([
        import('@/modules/Intelligence/Newsletter/locales'),
        import('@/modules/Intelligence/Search/locales'),
        import('@/modules/Intelligence/Ai/locales'),
        import('@/modules/Intelligence/Analytics/locales'),
    ]);
    return {
        en: {
            newsletter: newsletter.default.en,
            search: search.default.en,
            ai: ai.default.en,
            analytics: analytics.default.en,
        },
        id: {
            newsletter: newsletter.default.id,
            search: search.default.id,
            ai: ai.default.id,
            analytics: analytics.default.id,
        },
        su: {
            newsletter: newsletter.default.su,
            search: search.default.su,
            ai: ai.default.su,
            analytics: analytics.default.su,
        },
    };
};
