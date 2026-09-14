<?php
/**
 * Lightweight wishlist.
 *
 * Guests: product IDs in an HttpOnly cookie. Logged-in customers: user meta.
 * The guest list is merged into the account on login.
 *
 * Front end: [data-wishlist] buttons (see theme main.js), [qv_wishlist]
 * shortcode on the Wishlist page, My Account menu link.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

const QVC_WISHLIST_COOKIE = 'qv_wishlist';
const QVC_WISHLIST_META   = '_qv_wishlist';
const QVC_WISHLIST_MAX    = 100;

function qvc_wishlist_sanitize( $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	return array_slice( $ids, 0, QVC_WISHLIST_MAX );
}

function qvc_wishlist_cookie_ids() {
	$raw = isset( $_COOKIE[ QVC_WISHLIST_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ QVC_WISHLIST_COOKIE ] ) ) : '';
	return qvc_wishlist_sanitize( explode( ',', $raw ) );
}

function qvc_wishlist_set_cookie( array $ids ) {
	$value = implode( ',', $ids );
	if ( ! headers_sent() ) {
		setcookie(
			QVC_WISHLIST_COOKIE,
			$value,
			array(
				'expires'  => $ids ? time() + 60 * DAY_IN_SECONDS : time() - HOUR_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}
	$_COOKIE[ QVC_WISHLIST_COOKIE ] = $value;
}

function qvc_wishlist_get() {
	if ( is_user_logged_in() ) {
		return qvc_wishlist_sanitize( get_user_meta( get_current_user_id(), QVC_WISHLIST_META, true ) );
	}
	return qvc_wishlist_cookie_ids();
}

function qvc_wishlist_save( array $ids ) {
	$ids = qvc_wishlist_sanitize( $ids );
	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), QVC_WISHLIST_META, $ids );
	} else {
		qvc_wishlist_set_cookie( $ids );
	}
	return $ids;
}

function qvc_wishlist_has( $product_id ) {
	return in_array( (int) $product_id, qvc_wishlist_get(), true );
}

/**
 * Toggle button markup (styled by the theme).
 */
function qv_wishlist_button( $product_id, $args = array() ) {
	$args  = wp_parse_args( $args, array( 'class' => '', 'show_label' => false ) );
	$on    = qvc_wishlist_has( $product_id );
	$icon  = function_exists( 'qv_icon' ) ? qv_icon( 'heart', array( 'size' => 18 ) ) : '';
	$label = __( 'Save to wishlist', 'quantivista' );

	return sprintf(
		'<button type="button" class="wishlist-btn %1$s%2$s" data-wishlist="%3$d" aria-pressed="%4$s">%5$s<span class="%6$s">%7$s</span></button>',
		esc_attr( $args['class'] ),
		$on ? ' is-active' : '',
		(int) $product_id,
		$on ? 'true' : 'false',
		$icon,
		$args['show_label'] ? 'wishlist-btn__label' : 'screen-reader-text',
		esc_html( $label )
	);
}

/**
 * AJAX toggle (guests and customers).
 */
add_action( 'wp_ajax_qv_wishlist_toggle', 'qvc_wishlist_ajax_toggle' );
add_action( 'wp_ajax_nopriv_qv_wishlist_toggle', 'qvc_wishlist_ajax_toggle' );
function qvc_wishlist_ajax_toggle() {
	check_ajax_referer( 'qv_nonce', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
		wp_send_json_error( array( 'message' => 'invalid_product' ), 400 );
	}

	$ids = qvc_wishlist_get();
	if ( in_array( $product_id, $ids, true ) ) {
		$ids = array_diff( $ids, array( $product_id ) );
		$on  = false;
	} else {
		array_unshift( $ids, $product_id );
		$on = true;
	}
	$ids = qvc_wishlist_save( $ids );

	wp_send_json_success(
		array(
			'in_wishlist' => $on,
			'count'       => count( $ids ),
		)
	);
}

/**
 * Merge the guest list into the account on login.
 */
add_action(
	'wp_login',
	function ( $login, $user ) {
		$guest = qvc_wishlist_cookie_ids();
		if ( ! $guest ) {
			return;
		}
		$saved = qvc_wishlist_sanitize( get_user_meta( $user->ID, QVC_WISHLIST_META, true ) );
		update_user_meta( $user->ID, QVC_WISHLIST_META, qvc_wishlist_sanitize( array_merge( $saved, $guest ) ) );
		qvc_wishlist_set_cookie( array() );
	},
	10,
	2
);

/**
 * [qv_wishlist] — product grid of saved items.
 */
add_shortcode(
	'qv_wishlist',
	function () {
		$ids = qvc_wishlist_get();
		ob_start();

		if ( ! $ids ) {
			?>
			<div class="empty-state wishlist-empty">
				<?php echo function_exists( 'qv_icon' ) ? qv_icon( 'heart', array( 'size' => 40 ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<h2><?php esc_html_e( 'Your wishlist is empty', 'quantivista' ); ?></h2>
				<p><?php esc_html_e( 'Tap the heart on any product to save it for later.', 'quantivista' ); ?></p>
				<a class="btn btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse products', 'quantivista' ); ?></a>
			</div>
			<?php
			return ob_get_clean();
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => QVC_WISHLIST_MAX,
				'no_found_rows'  => true,
			)
		);

		$GLOBALS['qv_wishlist_context'] = true;
		echo '<p class="wishlist-summary">' . sprintf(
			/* translators: %s: item count */
			esc_html( _n( '%s saved product', '%s saved products', count( $ids ), 'quantivista' ) ),
			'<strong data-wishlist-count>' . (int) count( $ids ) . '</strong>'
		) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<ul class="products columns-3 wishlist-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		echo '</ul>';
		wp_reset_postdata();
		unset( $GLOBALS['qv_wishlist_context'] );

		return ob_get_clean();
	}
);

/**
 * My Account menu → Wishlist page.
 */
add_filter(
	'woocommerce_account_menu_items',
	function ( $items ) {
		if ( ! get_option( 'qv_wishlist_page_id' ) ) {
			return $items;
		}
		return qvc_array_insert_after( $items, 'licenses', array( 'wishlist' => __( 'Wishlist', 'quantivista' ) ) );
	},
	30
);

add_filter(
	'woocommerce_get_endpoint_url',
	function ( $url, $endpoint ) {
		if ( 'wishlist' === $endpoint && get_option( 'qv_wishlist_page_id' ) ) {
			return get_permalink( (int) get_option( 'qv_wishlist_page_id' ) );
		}
		return $url;
	},
	10,
	2
);
