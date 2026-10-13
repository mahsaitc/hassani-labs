#!/usr/bin/env bash
# Regenerate the right-to-left stylesheets used by the Persian site.
# Run after editing main.css or woocommerce.css. Needs Node.js (npx).
set -euo pipefail
CSS="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/app/public/wp-content/themes/quantivista/assets/css"
for f in main woocommerce; do
	npx --yes rtlcss@4.3.0 "$CSS/$f.css" "$CSS/$f-rtl.css"
done
