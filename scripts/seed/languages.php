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
PLL()->options->save();

// Polylang reads its languages once per request: create the Persian content
// in a new process so it sees both languages.
WP_CLI::runcommand( 'eval-file ' . escapeshellarg( __DIR__ . '/translations.php' ), array( 'launch' => true, 'exit_error' => true ) );
WP_CLI::runcommand(
	'eval ' . escapeshellarg( 'foreach ( (array) get_option( "qvs_fa_pairs" ) as $en => $fa ) { pll_save_post_translations( array( "en" => $en, "fa" => $fa ) ); } delete_option( "qvs_fa_pairs" );' ),
	array( 'launch' => true, 'exit_error' => true )
);
