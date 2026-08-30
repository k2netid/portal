import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useSystemStore } from '@/modules/Core/System/stores/system'
import { config } from '@/config'
import type { SiteSettings } from '@/engine/types/settings'

export function trimStr(v: unknown): string {
    if (v == null) return ''
    if (typeof v !== 'string') return String(v).trim()
    return v.trim()
}

/**
 * Normalize Indonesian-style numbers for https://wa.me/{digits}
 */
export function toWhatsAppDialDigits(input: string): string {
    const d = input.replace(/\D/g, '')
    if (!d) return ''
    if (d.startsWith('62')) return d
    if (d.startsWith('0')) return `62${d.slice(1)}`
    if (d.startsWith('8')) return `62${d}`
    return d
}

/**
 * Theme Customizer overrides first, then Identity / public settings.
 */
export function useJanariIdentity() {
    const { t } = useI18n()
    const { getSetting } = useTheme()
    const systemStore = useSystemStore()
    const site = computed(() => systemStore.settings as unknown as SiteSettings)

    const displayEmail = computed(() => {
        const t = trimStr(getSetting('contact_email', ''))
        if (t) return t
        const s = trimStr(site.value.contact_email) || trimStr(site.value.admin_email)
        if (s) return s
        return `info@${config.appDomain}`
    })

    const displayPhone = computed(() => {
        const t = trimStr(getSetting('contact_phone', ''))
        if (t) return t
        return trimStr(site.value.contact_phone) || ''
    })

    const displayAddress = computed(() => {
        const t = trimStr(getSetting('contact_address', ''))
        if (t) return t
        return trimStr(site.value.contact_address) || ''
    })

    const displaySiteName = computed(() => {
        const fromTheme = trimStr(getSetting('site_title', ''))
        if (fromTheme) return fromTheme
        return trimStr(site.value.site_name) || t('common.labels.app.name')
    })

    const displaySiteDescription = computed(() => {
        const t = trimStr(getSetting('site_description', ''))
        if (t) return t
        return trimStr(site.value.site_description) || ''
    })

    const phoneDialHref = computed(() => {
        const p = displayPhone.value
        if (!p) return ''
        const digits = p.replace(/[^\d+]/g, '').replace(/^\+/, '')
        if (!digits) return ''
        return `tel:${digits}`
    })

    /** Public CTA: theme contact phone override, else identity phone */
    const whatsAppAdminDigits = computed(() => {
        const explicit = toWhatsAppDialDigits(trimStr(getSetting('contact_phone', '')))
        if (explicit) return explicit
        return toWhatsAppDialDigits(displayPhone.value)
    })

    const whatsAppAdminUrl = computed(() => {
        const d = whatsAppAdminDigits.value
        if (!d) return ''
        return `https://wa.me/${d}`
    })

    return {
        site,
        displayEmail,
        displayPhone,
        displayAddress,
        displaySiteName,
        displaySiteDescription,
        phoneDialHref,
        whatsAppAdminDigits,
        whatsAppAdminUrl,
    }
}
