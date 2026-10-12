<?php
/**
 * Homepage.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/trust' );
get_template_part( 'template-parts/home/work' );

if ( class_exists( 'WooCommerce' ) ) {
	get_template_part( 'template-parts/home/featured-products' );
}

get_template_part( 'template-parts/home/why' );
get_template_part( 'template-parts/home/blog' );
get_template_part( 'template-parts/home/testimonials' );
get_template_part( 'template-parts/home/cta' );

get_footer();
