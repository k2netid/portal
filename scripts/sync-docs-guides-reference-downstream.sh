#!/usr/bin/env bash
# Mirror selected docs from ja-core_engine → downstream portals.
# SoT remains ja-core_engine; downstream copies are for local navigation / agents.
#
# Syncs:
#   docs/guides/
#   docs/reference/
#   docs/architecture/          (full copy — re-sync often to avoid drift)
#   DOCUMENTATION.md, WORKFLOW.md, COMPLETENESS.md (policy mirrors)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEV_ROOT="$(cd "${ROOT}/.." && pwd)"

TARGETS=(
  "${DEV_ROOT}/k2net-portal"
  "${DEV_ROOT}/smkn6-portal"
  "${DEV_ROOT}/ja-cms"
)

inject_sot_banner() {
  local file="$1"
  local sot_path="$2"
  [[ -f "$file" ]] || return 0
  if grep -q 'Source of Truth:' "$file" 2>/dev/null; then
    return 0
  fi
  local tmp
  tmp="$(mktemp)"
  {
    echo "> **Source of Truth:** \`${sot_path}\` — mirrored copy. Do not edit here."
    echo "> Re-sync: \`bash ${ROOT}/scripts/sync-docs-guides-reference-downstream.sh\`"
    echo
    cat "$file"
  } >"$tmp"
  mv "$tmp" "$file"
}

write_sot_marker() {
  local dest="$1"
  local canonical="$2"
  cat >"$dest" <<EOF
# Upstream SoT

Canonical: \`${canonical}\`
Sync: \`bash ${ROOT}/scripts/sync-docs-guides-reference-downstream.sh\`
EOF
}

sync_dir() {
  local src="$1"
  local dest="$2"
  local canonical="$3"
  local readme_banner_path="$4"
  mkdir -p "$dest"
  rsync -a --delete \
    --exclude '.upstream-sot.md' \
    "${src}/" "${dest}/"
  inject_sot_banner "${dest}/${readme_banner_path}" "$canonical"
  write_sot_marker "${dest}/.upstream-sot.md" "$canonical"
}

sync_file() {
  local src="$1"
  local dest="$2"
  local canonical="$3"
  mkdir -p "$(dirname "$dest")"
  cp -f "$src" "$dest"
  inject_sot_banner "$dest" "$canonical"
}

sync_one() {
  local dest="$1"
  local name
  name="$(basename "$dest")"
  if [[ ! -d "$dest/docs" ]]; then
    echo "SKIP ${name}: no docs/"
    return 0
  fi

  sync_dir "${ROOT}/docs/guides" "${dest}/docs/guides" "ja-core_engine/docs/guides/" "README.md"
  sync_dir "${ROOT}/docs/reference" "${dest}/docs/reference" "ja-core_engine/docs/reference/" "README.md"
  sync_dir "${ROOT}/docs/architecture" "${dest}/docs/architecture" "ja-core_engine/docs/architecture/" "01-overview-and-tier-design.md"

  sync_file "${ROOT}/docs/DOCUMENTATION.md" "${dest}/docs/DOCUMENTATION.md" "ja-core_engine/docs/DOCUMENTATION.md"
  sync_file "${ROOT}/docs/WORKFLOW.md" "${dest}/docs/WORKFLOW.md" "ja-core_engine/docs/WORKFLOW.md"
  sync_file "${ROOT}/docs/COMPLETENESS.md" "${dest}/docs/COMPLETENESS.md" "ja-core_engine/docs/COMPLETENESS.md"

  echo "OK  synced guides+reference+architecture+policy → ${name}"
}

for t in "${TARGETS[@]}"; do
  sync_one "$t"
done

echo "Done. Edit only in ja-core_engine, then re-run this script."
