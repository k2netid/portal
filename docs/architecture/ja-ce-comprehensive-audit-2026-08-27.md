# ja-CE comprehensive audit — 27 Aug 2026

**Status:** brief historis + peta residual (honesty pass kode sudah land)  
**Branch saat audit:** `fix/module-registry-p0-kernel-lock` → kini **`integrate/cms`**  
**Snapshot audit:** `2b0b8a7` · **Honesty land:** `3e343b1` (+ refine `3eefdc9`, builder `0a38d3a`)  
**Intent:** selaraskan dokumen ↔ kode, temukan bug nyata, dan petakan area refine **sebelum** P5 verticals.

P5 (modul produk vertikal bernama) **tetap ditunda**. Merge `integrate/cms` → `main` ditahan sampai quality gate lokal hijau dan residual di bawah (W5 naming + PR hygiene) selesai.

### Resolution (2026-08-28 → 30)

| Gelombang | Hasil |
| :--- | :--- |
| **W0** | Freeze tetap: jangan P5; merge `main` setelah gate |
| **W1–W3** | Landed di `3e343b1` — Identity owns `general`; uninstall respects deactivate; Member gated; theme pages resolve active theme; layout public menus/themes gated; Mail/cron skip when off |
| **W4** | Partial — docs/suite catch-up in same land + P2 refine; teruskan bila drift baru muncul |
| **W5** | **Partial** — pack keys default `publishing` (Media/Library); theme docs paths `Layout/*`; title fallbacks tanpa `JA Jejakawan`. Alias API `Jejakawan`→`publishing` tetap. Locale/extension map keys & seed brand `Jejakawan` sengaja. |
| **W6** | Tertutup sampai W5 + gate |

---

## 1. Tesis

Line CMS di kernel ini **hampir lengkap sebagai katalog pack**, tapi tiga lapisan belum selaras:

1. **Cerita produk vs sisa `ja-cms`.** Dual-boot, `/site`, `auth:member`, P4 Data Studio vs CCK, P6 settings, Mail SHOULD — itu sudah hidup di kode. Identifier, store, tema, dan beberapa rute masih bicara “Jejakawan / JA-CMS”.
2. **Registry sebagai saklar produk vs runtime yang masih selalu boot.** nwidart boot semua pack. `sys_extensions.status` mematikan sebagian API/menu/FE console. Public SPA, Member API, cron, dan beberapa listener **tidak ikut mati** dengan standar yang sama.
3. **Dokumen vs aktual.** Beberapa keputusan sudah di kode (port 5273, reserved slug `site`/`sites`, Publishing settings route) tapi docs/README/phpunit/manifest masih ketinggalan — atau sebaliknya, docs mengklaim P6 selesai sementara kernel Identity masih mengedit `general`.

Kesimpulan operasional: **jangan merge ke `main`, jangan P5.** Selesaikan “kontrak ja-CE jujur” dulu: satu owner per concern, satu gate per surface, satu nama per rute.

---

## 2. Apa yang sudah jujur (jangan dibongkar)

Ini **bukan** daftar utang. Ini fondasi yang sudah benar dan harus dipertahankan.

| Kontrak | Bukti |
| :--- | :--- |
| Dual boot: nwidart selalu boot, produk on/off = `sys_extensions.status` | `Extension::isProductActive()`, `EnsureExtensionActive` |
| Console IAM ≠ reader | `srv_auth_users` vs `mem_members` + `auth:member` |
| Mail = communications, bukan CMS | `OutboundMailPortInterface`; kernel SMTP tetap platform |
| First-party tidak di-uninstall | `ExtensionController::canUninstall()` + tes |
| Customized console menus sacred | hide by `extension_slug`, jangan hapus |
| Kernel thin; CMS = pack | Publishing / Library / Media / Layout / Forms / Newsletter / Analytics / Search / CmsAi / Member / Site |
| Public runtime = `/site`, console = `/dash` | `createWebHistory('/site')`, `backend/routes/web.php` |
| P4: Data Studio ≠ CCK | `ContentType::RESERVED_SLUGS` + FE mirror |
| P6 API: kernel menolak `seo` / `comments` / `analytics` | `Setting::PRODUCT_SETTING_GROUPS` + `SettingsApiTest` |
| Member verify-email | signed GET + resend; browser redirect ke `/site/member/verified` |
| Mail MIME + extension blocklist | `MailAttachmentStore::BLOCKED_MIME_TYPES` |

---

## 3. Agenda keputusan (urutan diskusi)

Sesi berikutnya idealnya **memutus owner**, bukan langsung PR. Usulan gelombang:

