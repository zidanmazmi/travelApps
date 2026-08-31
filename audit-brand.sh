#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGETS=()
for dir in app public resources; do
  [[ -e "$ROOT/$dir" ]] && TARGETS+=("$ROOT/$dir")
done

PATTERN='am[[:space:]_-]*wisata|amwisata|haqeem|am-wisata-bogor-logo|am-wisata-bogor-favicon'

if grep -RniIE --exclude-dir=vendor --exclude-dir=writable --exclude='audit-brand.sh' "$PATTERN" "${TARGETS[@]}" 2>/dev/null; then
  echo
  echo "FAIL: legacy brand reference masih ditemukan."
  exit 1
fi

echo "PASS: source app/public/resources bersih dari legacy brand reference."
