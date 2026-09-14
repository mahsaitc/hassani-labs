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
	wp_enqueue_style( 'quantivista', QV_URI . '/assets/css/main.css', array(), qv_asset_version( 'assets/css/main.css' ) );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'quantivista-shop', QV_URI . '/assets/css/woocommerce.css', array( 'quantivista' ), qv_asset_version( 'assets/css/woocommerce.css' ) );
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
			esc_url( QV_URI . '/assets/fonts/inter-var-latin.woff2' )
		);
		echo '<meta name="theme-color" content="#070B24">' . "\n";
	},
	1
);
