<?php
/**
 * Quantivista theme bootstrap.
 *
 * Presentation lives here; site functionality (CPTs, roles, license keys,
 * wishlist, newsletter, email verification) lives in the quantivista-core
 * mu-plugin so it survives a theme switch.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

define( 'QV_VERSION', '1.0.0' );
define( 'QV_DIR', get_template_directory() );
define( 'QV_URI', get_template_directory_uri() );

$qv_includes = array(
	'setup',
	'enqueue',
	'performance',
	'icons',
	'template-tags',
	'nav-walker',
	'customizer',
);

if ( class_exists( 'WooCommerce' ) ) {
	$qv_includes[] = 'woocommerce';
}

foreach ( $qv_includes as $qv_file ) {
	require_once QV_DIR . '/inc/' . $qv_file . '.php';
}
unset( $qv_includes, $qv_file );
