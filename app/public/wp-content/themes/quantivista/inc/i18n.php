<?php
/**
 * Two languages through Polylang: English at the site root, Persian under
 * /fa/. Everything here falls back to plain WordPress when Polylang is not
 * active.
 *
 * - Theme strings: languages/fa_IR.mo (built from fa_IR.po).
 * - Customizer texts: their defaults are translated by the .mo file; texts
 *   changed in the Customizer appear in Languages > Translations.
 * - Links to pages (About, Contact…) point to the page in the current language.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customizer keys whose values are visitor-facing text.
 */
const QV_TRANSLATABLE_MODS = array(
	'header_cta_label',
	'mega_promo_title',
	'mega_promo_text',
	'stat_1_label',
	'stat_2_label',
	'stat_3_label',
	'stat_4_label',
	'cta_title',
	'cta_text',
	'cta_primary_label',
	'footer_about',
	'contact_address',
	'contact_hours',
	'founder_name',
	'founder_role',
	'founder_intro',
);

/**
 * Translate a Customizer text: an untouched default through the theme's
 * .mo file, an edited value through Polylang's string translations.
 */
function qv_translate_mod( $key, $value, $default ) {
	if ( ! is_string( $value ) || '' === $value || ! in_array( $key, QV_TRANSLATABLE_MODS, true ) ) {
		return $value;
	}
	if ( $value === $default ) {
		return translate( $value, 'quantivista' ); // phpcs:ignore WordPress.WP.I18n -- defaults are listed in languages/fa_IR.po.
	}
	return function_exists( 'pll__' ) ? pll__( $value ) : $value;
}

add_action(
	'admin_init',
	function () {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}
		$defaults = qv_mod_defaults();
		foreach ( QV_TRANSLATABLE_MODS as $key ) {
			$value = get_theme_mod( 'qv_' . $key, $defaults[ $key ] ?? '' );
			if ( is_string( $value ) && '' !== $value && $value !== ( $defaults[ $key ] ?? '' ) ) {
				pll_register_string( $key, $value, 'Hassani Labs', strlen( $value ) > 80 );
			}
		}
	}
);

/**
 * Home URL in the current language, plus an optional path.
 */
function qv_home_url( $path = '/' ) {
	if ( function_exists( 'pll_home_url' ) ) {
		return trailingslashit( pll_home_url() ) . ltrim( $path, '/' );
	}
	return home_url( $path );
}

/**
 * A page (by its English path) in the current language, or null.
 */
function qv_page_by_path( $path ) {
	$page = get_page_by_path( trim( $path, '/' ) );
	if ( $page && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $page->ID );
		if ( $translated ) {
			$page = get_post( $translated );
		}
	}
	return $page;
}

/**
 * Resolve a site-relative URL like "/contact/" to the current language:
 * the translated page when it is a page, otherwise the language's home + path.
 */
function qv_localize_url( $url ) {
	if ( ! str_starts_with( (string) $url, '/' ) ) {
		return $url;
	}
	$parts = explode( '#', $url, 2 );
	$page  = '/' === $parts[0] ? null : qv_page_by_path( $parts[0] );
	$base  = $page ? get_permalink( $page ) : qv_home_url( $parts[0] );
	return $base . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );
}

/**
 * Language switcher: "EN · فا", linking to the translation of the current
 * page when there is one.
 */
function qv_language_switcher( $class = '' ) {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}
	$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
	if ( count( (array) $languages ) < 2 ) {
		return;
	}
	$labels = array( 'en' => 'EN', 'fa' => 'فا' );
	echo '<ul class="lang-switch ' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Language', 'quantivista' ) . '">';
	foreach ( $languages as $lang ) {
		printf(
			'<li class="%1$s"><a href="%2$s" hreflang="%3$s" lang="%3$s"%4$s>%5$s</a></li>',
			$lang['current_lang'] ? 'is-current' : '',
			esc_url( $lang['url'] ),
			esc_attr( $lang['slug'] ),
			$lang['current_lang'] ? ' aria-current="true"' : '',
			esc_html( $labels[ $lang['slug'] ] ?? strtoupper( $lang['slug'] ) )
		);
	}
	echo '</ul>';
}
