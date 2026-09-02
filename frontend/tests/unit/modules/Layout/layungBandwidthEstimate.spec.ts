import { describe, expect, it } from 'vitest';
import {
    WORKLOAD_MBPS,
    concurrencyRatio,
    diaSpeedLabel,
    estimatePeakMbps,
    recommendPlanId,
} from '@/modules/Layout/views/themes/layung/composables/layungBandwidthEstimate';

describe('layung bandwidth estimate', () => {
    it('uses stepped concurrency, not 100% simultaneous load', () => {
        expect(concurrencyRatio(8)).toBe(0.85);
        expect(concurrencyRatio(20)).toBe(0.75);
        expect(concurrencyRatio(50)).toBe(0.65);
        expect(concurrencyRatio(100)).toBe(0.55);
        expect(concurrencyRatio(200)).toBe(0.45);
    });

    it('rounds peak Mbps with 25% headroom and a 10 Mbps floor', () => {
        // 10 users → round(10×0.85)=9 concurrent × 2 Mbps × 1.25 = 22.5 → 23
        expect(estimatePeakMbps(10, WORKLOAD_MBPS.standard)).toBe(23);
        expect(estimatePeakMbps(1, WORKLOAD_MBPS.standard)).toBe(10);
    });

    it('maps retail force-segment by count or Mbps', () => {
        expect(
            recommendPlanId({ segment: 'retail', userCount: 3, mbps: 12, workload: 'standard' }),
        ).toBe('retail-10');
        expect(
            recommendPlanId({ segment: 'retail', userCount: 7, mbps: 20, workload: 'standard' }),
        ).toBe('retail-15');
        expect(
            recommendPlanId({ segment: 'retail', userCount: 12, mbps: 30, workload: 'standard' }),
        ).toBe('retail-20');
    });

    it('maps auto-mode small office to retail, heavy lab to DIA', () => {
        expect(
            recommendPlanId({ segment: 'auto', userCount: 4, mbps: 10, workload: 'standard' }),
        ).toBe('retail-10');
        expect(
            recommendPlanId({ segment: 'auto', userCount: 80, mbps: 200, workload: 'heavy' }),
        ).toBe('dia');
    });

    it('formats DIA capacity in 25 Mbps steps', () => {
        expect(diaSpeedLabel(40)).toBe('50 Mbps');
        expect(diaSpeedLabel(51)).toBe('75 Mbps');
        expect(diaSpeedLabel(1000)).toBe('1.0 Gbps');
    });
});
