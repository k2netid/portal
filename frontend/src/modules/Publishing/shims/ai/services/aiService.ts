export const AiService = {
  async generate(): Promise<{ success: false; message: string }> {
    return {
      success: false,
      message: 'CMS AI pack is not installed. Enable Settings → AI for kernel generate, or install cms-ai.',
    };
  },
};

export default AiService;
