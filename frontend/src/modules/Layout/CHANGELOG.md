# Changelog — Layout (FE)

## [Unreleased]

- Janari Customizer v8: Header chrome (official lines, login, news marquee); About stats/team CTA; Tim closing links; Contact map/form leftovers.
- Janari Customizer v7: Footer brand blurb, column titles, newsletter copy, legal links, copyright override.
- Janari Customizer v6: Search page chrome; News list headings/empty; Achievements filter labels.
- Janari Customizer v5: Pricing page chrome; News/Careers/Achievements titles + locale variants; Career sidebar + Achievement CTA editable.
- Janari Customizer v4: Partners + CTA sidebar entries; CTA secondary label + button URLs editable; logos still via bindings.
- Janari Customizer v3: Home Testimonials badge/title + Updates section titles/view-all editable via schema (item cards remain data bindings).
- Janari Customizer v2: Tim pillars/areas, About offerings, Solusi services/hub stack, Hero CTA labels, Contact UI labels — editable without code (empty = locale defaults).
- Builder isolation (phases 6–8): theme catalog from public routes; paginated pages fetch; create via builder prompt; About exclusive body; skip body→blocks on theme binds; Site Editor `edit content`; menu parents `/manage/layout/menus`; full revision reload; lock after bind.
- After Edit with Builder, empty theme binds show the builder empty workspace (not live Hero); Add Section opens section→row→column layout picker.
- Janari Customizer: Products / Solusi / Tim / About mission copy editable via schema (no code); sidebar pages for those sections.
- Builder isolation (phases 3–5): theme load is canvas-scoped (no public activate / `:root` CSS); page mode hides chrome + theme switcher; live preview uses absolute public origin.
- Builder context isolation (phases 0–2): Site Editor `noCache`; release edit lock on deactivate; stable `provide('builder')`.
- Theme tab keeps CMS id when previewing live Vue pages; Edit-with-Builder creates **draft** binds (not published empties); lookup by `meta.theme_page` + slug.
- Single save path (Site Editor toast-only); Page Settings marks dirty on meta edits; Create/Edit flush blocks before submit; no `markAsSaved` after failed persist.
- Empty pages stay empty in the builder; demo templates are library inserts only.
- Keyboard: Delete, Esc, Ctrl+D/C/V match Help. Site editor does not auto-open the first page.
- History panel lists saved server revisions. Canvas takes a content lock while open. Toolbar can generate layout blocks via Settings → AI.
- Lock banner + read-only canvas; AI append/replace; restore confirmation; AI type aliases.
- Canvas leaves render via public BlockRenderer; Layers Eye-off toggles sandboxed iframe preview. Save stamps `builder_schema_version`.
- Publishing overlay no longer double-saves; Create derives body from builder blocks.
- Public BlockRenderer + Janari Page body use SafeHtml (`publishing`).
- Janari SafeHtml uses `mode="publishing"` (legacy `Jejakawan` alias kept).
- Site Editor theme panel saves via theme settings API; deep-links to Theme Customizer + Menu Builder. Menu Builder links to customizer menus panel (`?panel=menus`). Public menu fetch accepts UUID when theme settings store `menu_location_*`.
- Site Editor theme panel uses merged customizer schema (`SettingControl`) + live apex `/` (or page slug) preview dialog.
- Theme tab shows live Janari Vue pages on the canvas instead of empty “create new” drafts.

## 1.1.0 — P3-3b

- ~500 files: builder, themes, customizer, content-renderer.
- Publishing imports Builder/BlockRenderer from `@/modules/Layout`.

## 1.0.0 — P3-3a

- Console views for menus, widgets, and redirects.
