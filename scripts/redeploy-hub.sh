#!/usr/bin/env bash
# Full redeploy after theme-system modernization (migrate, seed, build, sync).
set -euo pipefail
export PATH="/usr/bin:${PATH}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> 1/6 Backend: migrate"
cd "$ROOT/backend"
php artisan migrate --force

echo "==> 2/6 Seed console branding + demo plugin"
php artisan db:seed --class="Modules\\Core\\Database\\Seeders\\System\\ConsoleBrandingSettingsSeeder" --force 2>/dev/null || true
php artisan db:seed --class="Modules\\Core\\Database\\Seeders\\System\\ContentShareBarPluginSeeder" --force 2>/dev/null || true
php artisan db:seed --class="Modules\\Core\\Database\\Seeders\\System\\BeforeFooterPromoPluginSeeder" --force 2>/dev/null || true
php artisan db:seed --class="Modules\\Content\\Layout\\Database\\Seeders\\JanariHubThemeSettingsSeeder" --force 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan theme:staging-uploaded janari --revert 2>/dev/null || true
php artisan db:seed --class="Modules\\Operational\\Platform\\Database\\Seeders\\HubPublicCatalogSeeder" --force 2>/dev/null || true
php artisan db:seed --class="Modules\\Operational\\Member\\Database\\Seeders\\DemoMemberCustomerSeeder" --force 2>/dev/null || true

echo "==> 3/6 Backfill theme source + scan"
php artisan theme:backfill-source --force 2>/dev/null || true
php artisan db:seed --class="Modules\\Content\\Layout\\Database\\Seeders\\JanariHubMenuSeeder" --force 2>/dev/null || true
php artisan theme:scan-register 2>/dev/null || true

echo "==> 4/6 Laravel optimize clear"
php artisan optimize:clear
php artisan config:clear

echo "==> 5/6 Frontend build + sync (may take several minutes)"
cd "$ROOT"
npm run deploy:assets:full

echo "==> 6/6 Theme paths check"
cd "$ROOT/backend"
php artisan theme:paths
php artisan theme:validate janari 2>/dev/null || true

echo ""
echo "OK: Redeploy selesai."
echo "Coba:"
echo "  - Public: buka situs utama (tema Janari)"
echo "  - Console: /dash/settings/console-appearance"
echo "  - Themes: /dash/... themes index (upload ZIP jika FEATURE_UPLOADED_THEMES=true)"
