<?php
/**
 * Languages (Polylang): English at the site root, Persian under /fa/.
 * Sets up the two languages, then runs translations.php in a fresh WP-CLI
 * process (Polylang only picks up newly added languages on the next load)
 * to create the Persian content and menus. Run after pages, portfolio, blog.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_save_post_translations' ) ) {
	WP_CLI::warning( 'Polylang is not active: skipping languages. Install it with: wp plugin install polylang --activate' );
	return;
}

/* ------------------------------------------------------------------------
 * Languages and settings
 * --------------------------------------------------------------------- */
$qv_model = PLL()->model;
$qv_have  = wp_list_pluck( $qv_model->get_languages_list(), 'slug' );
foreach (
	array(
		'en' => array( 'name' => 'English', 'slug' => 'en', 'locale' => 'en_US', 'rtl' => false, 'term_group' => 0, 'flag' => 'us' ),
		'fa' => array( 'name' => 'فارسی', 'slug' => 'fa', 'locale' => 'fa_IR', 'rtl' => true, 'term_group' => 1, 'flag' => 'ir' ),
	) as $qv_slug => $qv_lang
) {
	if ( in_array( $qv_slug, $qv_have, true ) ) {
		continue;
	}
	$qv_result = $qv_model->languages->add( $qv_lang );
	if ( is_wp_error( $qv_result ) ) {
		WP_CLI::error( "Language {$qv_slug}: " . $qv_result->get_error_message() );
	}
}
$qv_model->clean_languages_cache();

$qv_settings = array(
	'default_lang'  => 'en',
	'force_lang'    => 1, // Language from the directory: /fa/…
	'hide_default'  => true, // English has no /en/ prefix.
	'rewrite'       => true, // /fa/ rather than /language/fa/.
	'redirect_lang' => true, // Persian home is /fa/, not /fa/home-fa/.
	'browser'       => false, // Do not redirect visitors by browser language.
	'post_types'    => array( 'qv_project', 'qv_milestone', 'qv_slide', 'qv_testimonial' ),
	'taxonomies'    => array(), // Technologies are shared by both languages.
);
foreach ( $qv_settings as $qv_key => $qv_value ) {
	$qv_error = PLL()->options->set( $qv_key, $qv_value );
	if ( $qv_error->has_errors() ) {
		WP_CLI::warning( "Polylang option {$qv_key}: " . $qv_error->get_error_message() );
	}
}
// Keep the English menus in place even before the Persian ones exist.
$qv_nav_menus = PLL()->options->get( 'nav_menus' );
foreach ( (array) get_theme_mod( 'nav_menu_locations', array() ) as $qv_location => $qv_menu_id ) {
	if ( empty( $qv_nav_menus[ get_option( 'stylesheet' ) ][ $qv_location ]['en'] ) ) {
		$qv_nav_menus[ get_option( 'stylesheet' ) ][ $qv_location ]['en'] = (int) $qv_menu_id;
	}
}
PLL()->options->set( 'nav_menus', $qv_nav_menus );
PLL()->options->save();

// Polylang reads its languages once per request: create the Persian content
// in a new process so it sees both languages, then link and flush in another.
// If a process cannot be started (seen on Windows), print the commands to run.
$GLOBALS['qvs_languages_done'] = true;
foreach ( array( 'translations.php', 'finish.php' ) as $qv_step ) {
	$qv_run = WP_CLI::runcommand(
		'eval-file ' . escapeshellarg( __DIR__ . '/' . $qv_step ),
		array( 'launch' => true, 'exit_error' => false, 'return' => 'all' )
	);
	if ( $qv_run->stdout ) {
		WP_CLI::log( rtrim( $qv_run->stdout ) );
	}
	if ( 0 !== $qv_run->return_code ) {
		WP_CLI::warning( "The Persian setup step {$qv_step} did not run here:\n" . trim( $qv_run->stderr ) );
		WP_CLI::log( 'Run these two commands yourself, one after the other:' );
		WP_CLI::log( '  wp eval-file "' . __DIR__ . DIRECTORY_SEPARATOR . 'translations.php"' );
		WP_CLI::log( '  wp eval-file "' . __DIR__ . DIRECTORY_SEPARATOR . 'finish.php"' );
		break;
	}
}
