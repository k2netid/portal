#!/usr/bin/env bash
# Scaffold an optional first-party module (Mail contract) for ja-core_engine.
# Usage: bash scripts/scaffold-optional-module.sh <slug> "<Display Name>" "<Description>"
# Example: bash scripts/scaffold-optional-module.sh forms "Forms" "Dynamic forms and submissions"
set -euo pipefail

SLUG="${1:-}"
DISPLAY_NAME="${2:-}"
DESCRIPTION="${3:-}"

if [[ -z "$SLUG" || -z "$DISPLAY_NAME" || -z "$DESCRIPTION" ]]; then
  echo "Usage: $0 <slug> \"<Display Name>\" \"<Description>\"" >&2
  echo "Example: $0 forms \"Forms\" \"Dynamic forms and submissions\"" >&2
  exit 1
fi

if [[ ! "$SLUG" =~ ^[a-z][a-z0-9_-]*$ ]]; then
  echo "error: slug must match ^[a-z][a-z0-9_-]*$" >&2
  exit 1
fi

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# forms -> Forms, cms-ai -> CmsAi
STUDLY="$(echo "$SLUG" | sed -r 's/(^|_|-)([a-z])/\U\2/g')"
BACKEND_MODULE="$ROOT/backend/Modules/$STUDLY"
FRONTEND_MODULE="$ROOT/frontend/src/modules/$STUDLY"
PERM_SLUG="${SLUG//-/_}"

if [[ -d "$BACKEND_MODULE" ]]; then
  echo "error: backend module already exists at $BACKEND_MODULE" >&2
  exit 1
fi
if [[ -d "$FRONTEND_MODULE" ]]; then
  echo "error: frontend module already exists at $FRONTEND_MODULE" >&2
  exit 1
fi

echo "==> Backend Modules/$STUDLY (slug=$SLUG)"
mkdir -p "$BACKEND_MODULE"/{app/Providers,app/Http/Controllers,routes,database/migrations,tests/Feature}

cat > "$BACKEND_MODULE/module.json" <<EOF
{
  "name": "$STUDLY",
  "alias": "$SLUG",
  "description": "$DESCRIPTION",
  "keywords": ["$SLUG"],
  "priority": 10,
  "providers": [
    "Modules\\\\$STUDLY\\\\Providers\\\\${STUDLY}ServiceProvider"
  ],
  "files": []
}
EOF

cat > "$BACKEND_MODULE/manifest.json" <<EOF
{
  "name": "$DISPLAY_NAME",
  "slug": "$SLUG",
  "version": "1.0.0",
  "description": "$DESCRIPTION",
  "author": "Jejakawan Team",
  "type": "module",
  "is_core": false,
  "license": "Commercial PRO",
  "license_tier": "pro",
  "settings_route": "$SLUG",
  "features": []
}
EOF

cat > "$BACKEND_MODULE/app/Providers/${STUDLY}ServiceProvider.php" <<EOF
<?php

declare(strict_types=1);

namespace Modules\\$STUDLY\\Providers;

use Illuminate\\Support\\ServiceProvider;

class ${STUDLY}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadMigrationsFrom(module_path('$STUDLY', 'database/migrations'));
        \$this->loadRoutesFrom(module_path('$STUDLY', 'routes/api.php'));
    }
}
EOF

cat > "$BACKEND_MODULE/routes/api.php" <<EOF
<?php

declare(strict_types=1);

use Illuminate\\Support\\Facades\\Route;

Route::prefix('api/v1/manage/$SLUG')
    ->middleware(['auth:sanctum', 'extension.active:$SLUG', 'permission:use $PERM_SLUG'])
    ->group(function (): void {
        Route::get('/health', static fn () => response()->json([
            'success' => true,
            'slug' => '$SLUG',
            'message' => '$DISPLAY_NAME module is mounted (activate in Module Registry)',
        ]));
    });
EOF

cat > "$BACKEND_MODULE/README.md" <<EOF
# $DISPLAY_NAME (\`$SLUG\`)

