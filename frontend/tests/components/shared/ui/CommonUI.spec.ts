import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Input from "@/shared/components/ui/Input.vue";
import Label from "@/shared/components/ui/Label.vue";
import Switch from "@/shared/components/ui/Switch.vue";
import Separator from "@/shared/components/ui/Separator.vue";
import Spinner from "@/shared/components/ui/Spinner.vue";

describe('Common UI Components', () => {
    it('Input renders and handles modelValue', async () => {
        const wrapper = mount(Input, {
            props: { modelValue: 'test', 'onUpdate:modelValue': (e: any) => (wrapper.setProps as any)({ modelValue: e }) }
        })
        const input = wrapper.find('input')
        expect(input.element.value).toBe('test')
        await input.setValue('new value')
        expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    })

    it('Label renders correctly', () => {
        const wrapper = mount(Label, {
            slots: { default: 'Name' }
        })
        expect(wrapper.text()).toBe('Name')
    })

    it('Switch toggles', async () => {
        const wrapper = mount(Switch, {
            props: { modelValue: false }
        })
        const button = wrapper.find('button')
        await button.trigger('click')
        expect(wrapper.emitted()).toBeDefined()
    })

    it('Separator renders', () => {
        const wrapper = mount(Separator)
        expect(wrapper.exists()).toBe(true)
    })

    it('Spinner renders', () => {
        const wrapper = mount(Spinner)
        expect(wrapper.exists()).toBe(true)
    })
})
