import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import TopToolbar from '@/modules/Layout/components/builder/layout/TopToolbar.vue'
import { ref } from 'vue'
import { setActivePinia, createPinia } from 'pinia'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string, fallback?: string) => fallback || key,
  }),
}))

describe('TopToolbar Zoom & Scale Controls', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })
  const createMockBuilder = () => {
    const zoom = ref(100)
    const device = ref<'desktop' | 'tablet' | 'mobile'>('desktop')
    const deviceModeType = ref<'auto' | 'manual'>('manual')
    return {
      zoom,
      device,
      deviceModeType,
      mode: ref('site'),
      content: ref({ status: 'draft' }),
      isDirty: ref(false),
      canUndo: ref(false),
      canRedo: ref(false),
      availableThemes: ref([]),
      activeTheme: ref('janari'),
      loadingThemes: ref(false),
      isFullscreen: ref(false),
      setDeviceModeAuto: vi.fn(),
      setDeviceMode: vi.fn(),
      fetchThemes: vi.fn(),
      undo: vi.fn(),
      redo: vi.fn(),
    }
  }

  const mountToolbar = (builderMock = createMockBuilder()) => {
    return mount(TopToolbar, {
      global: {
        provide: {
          builder: builderMock,
        },
        stubs: {
          AdminLogo: { template: '<div class="admin-logo-mock" />' },
        },
      },
    })
  }

  it('renders zoom controls with zoom level percentage', () => {
    const builder = createMockBuilder()
    const wrapper = mountToolbar(builder)

    const trigger = wrapper.find('.zoom-preset-trigger')
    expect(trigger.exists()).toBe(true)
    expect(trigger.text()).toContain('100%')
  })

  it('handles zoom in and zoom out clicks', async () => {
    const builder = createMockBuilder()
    const wrapper = mountToolbar(builder)

    // Find zoom in button
    const zoomInBtn = wrapper.find('button[title="Zoom In (+10%)"]')
    expect(zoomInBtn.exists()).toBe(true)
    await zoomInBtn.trigger('click')
    expect(builder.zoom.value).toBe(110)

    // Find zoom out button
    const zoomOutBtn = wrapper.find('button[title="Zoom Out (-10%)"]')
    expect(zoomOutBtn.exists()).toBe(true)
    await zoomOutBtn.trigger('click')
    expect(builder.zoom.value).toBe(100)
    await zoomOutBtn.trigger('click')
    expect(builder.zoom.value).toBe(90)
  })

  it('updates zoom via slider input', async () => {
    const builder = createMockBuilder()
    const wrapper = mountToolbar(builder)

    const slider = wrapper.find('.zoom-slider')
    expect(slider.exists()).toBe(true)

    await slider.setValue(75)
    await slider.trigger('input')
    expect(builder.zoom.value).toBe(75)
  })
})
