import apiClient from '@/engine/api/client';

export interface AiGeneratePayload {
    prompt: string;
    context?: string;
    provider?: string;
    model?: string;
    action?: string;
}

/** Core AI API — paths must match `manage/ai/*` in system_api.php */
export const AiService = {
    async providers(): Promise<{ data: Array<{ id: string; name: string; logo?: string }> }> {
        try {
            const res = await apiClient.get('/manage/ai/providers');
            return res.data;
        } catch {
            return { data: [] };
        }
    },

    async models(provider: string): Promise<{ data: Array<{ id: string; name: string }> }> {
        try {
            const res = await apiClient.get(`/manage/ai/models/${encodeURIComponent(provider)}`);
            return res.data;
        } catch {
            return { data: [] };
        }
    },

    async generate(payload: AiGeneratePayload): Promise<{ data: { content?: string; provider?: string } }> {
        const res = await apiClient.post('/manage/ai/generate', payload);
        const body = res.data;
        // Laravel success envelope: { data: { content, provider } }
        if (body?.data && typeof body.data === 'object') {
            return { data: body.data };
        }
        return { data: body ?? {} };
    },
};
