<?php
/**
 * Seed Hassani Labs settings and content (idempotent).
 *
 * Usage:
 *   wp eval-file scripts/seed.php                 # all sections
 *   wp eval-file scripts/seed.php shop pages      # selected sections
 *
 * Sections run in this order: settings, shop, pages, portfolio, blog, languages.
 * Every seeded object carries a `_qv_seed_key` meta so re-runs update
 * instead of duplicating.
 *
 * @package Quantivista\Scripts
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Run with WP-CLI: wp eval-file scripts/seed.php\n" );
}

$qv_all      = array( 'settings', 'shop', 'pages', 'portfolio', 'blog', 'languages' );
$qv_sections = ! empty( $args ) ? array_values( array_intersect( $qv_all, $args ) ) : $qv_all;
$qv_sections = array_values( array_filter( $qv_sections, fn( $s ) => file_exists( __DIR__ . "/seed/{$s}.php" ) ) );

require_once __DIR__ . '/lib/images.php';
require_once __DIR__ . '/seed/helpers.php';

foreach ( $qv_sections as $qv_section ) {
	WP_CLI::log( "==> Seeding: {$qv_section}" );
	$qv_started = microtime( true );
	require __DIR__ . "/seed/{$qv_section}.php";
	WP_CLI::log( sprintf( '    done in %.1fs', microtime( true ) - $qv_started ) );
}

// The languages step rebuilds the permalinks itself (finish.php, in its own process).
if ( empty( $GLOBALS['qvs_languages_done'] ) ) {
	flush_rewrite_rules( false );
}
WP_CLI::success( 'Seed complete: ' . implode( ', ', $qv_sections ) );