| Gelombang | Nama | Pertanyaan yang harus dijawab | Kalau setuju, baru dikerjakan |
| :--- | :--- | :--- | :--- |
| **W0** | Freeze | P5 tetap ditunda? Merge `main` tetap ditahan? | Ya — audit ini jadi backlog, bukan sprint vertikal |
| **W1** | Honesty / safety | Siapa owner `general` settings? Uninstall plugin harus gagal kalau deactivate gagal? | Kernel vs Publishing untuk `site_*`; guard uninstall |
| **W2** | Public theme contract | Activate theme = ganti seluruh `/site`, atau Zenith hardcoded adalah keputusan? | Resolver halaman + registrasi i18n tema |
| **W3** | Audience identity | Publishing `/member/*` Sanctum dihapus atau di-port ke `auth:member`? Member pack harus `extension.active`? | Satu surface member, satu guard |
| **W4** | Docs + tes | phpunit suite = semua pack yang punya tes? README modul wajib dipenuhi? | Docs catch-up, suite, module docs gap |
| **W5** | Naming residue | Store `Jejakawan`, string `JA-CMS`, group API palsu — bersihkan sekarang atau setelah W2? | Rename identifier, jangan sentuh customized menus |
| **W6** | P5 | Ada nama produk vertikal yang nyata? | Baru scaffold modul, bukan fork CCK ke kernel |

---

## 4. Matriks temuan (severity)

Severity di sini = **dampak produk / kontrak**, bukan “kode jelek”.

### P0 — kontrak publik rusak atau destruktif

Tidak ada crash i18n di jalur **live Zenith** (fallback bahasa Inggris ada). Yang P0 adalah **theme activate dusta** dan **uninstall plugin tidak aman**.

| ID | Temuan | Evidence | Arah (bukan patch) |
| :--- | :--- | :--- | :--- |
| **P0-1** | Activate tema **tidak** mengganti halaman `/site`. Header/Footer lewat `ThemePageResolver`; `public.ts` meng-hardcode halaman Zenith. | `frontend/src/engine/router/public.ts` L13–73 vs `FrontendLayout.vue` + `ThemePageResolver.vue` | Putuskan: (A) resolver per-page seperti header, atau (B) dokumentasikan Zenith-only sampai W2. Hari ini (A) dijanjikan UI, (B) yang terjadi. |
| **P0-2** | Uninstall plugin mengabaikan hasil `deactivate()`. Kalau reverse-dependent masih aktif, uninstall tetap rollback migrasi + hapus folder + `forceDelete`. | `ExtensionController::uninstall()` L315–348 | Cek return `deactivate` / panggil `assertCanDeactivate()` sebelum langkah destruktif. Tes skenario dependent. First-party sudah terblokir — ini utang **plugin**. |

### P1 — alur utama salah atau dual-owner

