# Theme App Blocks (Plugin Slots)

Public theme pages can expose **slots**; active plugins register Vue blocks into those slots.

## Add a plugin block

1. **Backend** — ensure plugin row exists (`sys_plugins`) with `is_active = true` and optional `settings.theme_blocks`:
   ```json
   [{ "slot": "after_post_content" }]
   ```
   Or rely on defaults in `PluginThemeBlocksService` for known slugs.

2. **Slot manifest** — add slot id to `slot-manifest.json` if new.

3. **Frontend loader** — create `loaders/myPlugin.ts`:
   ```ts
   import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';
   export const registerThemeBlocks: RegisterThemeBlocksFn = (register) => {
     register('after_post_content', { pluginSlug: 'my-plugin', component: MyBlock, priority: 10 });
   };
   ```
   Register in `loaders/index.ts`.

4. **Theme** — place `<PluginSlot name="after_post_content" :context="{ post_id }" />` in Janari (or other theme) templates.

5. **Bootstrap** — `pluginBootstrap.ts` loads manifest from `GET /api/v1/public/layout/plugin-blocks` and calls loaders for active slugs. Runs once in `main-public.ts` before `app.mount()` on the public shell.

## Built-in example

- Plugin: `content-share-bar`
- Loader: `loaders/contentShareBar.ts`
- Janari: `pages/Post.vue` → slot `after_post_content` (always rendered below body, not only when tags exist)

## Tests

- Unit: `pluginRegistry.spec.ts`, `PluginThemeBlocksServiceTest.php`
- Public theme E2E lives in downstream CMS repos (removed from kernel `main`).

## Admin: theme block slots per plugin

1. Open **Dev → Plugins** (`/dash/dev/plugins`).
2. Click **Settings** on an active plugin.
3. Check slots to expose; saved as `settings.theme_blocks` via `PUT /manage/system/plugins/{id}/settings`.
4. Backend validates slots against `layout.plugin_theme_slots` on save and activate.

## Adding a second bundled loader

See `loaders/beforeFooterPromo.ts` + `plugin_theme_blocks.php` entry `before-footer-promo`. Create a `sys_plugins` row (or seeder) with matching `slug` and activate it.

## CSS isolation (Pilar IV)

Plugin blocks render inside `.plugin-slot-isolate` / `.ja-plugin-block`. Prefer prefixed class names (`ja-plugin-block__title`) in third-party blocks; full Shadow DOM is optional later.

## Module-level registration

Add `src/modules/<Module>/plugins/themeBlocks.ts`:

```ts
export const themeBlockPluginSlug = 'my-plugin-slug';
export const registerThemeBlocks: RegisterThemeBlocksFn = (register) => { ... };
```

Discovered automatically via `resolveDiscoveredThemeBlockLoaders()` (merged with engine loaders).


## Remote blocks.js (optional, gated)

Off by default. Enable only when distributing plugin blocks outside the monorepo bundle.

**Backend**

```env
FEATURE_REMOTE_PLUGIN_BLOCKS=true
THEME_REMOTE_BLOCK_HOSTS=cdn.example.com
```

Plugin `settings.theme_blocks_remote_url` must be `https://<allowed-host>/.../blocks.js` (HTTPS + `.js` suffix). Manifest exposes `blocks_url` only after validation.

**CSP:** allowlisted hosts are added to `script-src` via `layout.remote_plugin_blocks.csp_script_hosts`.

**Frontend:** `pluginBootstrap` dynamic-imports `blocks_url` after the bundled loader runs. The remote module should call `registerThemeBlocks` (same contract as `loaders/*.ts`).
