# Changelog — Core (frontend)

## [Unreleased]

### Fixed
- App Store Configure uses real Vue route names (`publishing-settings`, `analytics`).
- Identity settings refresh `general` after save.
- Navigation Store & Shell: Preload database console menus upon successful authentication in `Login.vue` and add resilient fallback hydration in `TheSidebar.vue` to ensure ordered sidebar menus without requiring a page refresh (`F5`).
- i18n & Locale Resolution: Set site default language on initial load strictly to `id` (Bahasa Indonesia) by preventing browser language sniffing (`en-US`) from overriding the default when no user preference is stored.


