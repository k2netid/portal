#!/usr/bin/env bash
# Prefer system Node 22 (NodeSource) over Cursor embedded Node 20.
# Usage:
#   bash scripts/use-node22.sh npm run build
#   source scripts/use-node22.sh && npm run dev   # (export only — use .envrc instead)
export PATH="/usr/bin:${PATH}"
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
  exec "$@"
fi