| ID | Temuan | Evidence | Arah |
| :--- | :--- | :--- | :--- |
| **P1-1** | Kernel Identity **tidak bisa baca** `general` dari index, tapi **bisa tulis** via bulk-update. Publishing juga owner `general`. Dual-write `site_name` dkk. | Kernel `SettingController::index` exclude `general`; `getGroup` **tidak** menolak `general`; `PRODUCT_SETTING_GROUPS` hanya `seo/comments/analytics`. FE Identity filter `group === 'general'`. Publishing `GROUPS = ['general','seo','comments']`. | Satu owner. Opsi A: `general` tetap kernel (Identity), Publishing hanya `seo`+`comments`. Opsi B: seluruh site identity pindah ke Publishing settings. Jangan dua-duanya. |
| **P1-2** | `PublishingService.publicComments` / `postPublicComment` hit `/publishing/contents/...` (404). Canonical = `/public/publishing/contents/...`. Janari + `BlockRenderer` pakai service rusak. Zenith `Post.vue` sudah benar. | `publishingService.ts` L30–42 vs `paths.ts` L9 vs `zenith/pages/Post.vue` L191 | Semua klien komentar lewat `publishingPaths.publicContentComments`. |
| **P1-3** | Publishing member API masih `auth:sanctum` + model `User` (`user_id` bookmarks/comments/newsletter). Member pack punya `auth:member` untuk me/bookmarks. Split brain. | `Publishing/routes/api.php` L25–37; `Member/routes/api.php` L18–25 | Deprecate rute Publishing `/member/*` atau tulis ulang di `mem_*`. Jangan biarkan dua semantik “member”. |
| **P1-4** | Member API **tanpa** `extension.active:member`. Pack opsional di manifest, API selalu hidup. | `Member/routes/api.php` | Samakan dengan Publishing manage: middleware registry. Putuskan apakah register/login publik ikut mati saat pack off. |
| **P1-5** | Logged-in console user di `/site` memicu `fetchSettingsGroup('Jejakawan')` + `'layout'` → Publishing 403. | `publishing.ts` L114–129; Publishing `GROUPS` hanya `general\|seo\|comments` | Public shell selalu `fetchPublicSettings()`. Jangan pakai manage API sebagai “public”. |
| **P1-6** | App Store Configure Publishing: `settings_route: "publishing"` di-push sebagai **nama rute** `publishing`. Rute sungguhan = `publishing-settings`. Analytics Configure → nama rute `analytics` (kebetulan dashboard) atau fallback kernel tab yang sudah dihapus. | `Publishing/manifest.json` L17; `extensions/Index.vue` L905–912, L888–896 | Manifest = **nama rute Vue yang ada**, atau path absolut. Tambah `publishing-settings` ke `ConsoleMenu::getDefaultMenus()`. |
| **P1-7** | Sanctum stateful default **tidak** memuat `:5273`. Vite default **5273**. CSP sudah whitelist 5273. | `backend/config/sanctum.php` L21–27; `frontend/vite.config.ts` L18 | Tambah `localhost:5273` + `127.0.0.1:5273` ke default + `.env.example`. |
| **P1-8** | Public layout **menus/themes** tidak di-gate registry; widgets sudah (`isProductActive('layout')`). | `Layout/routes/api.php` L16–20 vs `PublicWidgetPresenter` | Samakan: menus/themes kosong atau 404 saat layout inactive. |
| **P1-9** | `OutboundMailPortInterface` selalu di-bind (nwidart Mail always true). `MemberEmailVerification` mengira `app()->bound()` = pack aktif. | `MailServiceProvider`; `MemberEmailVerification.php` | Bind port hanya jika product-active, **atau** verifikasi member boleh lewat Laravel `Mail` tanpa pack (dokumentasikan sebagai kernel mail). Jangan samar. |

### P2 — refine, hardening, keselarasan

| ID | Temuan | Notes |
| :--- | :--- | :--- |
| **P2-1** | Theme i18n tidak terdaftar. Bundle ada di `Layout/views/themes/{zenith,janari}/locales/` tapi `moduleLocales.ts` hanya `layoutPack`. Zenith selamat karena fallback string. Janari banyak `t('theme.janari.*')` tanpa fallback → raw key **jika** header/footer Janari ter-resolve. | Docs `05-i18n-guidelines.md` masih path `modules/Content/Publishing` — stale parah. |
| **P2-2** | FE public hard-import Layout + Member + Analytics. Console `deferredConsoleModules` meng-gate pack. Public tidak. | `main-public.ts`; `usePublicAnalytics.ts` L36–37: `active.length > 0 && !includes` → array kosong = tracking tetap jalan. |
| **P2-3** | Newsletter footer selalu `subscribe`; rute 403 jika pack off. | Gate UI dari `active_extensions`. |
| **P2-4** | Cron/Artisan pack tidak cek `isProductActive` (contoh `PublishScheduledContentCommand`). | Pack “off” masih mutasi data lewat scheduler. |
| **P2-5** | Search listeners di-register hanya saat boot jika search active. Activate runtime tanpa restart worker = index dingin. | Pola queue/long-running. |
| **P2-6** | Kernel `/manage/ai/generate` tidak di-gate `cms-ai`; hanya flag `ai_enabled`. | Putuskan: kernel AI vs pack CmsAi. |
| **P2-7** | Data Studio reserved slugs tidak meng-grandfather row lama. | Audit migrasi sekali, atau lock read-only. |
| **P2-8** | Widget manifest Layout = 5 tipe; `LayoutServiceProvider` hardcode 8. `declaredWidgets()` tidak dikonsumsi. | Manifest = dokumentasi dusta. |
| **P2-9** | Member verify tidak memblokir token. Register tetap keluarkan Sanctum token. | Policy, bukan bug — dokumentasikan “soft verify” atau gate komentar/bookmark. |
| **P2-10** | `settings_route: "member"` tidak punya rute console. Permissions `view members` tanpa UI. | Pack audience public-only vs janji console. |
| **P2-11** | Site pack = stub provider + rewrite `/site`. Tidak ada FE module. | Sah jika Site = “host contract”, tapi docs harus bilang stub. |
| **P2-12** | phpunit `Modules` suite: Core, Mail, Member, Site, Forms, Publishing. **Tidak** include Analytics, Search, CmsAi tes yang sudah ada. Layout/Media/Library/Newsletter hampir tanpa tes pack-local. Publishing hanya settings. | CI lokal pun buta. |
| **P2-13** | README root masih Vite **5173**. | `README.md` L107, L132; e2e compose. |
| **P2-14** | `data-studio-vs-cck.md` belum list `site`/`sites`. | Kode sudah. |
| **P2-15** | Laravel docs 12/13 campur; lock **13.26**. PHP docs 8.3+ vs composer `^8.2`. Manifests `laravel: ">=12.0"`. | Rapikan klaim. |
| **P2-16** | Naming: Pinia id `'Jejakawan'`, SafeHtml mode, license map, dashboard ids, string user-facing `JA-CMS` di Zenith CTA. | Melanggar `AGENT_START_HERE.md` L41. |
| **P2-17** | Kernel Identity tab Media = S3/FTP; Media pack = library. Dua “media settings”. | Dokumentasikan split atau pindahkan storage ke pack. |
| **P2-18** | Orphan `SeoTab.vue` / `DiscussionTab.vue` / `AnalyticsTab.vue` di Core settings. | Publishing sudah reuse SEO/discussion. Hapus atau alias. |

