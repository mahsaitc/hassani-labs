<?php
/**
 * Front-end assets.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version assets by file modification time so edits bust caches without
 * bumping QV_VERSION.
 */
function qv_asset_version( $relative ) {
	$path = QV_DIR . '/' . ltrim( $relative, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : QV_VERSION;
}

add_action( 'wp_enqueue_scripts', 'qv_enqueue_assets' );
function qv_enqueue_assets() {
	// Right-to-left languages (Persian) get the generated *-rtl.css instead.
	$rtl = is_rtl() ? '-rtl' : '';
	wp_enqueue_style( 'quantivista', QV_URI . "/assets/css/main{$rtl}.css", array(), qv_asset_version( "assets/css/main{$rtl}.css" ) );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'quantivista-shop', QV_URI . "/assets/css/woocommerce{$rtl}.css", array( 'quantivista' ), qv_asset_version( "assets/css/woocommerce{$rtl}.css" ) );
	}

	wp_enqueue_script(
		'quantivista',
		QV_URI . '/assets/js/main.js',
		array(),
		qv_asset_version( 'assets/js/main.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'quantivista',
		'qvData',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'qv_nonce' ),
			'i18n'    => array(
				'added'   => __( 'Saved to wishlist', 'quantivista' ),
				'removed' => __( 'Removed from wishlist', 'quantivista' ),
				'copied'  => __( 'Link copied', 'quantivista' ),
			),
		)
	);

	if ( is_singular( 'post' ) && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

/**
 * Preload the variable font used above the fold.
 */
add_action(
	'wp_head',
	function () {
		// Flag JS early so scroll-reveal styles apply without a flash; if main.js
		// never runs, reveal everything after 2.5s.
		echo "<script>document.documentElement.classList.add('js');setTimeout(function(){if(!window.qvReady){document.documentElement.classList.add('no-reveal');}},2500);</script>\n";
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( QV_URI . ( is_rtl() ? '/assets/fonts/vazirmatn-variable.woff2' : '/assets/fonts/inter-var-latin.woff2' ) )
		);
		echo '<meta name="theme-color" content="#070B24">' . "\n";
		if ( ! has_site_icon() ) { // A Site Icon set in the Customizer wins.
			printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( QV_URI . '/assets/img/favicon-32.png' ) );
			printf( '<link rel="icon" href="%s" sizes="192x192">' . "\n", esc_url( QV_URI . '/assets/img/logo-mark-192.png' ) );
			printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( QV_URI . '/assets/img/apple-touch-icon.png' ) );
		}
	},
	1
);
