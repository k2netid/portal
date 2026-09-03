import { describe, it, expect, vi } from 'vitest';
import { getCanvasFingerprint } from '@/shared/utils/fingerprint';

describe('getCanvasFingerprint', () => {
    it('returns not-supported when getContext 2d is unavailable', () => {
        vi.spyOn(document, 'createElement').mockReturnValueOnce({
            getContext: vi.fn().mockReturnValue(null),
        } as any);

        expect(getCanvasFingerprint()).toBe('not-supported');
    });

    it('returns a hex hash string when canvas context is available', () => {
        const mockContext = {
            fillRect: vi.fn(),
            fillText: vi.fn(),
            beginPath: vi.fn(),
            arc: vi.fn(),
            stroke: vi.fn(),
        };

        vi.spyOn(document, 'createElement').mockReturnValueOnce({
            getContext: vi.fn().mockReturnValue(mockContext),
            toDataURL: vi.fn().mockReturnValue('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA'),
        } as any);

        const result = getCanvasFingerprint();
        expect(typeof result).toBe('string');
        expect(result).not.toBe('not-supported');
        expect(result).not.toBe('error');
        expect(result.length).toBeGreaterThan(0);
    });

    it('returns error when an exception is thrown', () => {
        vi.spyOn(document, 'createElement').mockImplementationOnce(() => {
            throw new Error('Canvas security violation');
        });

        expect(getCanvasFingerprint()).toBe('error');
    });
});
