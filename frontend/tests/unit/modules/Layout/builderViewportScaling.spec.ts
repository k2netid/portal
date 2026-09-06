import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import CanvasFrame from '@/modules/Layout/components/builder/layout/CanvasFrame.vue'
import { ref } from 'vue'

describe('CanvasFrame Viewport Preview Scaling', () => {
  const mockBuilder = {
    showGrid: ref(false),
    content: ref({ slug: 'beranda-sekolah', title: 'Beranda' }),
  }

  const globalProvide = {
    global: {
      provide: {
        builder: mockBuilder,
      },
    },
  }

  it('renders desktop mode at 100% zoom with fluid full width and no macOS header', () => {
    const wrapper = mount(CanvasFrame, {
      props: {
        device: 'desktop',
        zoom: 100,
      },
      slots: {
        default: '<div class="test-canvas-content">Content</div>',
      },
      ...globalProvide,
    })

    expect(wrapper.classes()).toContain('items-stretch')
    expect(wrapper.classes()).toContain('p-0')

    const stageWrapper = wrapper.find('.canvas-frame__stage-wrapper')
    expect(stageWrapper.exists()).toBe(true)
    expect(stageWrapper.attributes('style')).toContain('width: 100%')
    expect(stageWrapper.attributes('style')).toContain('height: 100%')

    const viewport = wrapper.find('.canvas-frame__viewport--desktop')
    expect(viewport.exists()).toBe(true)
    expect(viewport.classes()).toContain('rounded-none')
    expect(viewport.classes()).toContain('border-0')
    expect(viewport.attributes('style')).toContain('transform: none')

    // macOS studio header should NOT be present when 100% unscaled
    expect(viewport.text()).not.toContain('1280px')
    expect(wrapper.find('.test-canvas-content').exists()).toBe(true)
  })

  it('renders desktop mode scaled at 75% zoom with macOS studio window frame and 1280px resolution badge', () => {
    const wrapper = mount(CanvasFrame, {
      props: {
        device: 'desktop',
        zoom: 75,
      },
      slots: {
        default: '<div class="test-canvas-content">Content</div>',
      },
      ...globalProvide,
    })

    expect(wrapper.classes()).toContain('items-start')

    const stageWrapper = wrapper.find('.canvas-frame__stage-wrapper')
    // 1280 * 0.75 = 960px
    expect(stageWrapper.attributes('style')).toContain('width: 960px')

    const viewport = wrapper.find('.canvas-frame__viewport--desktop')
    expect(viewport.classes()).toContain('rounded-2xl')
    expect(viewport.attributes('style')).toContain('scale(0.75)')

    // macOS header features
    expect(viewport.text()).toContain('1280px')
    expect(viewport.text()).toContain('beranda-sekolah')
  })

  it('renders tablet mode with iPad Pro frame, status bar, and home indicator', () => {
    const wrapper = mount(CanvasFrame, {
      props: {
        device: 'tablet',
        zoom: 100,
      },
      slots: {
        default: '<div class="test-canvas-content">Content</div>',
      },
      ...globalProvide,
    })

    const viewport = wrapper.find('.canvas-frame__viewport--tablet')
    expect(viewport.exists()).toBe(true)
    expect(viewport.classes()).toContain('rounded-[2.5rem]')

    // Status bar & home indicator
    expect(viewport.text()).toContain('9:41')
    expect(wrapper.find('.device-tablet-camera').exists() || wrapper.find('.rounded-full.bg-slate-950').exists()).toBe(true)

    // Stage wrapper matches tablet dimensions (768 + 28 = 796px)
    const stageWrapper = wrapper.find('.canvas-frame__stage-wrapper')
    expect(stageWrapper.attributes('style')).toContain('width: 796px')
  })

  it('renders mobile mode with iPhone 16 Pro Dynamic Island, status bar, and home indicator', () => {
    const wrapper = mount(CanvasFrame, {
      props: {
        device: 'mobile',
        zoom: 50,
      },
      slots: {
        default: '<div class="test-canvas-content">Content</div>',
      },
      ...globalProvide,
    })

    const viewport = wrapper.find('.canvas-frame__viewport--mobile')
    expect(viewport.exists()).toBe(true)
    expect(viewport.classes()).toContain('rounded-[3rem]')

    // Dynamic Island & status bar
    expect(viewport.text()).toContain('9:41')

    // Stage wrapper matches scaled mobile dimensions ((390 + 28) * 0.5 = 209px)
    const stageWrapper = wrapper.find('.canvas-frame__stage-wrapper')
    expect(stageWrapper.attributes('style')).toContain('width: 209px')
    expect(viewport.attributes('style')).toContain('scale(0.5)')
  })

  it('renders desktop in auto fit mode when zoom is 0', () => {
    const wrapper = mount(CanvasFrame, {
      props: {
        device: 'desktop',
        zoom: 0,
      },
      slots: {
        default: '<div class="test-canvas-content">Content</div>',
      },
      ...globalProvide,
    })

    const viewport = wrapper.find('.canvas-frame__viewport--desktop')
    expect(viewport.exists()).toBe(true)
    // In fit mode (zoom <= 0), macOS Studio header is shown with Fit tag
    expect(viewport.text()).toContain('Fit')
    expect(viewport.text()).toContain('beranda-sekolah')
  })
})
