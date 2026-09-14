<?php
/**
 * Front-end performance tweaks (manual alternative to a premium cache plugin).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

// Emoji detection script + styles are dead weight on a modern site.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );

// Head clean-up.
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

/**
 * Only load Contact Form 7 assets where a form is actually rendered.
 */
add_filter( 'wpcf7_load_js', 'qv_cf7_needed' );
add_filter( 'wpcf7_load_css', 'qv_cf7_needed' );
function qv_cf7_needed() {
	if ( is_admin() ) {
		return true;
	}
	return is_page_template( 'page-templates/contact.php' ) || ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'contact-form-7' ) );
}

/**
 * Drop WooCommerce block styles on pages that never render WC blocks.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return;
		}
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
			wp_dequeue_style( 'wc-blocks-style' );
			wp_dequeue_style( 'woocommerce-inline' );
		}
	},
	100
);

/**
 * Hero/LCP image: the first slide poster gets high fetch priority, not lazy.
 */
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr, $attachment, $size ) {
		if ( 'qv-wide' === $size && ! empty( $attr['data-qv-lcp'] ) ) {
			$attr['loading']       = 'eager';
			$attr['fetchpriority'] = 'high';
			unset( $attr['data-qv-lcp'] );
		}
		return $attr;
	},
	10,
	3
);

/**
 * Resource hints: allow the browser to warm up the connection to Gravatar
 * (author avatars on blog pages).
 */
add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation && ( is_singular( 'post' ) || is_home() ) ) {
			$urls[] = array(
				'href'        => 'https://secure.gravatar.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $urls;
	},
	10,
	2
);
