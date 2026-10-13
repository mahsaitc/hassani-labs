<?php
/**
 * Last step of the Persian setup, in its own process: links every en/fa pair
 * once more (Polylang drops the links of the front page and the blog page
 * while translations.php runs) and rebuilds the permalinks so /fa/ works.
 *
 * Usage: wp eval-file scripts/seed/finish.php
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_save_post_translations' ) ) {
	WP_CLI::error( 'Polylang is not active. Run: wp plugin install polylang --activate' );
}

foreach ( (array) get_option( 'qvs_fa_pairs', array() ) as $qv_en => $qv_fa ) {
	if ( $qv_en && $qv_fa && get_post( $qv_en ) && get_post( $qv_fa ) ) {
		pll_save_post_translations( array( 'en' => (int) $qv_en, 'fa' => (int) $qv_fa ) );
	}
}
delete_option( 'qvs_fa_pairs' );
flush_rewrite_rules( false );

WP_CLI::log( '    languages: pages linked and permalinks rebuilt' );