**Refine pass 2026-08-28:** landed P2-2, P2-7, P2-9, P2-10, P2-12, P2-15, P2-16 (SafeHtml mode), P2-17, P2-18. P5 still deferred.

---

## 5. Docs vs kode (drift yang harus dibahas)

### 5.1 Klaim dokumen yang stale

| Klaim | Dokumen | Aktual |
| :--- | :--- | :--- |
| Vite :5173 | `README.md`, e2e compose | `vite.config.ts` default **5273** |
| P6 “kernel tidak punya SEO/discussion/analytics” | `architectural-status.md` | API ya; **Identity masih `general`**; tab Analytics kernel sudah mati tapi App Store map masih `tab=analytics` |
| Reserved slugs tanpa `site`/`sites` | `data-studio-vs-cck.md` | `ContentType::RESERVED_SLUGS` + FE mirror sudah |
| i18n path `modules/Content/...` | `05-i18n-guidelines.md` | Pack di `frontend/src/modules/{Publishing,Layout,...}` |
| PHP 8.3+ | overview docs | Aligned: PHP 8.2+ / Laravel 13 |
| Laravel 12/13 | overview + manifests | Pack manifests `laravel: ">=13.0"` |
| Console slug `/dash` vs seed `ja-dash` | status vs README login | Constant FE `dash`; seed/UI default `ja-dash` — dua kebenaran |
| `settings_route` = console route | `module-contract.md` | Publishing `"publishing"` ≠ `publishing-settings` |
| Setiap optional module punya `frontend/.../module.ts` | contract | Member now has console `module.ts`; Site remains host-only |

### 5.2 Perilaku kode yang belum didokumentasikan

- Member API tidak memakai `extension.active:member`.
- Site = host `/site`, bukan pack UI.
- Public widgets di-gate di presenter; menus/themes tidak.
- Alias query `module=Jejakawan` → `publishing` di `PublicWidgetPresenter`.
- Publishing memiliki group `general` (tumpang tindih kernel Identity).
- Media `settings_route` vs kernel storage tab.
- Core tidak punya `manifest.json` (fallback `module.json`).
- `fetchSettingsGroup('Jejakawan')` adalah group yang tidak ada.

### 5.3 Module README / CHANGELOG (rule `.cursor/rules/module-documentation.mdc`)

**Backend**

| Pack | README | CHANGELOG |
| :--- | :---: | :---: |
| Core | tidak | ya |
| Member | tidak | tidak |
| Site | tidak | tidak |
| sisanya (10 pack) | ya | ya |

**Frontend**

| Pack | README | CHANGELOG | `module.ts` |
| :--- | :---: | :---: | :---: |
| Core | tidak | tidak | `Core/System/module.ts` |
| Media | tidak | tidak | ya |
| Member | tidak | tidak | tidak (public only) |
| Publishing | ya | tidak | ya |
| Library | ya | tidak | ya |
| Site | N/A | N/A | N/A |
| Analytics, CmsAi, Forms, Layout, Mail, Newsletter, Search | ya | ya | ya |

---

## 6. Pack coupling (deklarasi vs runtime)

Manifest `dependencies` relatif jujur untuk Publishing→Library, Layout→Publishing, Search→Publishing+Library, Site→Layout+Publishing.

