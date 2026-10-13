#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Quantivista — environment provisioning (idempotent).
#
# Installs/activates plugins, applies core + WooCommerce settings and seeds
# placeholder content. Safe to re-run.
#
# Usage:
#   WP="wp" ./scripts/provision.sh            # any WP-CLI binary
#   WP="qwp" ./scripts/provision.sh           # Local by Flywheel wrapper
#
# On Local (Windows) use "Open site shell" from the Local app, or a wrapper
# that points php.exe + wp-cli.phar at app/public.
# ---------------------------------------------------------------------------
set -euo pipefail

WP="${WP:-wp}"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "==> Core"
$WP core is-installed || { echo "WordPress is not installed at the configured path." >&2; exit 1; }
$WP config set DISALLOW_FILE_EDIT true --raw
$WP rewrite structure '/%postname%/' --hard

echo "==> Plugins"
PLUGINS=(
	woocommerce
	advanced-custom-fields
	polylang
	wordfence
)
for plugin in "${PLUGINS[@]}"; do
	if $WP plugin is-installed "$plugin"; then
		$WP plugin activate "$plugin" >/dev/null
	else
		$WP plugin install "$plugin" --activate
	fi
done

# Replaced by the theme (inc/seo.php) and the built-in contact form: fewer plugins to keep updated.
for plugin in wordpress-seo contact-form-7; do
	$WP plugin is-active "$plugin" && $WP plugin deactivate "$plugin" || true
done

# Default themes are not needed; keep one as an emergency fallback.
for theme in twentytwentythree twentytwentyfour; do
	$WP theme is-installed "$theme" && $WP theme delete "$theme" || true
done

echo "==> Theme"
$WP theme activate quantivista

echo "==> Seed content & settings"
$WP eval-file "$ROOT/scripts/seed.php"

$WP rewrite flush --hard
$WP cache flush || true

echo "Done. Visit $($WP option get home)"
