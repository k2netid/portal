import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import i18n from '@/engine/i18n';
import Tim from '@/modules/Layout/views/themes/layung/pages/Tim.vue';
import CareerCenter from '@/modules/Layout/views/themes/layung/pages/CareerCenter.vue';

describe('Layung Tim and Career pages', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('renders Tim page with 4 operational pillars, escalation tiers, and hubs', async () => {
        const wrapper = mount(Tim, {
            global: {
                plugins: [i18n],
                stubs: {
                    'router-link': true,
                },
            },
        });

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.text()).toContain('Struktur Operasional');
        expect(wrapper.text()).toContain('Network Operations Center (NOC)');
        expect(wrapper.text()).toContain('Field & Fiber Engineering');
        expect(wrapper.text()).toContain('Solutions Architecture');
        expect(wrapper.text()).toContain('Customer Experience');
        expect(wrapper.text()).toContain('Tier 1');
        expect(wrapper.text()).toContain('Kantor Pusat & NOC Bandung');
    });

    it('renders CareerCenter page with benefits, open positions, and apply guide', async () => {
        const wrapper = mount(CareerCenter, {
            global: {
                plugins: [i18n],
                stubs: {
                    'router-link': true,
                },
            },
        });

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.text()).toContain('Mengapa Bergabung di K2NET?');
        expect(wrapper.text()).toContain('Sertifikasi Didukung');
        expect(wrapper.text()).toContain('NOC Network Engineer (L2)');
        expect(wrapper.text()).toContain('Fiber Optic Field Technician');
        expect(wrapper.text()).toContain('B2B Enterprise Account Executive');
        expect(wrapper.text()).toContain('Fullstack / Frontend Engineer');
        expect(wrapper.text()).toContain('Cara Mengirimkan Berkas Lamaran');
    });
});
