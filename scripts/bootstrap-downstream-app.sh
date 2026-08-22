#!/usr/bin/env bash
# Scaffold a downstream app from ja-core_engine kernel.
# Usage: bash scripts/bootstrap-downstream-app.sh my-product-id "My Product Name"
set -euo pipefail

PRODUCT_ID="${1:-}"
PRODUCT_NAME="${2:-}"

if [[ -z "$PRODUCT_ID" || -z "$PRODUCT_NAME" ]]; then
  echo "Usage: $0 <product_id> \"<Product Name>\"" >&2
  echo "Example: $0 ja-cms \"Jejakawan CMS\"" >&2
  exit 1
fi

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MODULE_CLASS="$(echo "$PRODUCT_ID" | sed -r 's/(^|-)([a-z])/\U\2/g;s/-//g')"
BACKEND_MODULE="$ROOT/backend/Modules/$MODULE_CLASS"
FRONTEND_MODULE="$ROOT/frontend/src/modules/$MODULE_CLASS"

if [[ -d "$BACKEND_MODULE" ]]; then
  echo "error: backend module already exists at $BACKEND_MODULE" >&2
  exit 1
fi

echo "==> Creating backend module skeleton: Modules/$MODULE_CLASS"
mkdir -p "$BACKEND_MODULE"/{app/Providers,routes,database/migrations,tests/Feature}
cat > "$BACKEND_MODULE/module.json" <<EOF
{
  "name": "$MODULE_CLASS",
  "alias": "$(echo "$PRODUCT_ID" | tr '-' '_')",
  "description": "$PRODUCT_NAME module (downstream)",
  "keywords": ["$PRODUCT_ID"],
  "priority": 0,
  "providers": [
    "Modules\\\\$MODULE_CLASS\\\\Providers\\\\${MODULE_CLASS}ServiceProvider"
  ],
  "files": []
}
EOF

cat > "$BACKEND_MODULE/app/Providers/${MODULE_CLASS}ServiceProvider.php" <<EOF
<?php

declare(strict_types=1);

namespace Modules\\$MODULE_CLASS\\Providers;

use Illuminate\\Support\\ServiceProvider;

class ${MODULE_CLASS}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadRoutesFrom(module_path('$MODULE_CLASS', 'routes/api.php'));
    }
}
EOF

cat > "$BACKEND_MODULE/routes/api.php" <<EOF
<?php

declare(strict_types=1);

use Illuminate\\Support\\Facades\\Route;

Route::prefix('api/v1/manage/$PRODUCT_ID')->middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/health', static fn () => response()->json([
        'success' => true,
        'product' => '$PRODUCT_ID',
        'message' => '$PRODUCT_NAME module is mounted',
    ]));
});
EOF

echo "==> Creating frontend module skeleton: src/modules/$MODULE_CLASS"
mkdir -p "$FRONTEND_MODULE"/views
cat > "$FRONTEND_MODULE/README.md" <<EOF
# $PRODUCT_NAME ($PRODUCT_ID)

Downstream module — register routes in \`frontend/src/engine/router/console.ts\`.
EOF

echo "==> Next manual steps"
echo "1. Enable module in backend/modules_statuses.json: \"$MODULE_CLASS\": true"
echo "2. Add scan path in backend/config/modules.php: base_path('Modules/$MODULE_CLASS/*')"
echo "3. Register Vue routes + menu entries in console router / Menu Editor"
echo "4. Set APP_NAME / DB_DATABASE in .env for this deployment"
echo "5. (Optional) Activate license via JA-CP for commercial tiers"
echo "Done."
