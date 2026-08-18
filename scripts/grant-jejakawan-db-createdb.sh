#!/usr/bin/env bash
# Hub control plane uses a single PostgreSQL database — CREATEDB is not required for tenant provisioning.
set -euo pipefail

echo "ja-control-plane hub is single-DB; tenant CREATEDB provisioning is not used." >&2
echo "Use: cd backend && php artisan migrate:fresh --seed" >&2
exit 1
