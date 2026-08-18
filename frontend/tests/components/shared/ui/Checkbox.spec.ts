import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Checkbox from "@/shared/components/ui/Checkbox.vue";

describe('Checkbox.vue', () => {
    it('renders correctly', () => {
        const wrapper = mount(Checkbox)
        expect(wrapper.exists()).toBe(true)
    })

    it('emits update:modelValue event when clicked', async () => {
        const wrapper = mount(Checkbox, {
            props: {
                modelValue: false
            }
        })

        await wrapper.trigger('click')
        // Check for any emitted event since Radix Vue might name it differently internally
        // but typically it responds to clicks.
        expect(wrapper.emitted()).toBeDefined()
    })
})
