import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import MediaPicker from '@/shared/components/ui/MediaPicker.vue';
import api from '@/engine/api/client';

vi.mock('@/engine/api/client', () => ({
    default: {
        get: vi.fn()
    }
}))

vi.mock('@/shared/composables/useToast', () => ({
    useToast: () => ({
        success: { action: vi.fn() },
        error: { validation: vi.fn(), action: vi.fn() }
    })
}))

const globalMocks = {
    global: {
        mocks: {
            $t: (k: string) => k
        },
        stubs: {
            Teleport: true,
            MediaUpload: true,
            Button: { template: '<button><slot /></button>' },
            ImageIcon: true,
            X: true,
            Home: true,
            ChevronRight: true,
            Grid: true,
            ListIcon: true,
            File: true,
            Folder: true,
            Search: true,
            ArrowUp: true,
            AlertCircle: true
        }
    }
}

describe('MediaPicker.vue', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        vi.mocked(api.get).mockResolvedValue({ data: { data: [], folders: [] } })
    })

    it('renders correctly with default trigger', () => {
        const wrapper = mount(MediaPicker, globalMocks)
        expect(wrapper.text()).toContain('media.modals.picker.select')
    })

    it('opens modal and fetches media when clicked', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        const btn = wrapper.find('button')
        await btn.trigger('click')

        expect((wrapper.vm as any).showModal).toBe(true)
        // Fetches folders and media in parallel
        expect(api.get).toHaveBeenCalledWith('/manage/folders', expect.objectContaining({
            params: expect.objectContaining({ module: expect.any(String) }),
        }))
    })

    it('handles folder navigation', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        await (wrapper.vm as any).openModal()

        vi.mocked(api.get).mockResolvedValue({
            data: {
                data: [{ id: "1", name: 'file.jpg' }],
                folders: [{ id: "2", name: 'Subfolder' }]
            }
        })

        await (wrapper.vm as any).navigateToFolder({ id: "2", name: 'Subfolder' })
        expect((wrapper.vm as any).currentFolderId).toBe('2')

        await (wrapper.vm as any).navigateToBreadcrumb(-1) // Back to root
        expect((wrapper.vm as any).currentFolderId).toBeNull()
    })

    it('toggles view mode', () => {
        const wrapper = mount(MediaPicker, globalMocks)
        expect((wrapper.vm as any).viewMode).toBe('grid')
            ; (wrapper.vm as any).viewMode = 'list'
        expect((wrapper.vm as any).viewMode).toBe('list')
    })

    it('selects and confirms media', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        const media = { id: "10", url: 'test.jpg' }
            ; (wrapper.vm as any).selectMedia(media)
        expect((wrapper.vm as any).selectedMediaId).toBe('10')

        await (wrapper.vm as any).confirmSelection()
        expect(wrapper.emitted('selected')).toHaveLength(1)
        expect(wrapper.emitted('selected')![0]).toEqual([media])
        expect((wrapper.vm as any).showModal).toBe(false)
    })

    it('handles close', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        await (wrapper.vm as any).openModal()
            ; (wrapper.vm as any).closeModal()
        expect((wrapper.vm as any).showModal).toBe(false)
    })

    it('handles media uploaded and selects it', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        const newMedia = { id: "20", url: 'new.jpg' }
        await (wrapper.vm as any).handleMediaUploaded({ media: newMedia })

        expect((wrapper.vm as any).mediaList[0].id).toBe('20')
        expect((wrapper.vm as any).selectedMediaId).toBe('20')
    })

    it('validates allowed extensions in selectMedia', async () => {
        const wrapper = mount(MediaPicker, {
            ...globalMocks,
            props: {
                constraints: { allowedExtensions: ['jpg', 'png'] }
            }
        })

        const invalidMedia = { id: "30", url: 'test.gif', extension: 'gif' }
        await (wrapper.vm as any).selectMedia(invalidMedia)
        expect((wrapper.vm as any).selectedMediaId).toBeNull()

        const validMedia = { id: "31", url: 'test.jpg', extension: 'jpg' }
        await (wrapper.vm as any).selectMedia(validMedia)
        expect((wrapper.vm as any).selectedMediaId).toBe('31')
    })

    it('navigates through breadcrumbs correctly', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        const folders = [
            { id: "1", name: 'F1' },
            { id: "2", name: 'F2' }
        ]
            ; (wrapper.vm as any).breadcrumbs = [...folders]

        await (wrapper.vm as any).navigateToBreadcrumb(0) // Go to F1
        expect((wrapper.vm as any).currentFolderId).toBe('1')
        expect((wrapper.vm as any).breadcrumbs).toHaveLength(1)
    })

    it('formats size and date correctly', () => {
        const wrapper = mount(MediaPicker, globalMocks)
        const vm = wrapper.vm as any

        expect(vm.formatSize(0)).toBe('0 B')
        expect(vm.formatSize(1024)).toBe('1 KB')
        expect(vm.formatSize(1024 * 1024)).toBe('1 MB')

        expect(vm.formatDate(undefined)).toBe('-')
        const date = '2024-01-01'
        expect(vm.formatDate(date)).toMatch(/1\/1\/2024|01\/01\/2024|1-1-2024|01-01-2024/);
    })

    it('handles pagination in fetchMedia', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        vi.mocked(api.get).mockResolvedValueOnce({ data: { data: [] } }) // Folders
        vi.mocked(api.get).mockResolvedValueOnce({
            data: {
                data: { data: [{ id: "100", url: 'paginated.jpg' }] } // Paginated media
            }
        })

        await (wrapper.vm as any).fetchMedia()
        expect((wrapper.vm as any).mediaList[0].id).toBe('100')
    })
})
