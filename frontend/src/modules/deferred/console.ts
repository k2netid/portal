export async function loadDeferredConsoleModules() {
    const [
        { intelligenceModules },
    ] = await Promise.all([
        import('../Intelligence'),
    ]);

    return [
        ...intelligenceModules,
    ];
}
