<?php
/**
 * Theme supports, menus, image sizes and widget areas.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'qv_setup' );
function qv_setup() {
	load_theme_textdomain( 'quantivista', QV_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 640,
			'single_image_width'    => 1100,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary'         => __( 'Primary Menu', 'quantivista' ),
			'footer_quick'    => __( 'Footer — Quick Links', 'quantivista' ),
			'footer_products' => __( 'Footer — Products', 'quantivista' ),
			'footer_policies' => __( 'Footer — Policies', 'quantivista' ),
		)
	);

	add_image_size( 'qv-card', 800, 500, true );
	add_image_size( 'qv-wide', 1600, 900, true );
	add_image_size( 'qv-square', 480, 480, true );

	$GLOBALS['content_width'] = 760;
}

add_action( 'widgets_init', 'qv_widgets_init' );
function qv_widgets_init() {
	$defaults = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$defaults,
			array(
				'name'        => __( 'Blog Sidebar', 'quantivista' ),
				'id'          => 'blog-sidebar',
				'description' => __( 'Shown next to the blog index and archives.', 'quantivista' ),
			)
		)
	);
	register_sidebar(
		array_merge(
			$defaults,
			array(
				'name'        => __( 'Shop Sidebar', 'quantivista' ),
				'id'          => 'shop-sidebar',
				'description' => __( 'Shown below the product filters on shop and category pages.', 'quantivista' ),
			)
		)
	);
	register_sidebar(
		array_merge(
			$defaults,
			array(
				'name'        => __( 'Footer — Extra', 'quantivista' ),
				'id'          => 'footer-extra',
				'description' => __( 'Optional row above the footer bottom bar.', 'quantivista' ),
			)
		)
	);
}

add_filter( 'excerpt_length', fn() => 22 );
add_filter( 'excerpt_more', fn() => '&hellip;' );

/**
 * Body classes used by the stylesheet.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_front_page() ) {
			$classes[] = 'is-home';
		}
		if ( is_active_sidebar( 'blog-sidebar' ) && ( is_home() || is_archive() ) && ! ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) ) {
			$classes[] = 'has-blog-sidebar';
		}
		return $classes;
	}
);
