import { describe, expect, it } from 'vitest';
import {
    LAYUNG_ISP_DEDICATED,
    LAYUNG_ISP_RETAIL_PLANS,
    LAYUNG_ISP_SOHO_PLANS,
} from '@/modules/Layout/views/themes/layung/composables/layungPricingPlans';

describe('layung pricing catalog', () => {
    it('exposes retail 10/15/20 and SOHO 50/100, not a starter-to-ultimate ladder', () => {
        expect(LAYUNG_ISP_RETAIL_PLANS.map((plan) => plan.tier)).toEqual(['Paket 10', 'Paket 15', 'Paket 20']);
        expect(LAYUNG_ISP_SOHO_PLANS.map((plan) => plan.tier)).toEqual(['SOHO 50', 'SOHO 100']);
        expect(LAYUNG_ISP_DEDICATED.contactSales).toBe(true);
        expect(LAYUNG_ISP_RETAIL_PLANS.map((plan) => plan.price)).toEqual([
            'Rp 150.000',
            'Rp 200.000',
            'Rp 250.000',
        ]);
    });
});
