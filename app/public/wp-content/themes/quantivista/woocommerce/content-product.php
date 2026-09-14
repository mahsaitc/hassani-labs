<?php
/**
 * The template for displaying product content within loops.
 *
 * Override: delegates to the theme's reusable product card.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Quantivista\WooCommerce
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

get_template_part( 'template-parts/product/card' );
