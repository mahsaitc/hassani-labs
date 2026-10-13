<?php
/**
 * Product catalogue only, for a site that is already set up: product
 * categories (data/catalog.php), the Products pages in both languages and
 * the "Products" mega menu in the English and Persian main menus. Other
 * pages, products and menu items are left as they are.
 *
 * Usage: wp eval-file scripts/seed.php catalog
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'product_cat' ) ) {
	WP_CLI::warning( 'WooCommerce is not active: skipping the catalogue.' );
	return;
}

$qv_cat_ids = qvs_catalog_terms();

// The clinic software plans belong to Medical & healthcare and Web applications.
foreach ( array( 'QV-CLINIC-BASIC', 'QV-CLINIC-PRO', 'QV-CLINIC-RENEW' ) as $qv_sku ) {
	$qv_id = wc_get_product_id_by_sku( $qv_sku );
	if ( $qv_id ) {
		wp_set_object_terms( $qv_id, qvs_catalog_ids( $qv_cat_ids, array( 'medical-healthcare', 'web-apps' ) ), 'product_cat' );
	}
}
// Sample products lost their old categories: file them under "Web applications" until edited.
foreach ( (array) get_option( 'qv_seed_product_ids', array() ) as $qv_id ) {
	if ( get_post( $qv_id ) && ! wp_get_object_terms( $qv_id, 'product_cat', array( 'fields' => 'ids', 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) ) ) {
		wp_set_object_terms( $qv_id, qvs_catalog_ids( $qv_cat_ids, array( 'web-apps' ) ), 'product_cat' );
	}
}
if ( function_exists( 'wc_recount_all_terms' ) ) {
	wc_recount_all_terms();
}

list( $qv_products_en, $qv_products_fa ) = qvs_catalog_pages();

$qv_locations = get_theme_mod( 'nav_menu_locations', array() );
$qv_menus     = array( 'en' => (int) ( $qv_locations['primary'] ?? 0 ) );
if ( function_exists( 'PLL' ) ) {
	$qv_pll_menus = PLL()->options->get( 'nav_menus' );
	foreach ( (array) ( $qv_pll_menus[ get_option( 'stylesheet' ) ]['primary'] ?? array() ) as $qv_lang => $qv_menu_id ) {
		if ( $qv_menu_id ) {
			$qv_menus[ $qv_lang ] = (int) $qv_menu_id;
		}
	}
}
foreach ( $qv_menus as $qv_lang => $qv_menu_id ) {
	if ( $qv_menu_id && wp_get_nav_menu_object( $qv_menu_id ) ) {
		qvs_catalog_replace_menu( $qv_menu_id, $qv_lang, 'fa' === $qv_lang ? $qv_products_fa : $qv_products_en );
	}
}

WP_CLI::log( sprintf( '    catalogue: %d categories, Products pages and menus (%s)', count( $qv_cat_ids ), implode( ', ', array_keys( $qv_menus ) ) ) );
