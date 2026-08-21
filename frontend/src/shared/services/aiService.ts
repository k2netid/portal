import apiClient from '@/engine/api/client';

export interface AiGeneratePayload {
    prompt: string;
    context?: string;
    provider?: string;
    model?: string;
    action?: string;
}

export const AiService = {
    async providers(): Promise<{ data: Array<{ id: string; name: string; logo?: string }> }> {
        try {
            const res = await apiClient.get('/manage/system/ai/providers');
            return res.data;
        } catch {
            return { data: [] };
        }
    },

    async models(provider: string): Promise<{ data: Array<{ id: string; name: string }> }> {
        try {
            const res = await apiClient.get(`/manage/system/ai/models?provider=${encodeURIComponent(provider)}`);
            return res.data;
        } catch {
            return { data: [] };
        }
    },

    async generate(payload: AiGeneratePayload): Promise<{ data: { content?: string } }> {
        try {
            const res = await apiClient.post('/manage/system/ai/generate', payload);
            return res.data;
        } catch {
            return { data: {} };
        }
    },
};