Optional first-party module — contract: \`docs/extensions/module-contract.md\`.

## Wire-up

1. PSR-4 in \`backend/composer.json\`: \`"Modules\\\\$STUDLY\\\\": "Modules/$STUDLY/app/"\`
2. \`composer dump-autoload\`
3. \`backend/modules_statuses.json\`: \`"$STUDLY": true\`
4. Seed permission \`use $PERM_SLUG\`
5. FE: add slug to \`OPTIONAL_FIRST_PARTY\` in \`deferredConsoleModules.ts\`
6. Module Registry → Activate

Packaging modes: \`docs/extensions/external-module-packaging.md\`.
EOF

cat > "$BACKEND_MODULE/CHANGELOG.md" <<EOF
# Changelog — $DISPLAY_NAME

## [Unreleased]

### Added

- Scaffold via \`scripts/scaffold-optional-module.sh\`
EOF

echo "==> Frontend src/modules/$STUDLY"
mkdir -p "$FRONTEND_MODULE"/{views,router}

cat > "$FRONTEND_MODULE/module.ts" <<EOF
import type { AppModule } from '@/engine/types/module';
import routes from './router';
import { navigation } from './navigation';

export const ${STUDLY}Module: AppModule = {
  id: '$SLUG',
  name: '$DISPLAY_NAME',
  extensionSlug: '$SLUG',
  routes,
  navigation,
};

export default ${STUDLY}Module;
EOF

cat > "$FRONTEND_MODULE/index.ts" <<EOF
import { ${STUDLY}Module } from './module';

/** Export name used by deferredConsoleModules loaders — keep in sync when wiring. */
export const ${STUDLY}Modules = [${STUDLY}Module];

export { ${STUDLY}Module };
EOF

cat > "$FRONTEND_MODULE/navigation.ts" <<EOF
import type { NavItem } from '@/shared/utils/navigation';

export const navigation: NavItem[] = [
  {
    name: '$SLUG',
    label: '$DISPLAY_NAME',
    icon: 'puzzle',
    group: 'operations',
    to: '/$SLUG',
    extension: '$SLUG',
    permission: 'use $PERM_SLUG',
  },
];
EOF

cat > "$FRONTEND_MODULE/router/index.ts" <<EOF
import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '$SLUG',
    name: '$SLUG',
    component: () => import('../views/Index.vue'),
    meta: {
      extension: '$SLUG',
      permission: 'use $PERM_SLUG',
      title: '$DISPLAY_NAME',
    },
  },
];

export default routes;
EOF

cat > "$FRONTEND_MODULE/views/Index.vue" <<EOF
<template>
  <div class="p-6">
    <h1 class="text-xl font-semibold">$DISPLAY_NAME</h1>
    <p class="text-sm text-muted-foreground mt-2">
      Optional module skeleton — replace with real UI. Slug: <code>$SLUG</code>
    </p>
  </div>
</template>

<script setup lang="ts">
</script>
EOF

cat > "$FRONTEND_MODULE/README.md" <<EOF
# $DISPLAY_NAME (frontend)

\`AppModule.id\` / \`extensionSlug\` = \`$SLUG\`.

Register via \`OPTIONAL_FIRST_PARTY\` in \`frontend/src/engine/bootstrap/deferredConsoleModules.ts\`.
EOF

cat > "$FRONTEND_MODULE/CHANGELOG.md" <<EOF
# Changelog — $DISPLAY_NAME (frontend)

## [Unreleased]

### Added

- Scaffold via \`scripts/scaffold-optional-module.sh\`
EOF

echo ""
echo "==> Next manual steps"
echo "1. Add PSR-4: Modules\\\\$STUDLY\\\\ -> Modules/$STUDLY/app/  in backend/composer.json"
echo "2. composer dump-autoload  (from backend/)"
echo "3. modules_statuses.json: \"$STUDLY\": true"
echo "4. Seed permission: use $PERM_SLUG"
echo "5. deferredConsoleModules.ts OPTIONAL_FIRST_PARTY += { slug: '$SLUG', load: () => import('@/modules/$STUDLY') }"
echo "   (adjust export: ${STUDLY}Modules)"
echo "6. Module Registry → Activate"
echo "Docs: docs/extensions/external-module-packaging.md"
echo "Done."
