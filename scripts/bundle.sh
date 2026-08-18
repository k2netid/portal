#!/bin/bash

# Jejakawan Bundler (Production Release)
# This script prepares a clean production package

set -e

APP_NAME="jejakawan"
VERSION=$(jq -r .version frontend/package.json)
TIMESTAMP=$(date +%Y%m%d%H%M)
RELEASE_NAME="${APP_NAME}-v${VERSION}-${TIMESTAMP}"
RELEASE_DIR="releases/${RELEASE_NAME}"

echo "📦 Starting bundling process for ${RELEASE_NAME}..."

# 1. Create Release Directory
mkdir -p "$RELEASE_DIR"

# 2. Build Frontend
echo "🎨 Building frontend assets..."
cd frontend
npm install
npm run build
cd ..

# 3. Copy Backend to Release Directory
echo "🚚 Copying backend files..."
cp -r backend/. "$RELEASE_DIR/"

# 4. Production Optimization
echo "🗺️ Optimizing autoloader for production..."
if command -v jq &> /dev/null; then
    # Convert sensitive folders to Classmap for production performance
    CLASSMAP_JSON="[\"app/\", \"Modules/\", \"database/seeders/\", \"database/migrations/\"]"
    jq ".autoload.classmap = $CLASSMAP_JSON" "$RELEASE_DIR/composer.json" > "$RELEASE_DIR/composer.json.tmp" && mv "$RELEASE_DIR/composer.json.tmp" "$RELEASE_DIR/composer.json"
fi

# 5. Clean up unnecessary files from release
echo "🧹 Cleaning up development files..."
rm -rf "$RELEASE_DIR/node_modules"
rm -rf "$RELEASE_DIR/vendor"
rm -rf "$RELEASE_DIR/storage/framework/cache/data/*"
rm -rf "$RELEASE_DIR/storage/framework/sessions/*"
rm -rf "$RELEASE_DIR/storage/framework/views/*.php"
rm -rf "$RELEASE_DIR/storage/logs/*.log"
rm -rf "$RELEASE_DIR/.env"
rm -rf "$RELEASE_DIR/.git"
rm -rf "$RELEASE_DIR/tests"
rm -rf "$RELEASE_DIR/docs"
rm -rf "$RELEASE_DIR/.github"
rm -rf "$RELEASE_DIR/.editorconfig"
rm -rf "$RELEASE_DIR/.gitattributes"
rm -rf "$RELEASE_DIR/.gitignore"
rm -rf "$RELEASE_DIR/.php-version"
rm -rf "$RELEASE_DIR/compose.yaml"
rm -rf "$RELEASE_DIR/phpunit.xml"
rm -rf "$RELEASE_DIR/phpstan.neon"
rm -rf "$RELEASE_DIR/package.json"
rm -rf "$RELEASE_DIR/vite.config.js"
rm -rf "$RELEASE_DIR/composer"
rm -rf "$RELEASE_DIR/composer-audit.json"

# 5. Copy Build Assets to Backend Public (if not already linked)
# Adjust this based on your specific Vite/Laravel integration
# Usually, build results are already in backend/public/build or similar

# 6. Copy Installer Scripts & Templates
echo "📜 Adding installer scripts and templates..."
rm -rf "$RELEASE_DIR/scripts"
mkdir -p "$RELEASE_DIR/scripts"
cp scripts/install.sh "$RELEASE_DIR/scripts/"
if [ -d "scripts/templates" ]; then
    cp -r scripts/templates "$RELEASE_DIR/scripts/"
fi
cp .env.example "$RELEASE_DIR/.env.example"

# 7. Finalize Package
echo "📦 Creating ZIP archive..."
cd releases
zip -r "${RELEASE_NAME}.zip" "${RELEASE_NAME}"
cd ..

echo "✅ Release package created: releases/${RELEASE_NAME}.zip"
echo "🚀 You can now share this ZIP file with your clients."
