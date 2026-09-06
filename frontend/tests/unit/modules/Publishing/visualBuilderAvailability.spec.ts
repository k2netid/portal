import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { setActivePinia, createPinia } from 'pinia';
import ContentMain from '@/modules/Publishing/components/content/ContentMain.vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import type { ContentForm } from '@/modules/Publishing/types/content';

vi.mock('vue-i18n', async (importOriginal) => {
  const actual = await importOriginal<typeof import('vue-i18n')>();
  return {
    ...actual,
    useI18n: () => ({
      t: (key: string, fallbackOrParams?: unknown) => {
        if (typeof fallbackOrParams === 'string') return fallbackOrParams;
        return key;
      },
    }),
  };
});

describe('ContentMain Visual Builder Extension Gating', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  const defaultModelValue: ContentForm = {
    title: 'Test Article',
    slug: 'test-article',
    type: 'article',
    status: 'draft',
    body: '<p>Sample text</p>',
    meta: {},
  };

  const mountComponent = (modelValue = defaultModelValue) => {
    return mount(ContentMain, {
      props: {
        modelValue,
      },
      global: {
        mocks: {
          $t: (key: string, fallback?: string) => fallback || key,
        },
        stubs: {
          TiptapEditor: { template: '<div class="tiptap-mock" />' },
          'router-link': {
            props: ['to'],
            template: '<a class="router-link-mock" :data-to="JSON.stringify(to)"><slot /></a>',
          },
        },
      },
    });
  };

  it('renders visual builder button and emits open-builder when extension is active', async () => {
    const systemStore = useSystemStore();
    systemStore.activeExtensions = ['publishing', 'layout', 'visual-builder'];

    const wrapper = mountComponent();

    // Check banner title
    expect(wrapper.text()).toContain('Visual Page & Block Builder');

    // Builder button should exist
    const builderBtn = wrapper.find('button.bg-primary');
    expect(builderBtn.exists()).toBe(true);
    expect(builderBtn.text()).toContain('Rancang dengan Visual Builder');

    // Inactive badge should NOT exist
    expect(wrapper.text()).not.toContain('Modul Tidak Aktif');

    // Clicking builder button emits open-builder
    await builderBtn.trigger('click');
    expect(wrapper.emitted('open-builder')).toBeTruthy();
  });

  it('hides builder button and displays inactive badge + extension settings link when extension is inactive', async () => {
    const systemStore = useSystemStore();
    systemStore.activeExtensions = ['publishing', 'layout']; // visual-builder missing

    const wrapper = mountComponent();

    // Inactive badge should be visible
    expect(wrapper.text()).toContain('Modul Tidak Aktif');
    expect(wrapper.text()).toContain('Modul Visual Builder sedang nonaktif');

    // Launch button should not be rendered
    const builderBtn = wrapper.find('button.bg-primary');
    expect(builderBtn.exists()).toBe(false);

    // Settings link should be present pointing to extensions.index
    const settingsLink = wrapper.find('.router-link-mock');
    expect(settingsLink.exists()).toBe(true);
    expect(settingsLink.attributes('data-to')).toContain('extensions.index');
  });

  it('preserves Tiptap editor fallback when visual-builder is inactive even with builder_blocks present', async () => {
    const systemStore = useSystemStore();
    systemStore.activeExtensions = [];

    const wrapper = mountComponent({
      ...defaultModelValue,
      meta: {
        builder_blocks: [{ id: 'b1', type: 'hero', settings: {} }],
      },
    });

    // Tiptap is still rendered
    expect(wrapper.find('.tiptap-mock').exists()).toBe(true);
    // Shows blocks badge count
    expect(wrapper.find('.router-link-mock').exists()).toBe(true);
  });
});
