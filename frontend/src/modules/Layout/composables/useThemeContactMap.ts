import { computed, type ComputedRef, type Ref } from 'vue'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useSystemStore } from '@/modules/Core/System/stores/system'

type AddressSource = Ref<string> | ComputedRef<string>

/**
 * Shared Google Maps helpers for theme contact pages.
 * Reads `contact_map_*` settings (same keys as Janari theme customizer)
 * and supports precise GPS coordinates (lat,lng) to prevent pins drifting into water.
 */
export function useThemeContactMap(
  displayAddress: AddressSource,
  options?: {
    useDirectLink?: boolean
    coordinates?: Ref<string> | ComputedRef<string> | string
  },
) {
  const { getSetting } = useTheme()
  const systemStore = useSystemStore()
  const useDirectLink = options?.useDirectLink !== false

  const mapEnabled = computed(() => getSetting('contact_map_enabled', true) !== false)

  const mapSource = computed(() =>
    String(getSetting('contact_map_source', 'current_location') || 'current_location'),
  )

  const mapZoom = computed(() => {
    const raw = Number(getSetting('contact_map_zoom', 15))
    if (!Number.isFinite(raw)) return 15
    return Math.min(20, Math.max(10, Math.round(raw)))
  })

  const mapDirectLink = computed(() => {
    const raw = String(getSetting('contact_map_link', '') || '').trim()
    if (!raw) return ''
    return /^https?:\/\//i.test(raw) ? raw : ''
  })

  const mapCoordinates = computed(() => {
    // 0. Explicit option passed
    if (options?.coordinates) {
      const explicit = typeof options.coordinates === 'string'
        ? options.coordinates.trim()
        : String(options.coordinates.value || '').trim()
      if (explicit && /(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/.test(explicit)) {
        return explicit
      }
    }
    // 1. Theme override setting
    const fromTheme = String(getSetting('contact_coordinates', '') || '').trim()
    if (fromTheme && /(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/.test(fromTheme)) {
      return fromTheme
    }
    // 2. Global Site Settings
    const fromSite = String(
      (systemStore.siteSettings as Record<string, unknown> | undefined)?.contact_coordinates ||
      (systemStore.settings as Record<string, unknown> | undefined)?.contact_coordinates ||
      ''
    ).trim()
    if (fromSite && /(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/.test(fromSite)) {
      return fromSite
    }
    return ''
  })

  const mapQuery = computed(() => {
    if (useDirectLink && mapDirectLink.value) {
      try {
        const url = new URL(mapDirectLink.value)
        const q =
          url.searchParams.get('query') ||
          url.searchParams.get('q') ||
          url.searchParams.get('destination')
        if (q) return q
        const atMatch = url.pathname.match(/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/)
        if (atMatch) return `${atMatch[1]},${atMatch[2]}`
      } catch {
        /* ignore */
      }
    }

    // Coordinates guarantee 100% pinpoint accuracy without geocoding drifting into the ocean
    if (mapCoordinates.value) {
      return mapCoordinates.value
    }

    return String(displayAddress.value || '').trim()
  })

  const mapEmbedUrl = computed(() => {
    const q = encodeURIComponent(mapQuery.value || String(displayAddress.value || ''))
    return `https://www.google.com/maps?q=${q}&z=${mapZoom.value}&output=embed`
  })

  const mapExternalUrl = computed(() => {
    if (useDirectLink && mapDirectLink.value) return mapDirectLink.value
    const q = encodeURIComponent(mapQuery.value || String(displayAddress.value || ''))
    return `https://www.google.com/maps/search/?api=1&query=${q}`
  })

  const mapDirectionsUrl = computed(() => {
    if (useDirectLink && mapSource.value === 'link' && mapDirectLink.value) return mapDirectLink.value
    const destination = encodeURIComponent(mapQuery.value || String(displayAddress.value || ''))
    return `https://www.google.com/maps/dir/?api=1&destination=${destination}`
  })

  function openMapExternal(): void {
    const url = mapExternalUrl.value
    if (!url) return
    window.open(url, '_blank', 'noopener,noreferrer')
  }

  function openMapDirections(): void {
    const url = mapDirectionsUrl.value
    if (!url) return
    window.open(url, '_blank', 'noopener,noreferrer')
  }

  return {
    mapEnabled,
    mapCoordinates,
    mapQuery,
    mapEmbedUrl,
    mapExternalUrl,
    mapDirectionsUrl,
    openMapExternal,
    openMapDirections,
  }
}
