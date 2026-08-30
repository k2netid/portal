import { ref } from 'vue'

const generateId = () => {
    return 'var_' + Date.now() + '_' + Math.floor(Math.random() * 1000)
}

export interface GlobalNumber {
    id: string;
    name: string;
    value: string;
    unit: string;
}

export interface GlobalText {
    id: string;
    name: string;
    value: string;
}

export interface GlobalImage {
    id: string;
    name: string;
    value: string | null;
}

export interface GlobalLink {
    id: string;
    name: string;
    value: string;
}

export interface GlobalColor {
    id: string;
    name: string;
    value: string;
    hex: string;
    opacity: number;
}

export interface GlobalFont {
    id: string;
    name: string;
    family: string;
}

export interface GlobalVariablesData {
    globalNumbers?: GlobalNumber[];
    globalText?: GlobalText[];
    globalImages?: GlobalImage[];
    globalLinks?: GlobalLink[];
    globalColors?: GlobalColor[];
    globalFonts?: GlobalFont[];
}

export function useGlobalVariables() {
    // State
    const globalNumbers = ref<GlobalNumber[]>([
        { id: generateId(), name: 'Spacing XS', value: '4', unit: 'px' },
        { id: generateId(), name: 'Spacing SM', value: '8', unit: 'px' },
        { id: generateId(), name: 'Spacing MD', value: '12', unit: 'px' },
        { id: generateId(), name: 'Spacing LG', value: '16', unit: 'px' },
        { id: generateId(), name: 'Spacing XL', value: '24', unit: 'px' },
        { id: generateId(), name: 'Spacing 2XL', value: '32', unit: 'px' },
        { id: generateId(), name: 'Radius SM', value: '4', unit: 'px' },
        { id: generateId(), name: 'Radius MD', value: '8', unit: 'px' },
        { id: generateId(), name: 'Radius LG', value: '12', unit: 'px' }
    ])

    const globalText = ref<GlobalText[]>([])
    const globalImages = ref<GlobalImage[]>([])
    const globalLinks = ref<GlobalLink[]>([])

    const globalColors = ref<GlobalColor[]>([
        { id: generateId(), name: 'Primary', value: '#2059ea', hex: '2059ea', opacity: 100 },
        { id: generateId(), name: 'Secondary', value: '#18b793', hex: '18b793', opacity: 100 },
        { id: generateId(), name: 'Dark', value: '#1a1e25', hex: '1a1e25', opacity: 100 },
        { id: generateId(), name: 'Light', value: '#ffffff', hex: 'ffffff', opacity: 100 },
        { id: generateId(), name: 'Muted', value: '#9da5b1', hex: '9da5b1', opacity: 100 },
        { id: generateId(), name: 'Border', value: '#e2e8f0', hex: 'e2e8f0', opacity: 100 }
    ])

    const globalFonts = ref<GlobalFont[]>([
        { id: generateId(), name: 'Headings', family: 'Instrument Sans' },
        { id: generateId(), name: 'Body', family: 'Inter' }
    ])

    // Actions
    const addVariable = (type: string) => {
        const id = generateId()
        switch (type) {
            case 'numbers':
                globalNumbers.value.push({ id, name: '', value: 'none', unit: '-' })
                break
            case 'text':
                globalText.value.push({ id, name: '', value: '' })
                break
            case 'images':
                globalImages.value.push({ id, name: '', value: null })
                break
            case 'links':
                globalLinks.value.push({ id, name: '', value: '' })
                break
            case 'colors':
                globalColors.value.push({ id, name: '', value: '#000000', hex: '000000', opacity: 100 })
                break
            case 'fonts':
                globalFonts.value.push({ id, name: '', family: 'Open Sans' })
                break
        }
        return id
    }

    const deleteVariable = (index: number, type: string) => {
        switch (type) {
            case 'numbers': globalNumbers.value.splice(index, 1); break
            case 'text': globalText.value.splice(index, 1); break
            case 'images': globalImages.value.splice(index, 1); break
            case 'links': globalLinks.value.splice(index, 1); break
            case 'colors': globalColors.value.splice(index, 1); break
            case 'fonts': globalFonts.value.splice(index, 1); break
        }
    }

    // reset/load methods could go here
    const loadVariables = (data: GlobalVariablesData) => {
        if (!data) return
        if (data.globalNumbers) globalNumbers.value = data.globalNumbers
        if (data.globalText) globalText.value = data.globalText
        if (data.globalImages) globalImages.value = data.globalImages
        if (data.globalLinks) globalLinks.value = data.globalLinks
        if (data.globalColors) globalColors.value = data.globalColors
        if (data.globalFonts) globalFonts.value = data.globalFonts
    }

    const getVariables = () => {
        return {
            globalNumbers: globalNumbers.value,
            globalText: globalText.value,
            globalImages: globalImages.value,
            globalLinks: globalLinks.value,
            globalColors: globalColors.value,
            globalFonts: globalFonts.value
        }
    }

    return {
        // State
        globalNumbers,
        globalText,
        globalImages,
        globalLinks,
        globalColors,
        globalFonts,

        // Actions
        addVariable,
        deleteVariable,
        loadVariables,
        getVariables
    }
}

export type GlobalVariablesManager = ReturnType<typeof useGlobalVariables>;