Yang **lebih kuat dari manifest** (hard import PHP/FE):

| From | To | Bentuk |
| :--- | :--- | :--- |
| Layout | Publishing | `Content` model di DynamicTag / Builder / widget presenter |
| Library | Publishing | Eloquent `contents()` |
| Search | Publishing | events + read port |
| Analytics | Publishing | analytics port |
| Newsletter | Publishing | sample/count ports |
| Member | Publishing | `MemberIdentityPort` (arah benar: port) |
| Publishing | Member | optional port, fallback guest — **ini pola yang harus ditiru** |
| CmsAi | Publishing | draft/taxonomy |
| Public SPA | Layout + Member | static import, tidak deferred |

**Dual registry:** `modules_statuses.json` semua `true` → provider, migrasi, model selalu ada. Mematikan pack = middleware + menu + sebagian observer + FE console. Artisan, service resolution, dan public hard-import lolos.

Itu bukan bug dual-boot — itu **kontrak yang belum selesai diimplementasi di tepi**.

---

## 7. Tes: apa yang melindungi kita, apa yang tidak

**Masuk suite `phpunit.xml`:** Core, Mail, Member, Site, Forms, Publishing (hanya settings).

**Ada di disk, tidak di suite:** Analytics, Search, CmsAi.

**Hampir tanpa tes pack:** Layout (hanya tes Core: widget runtime + manifest), Media, Library, Newsletter. Publishing tidak menguji content/comment/SEO/inactive-gate.

**Skenario bernilai tinggi yang belum ada:**

- Uninstall plugin dengan dependent aktif
- `extension.active:*` per pack (Forms sudah jadi contoh)
- Member API saat `member` inactive
- Public menus/themes saat `layout` inactive
- Console menu memuat `publishing-settings` setelah activate
- Widget manifest ↔ runtime registry
- Scheduled publish saat publishing inactive
- FE: `PublishingService` comment path; theme locale registration

---

## 8. Rekomendasi posisi (untuk ditantang, bukan dikunci)

Pendapat agent untuk sesi dewa — **boleh ditolak**:

1. **W1 dulu, W5 belakangan.** Dual-write `general` dan uninstall plugin lebih berbahaya daripada string `JA-CMS` di hero Zenith.
2. **Owner `general` = kernel Identity.** Publishing settings = `seo` + `comments` saja. Site name/tagline adalah platform, bukan CMS — downstream non-CMS tetap butuh Identity. Ini selaras “kernel thin tapi bukan kernel kosong”.
3. **W2 harus keputusan sadar.** Kalau `/site` Zenith-only sampai tema kedua siap, tulis itu di `architectural-status` dan sembunyikan “Activate Janari” sebagai preview builder, bukan runtime publik. Kalau Activate harus nyata, halaman harus lewat resolver yang sama dengan Header.
4. **Publishing `/member/*` Sanctum dihapus**, bukan di-port. Member pack sudah jadi owner. Sisakan `MemberIdentityPort`.
5. **Member pack off = API publik member mati.** Kalau tidak, App Store toggle dusta.
6. **Mail port:** verifikasi email member adalah **communications kernel-capable**. Boleh fallback `Mail::html` tanpa pack; jangan pura-pura gate-by-bind.
7. **phpunit suite expansion** adalah PR kecil, ROI besar — lakukan di W4 tanpa drama arsitektur.
8. **P5 tetap tertutup** sampai W1–W4 punya owner tertulis di status doc.

---

## 9. Yang sengaja tidak dikerjakan dalam audit ini

- Tidak ada patch, tidak ada commit, tidak ada PR.
- Tidak ada scaffold vertikal (P5).
- Tidak merge ke `main`.
- Tidak menyalakan GitHub Actions (billing).
- Tidak menghapus customized console menus.
- Tidak mengubah first-party uninstall policy.

---

## 10. Bahan bacaan cepat sebelum diskusi

- [architectural-status.md](../architectural-status.md)
- [lifecycle.md](../extensions/lifecycle.md)
- [module-contract.md](../extensions/module-contract.md)
- [data-studio-vs-cck.md](data-studio-vs-cck.md)
- [AGENT_START_HERE.md](../AGENT_START_HERE.md)
- Canvas: workspace `canvases/ja-ce-comprehensive-audit.canvas.tsx`

---

*Audit dikompilasi 2026-08-27 dari kode di branch registry, dikonfirmasi di path yang dikutip. Temuan agen yang tidak lolos verifikasi (mis. crash i18n `@` linked-message, collision POST bookmarks) tidak masuk daftar di atas.*
