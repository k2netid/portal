import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Button from "@/shared/components/ui/Button.vue";

describe('Button.vue', () => {
    it('renders correctly with default slot', () => {
        const wrapper = mount(Button, {
            slots: {
                default: 'Click me'
            }
        })
        expect(wrapper.text()).toBe('Click me')
        expect(wrapper.attributes('type')).toBe('button')
    })

    it('applies variant classes', () => {
        const wrapper = mount(Button, {
            props: { variant: 'destructive' }
        })
        expect(wrapper.attributes('data-variant')).toBe('destructive')
    })

    it('applies size classes', () => {
        const wrapper = mount(Button, {
            props: { size: 'sm' }
        })
        expect(wrapper.attributes('data-size')).toBe('sm')
    })

    it('handles custom class', () => {
        const wrapper = mount(Button, {
            props: { class: 'custom-btn' }
        })
        expect(wrapper.classes()).toContain('custom-btn')
    })
})
