<?php
/**
 * Store rules: account required for checkout, lean checkout for digital
 * carts, project brief for service orders, account menu labels.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Insert items into an associative array after a given key.
 */
function qvc_array_insert_after( array $items, $after_key, array $insert ) {
	$position = array_search( $after_key, array_keys( $items ), true );
	if ( false === $position ) {
		return $items + $insert;
	}
	++$position;
	return array_slice( $items, 0, $position, true ) + $insert + array_slice( $items, $position, null, true );
}

/**
 * "download" (software, license key) or "service" (delivered by the team).
 */
function qvc_product_delivery( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return 'download';
	}
	$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
	$delivery  = get_post_meta( $parent_id, 'qv_delivery', true );
	if ( $delivery ) {
		return $delivery;
	}
	return ( $product->is_virtual() && ! $product->is_downloadable() ) ? 'service' : 'download';
}

function qvc_cart_has_service() {
	if ( ! WC()->cart ) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( 'service' === qvc_product_delivery( $item['data'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Guests are sent to log in / register before checkout. Guest checkout is
 * also disabled in WooCommerce settings; this makes the flow explicit and
 * keeps email verification in the path.
 */
add_action(
	'template_redirect',
	function () {
		if ( ! is_checkout() || is_user_logged_in() ) {
			return;
		}
		if ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-pay' ) ) {
			return;
		}
		wp_safe_redirect(
			add_query_arg(
				array(
					'qv_notice'   => 'login_required',
					'redirect_to' => rawurlencode( wc_get_checkout_url() ),
				),
				wc_get_page_permalink( 'myaccount' )
			)
		);
		exit;
	},
	20
);

/**
 * Carry ?redirect_to through the WooCommerce login form.
 */
add_action(
	'woocommerce_login_form_end',
	function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['redirect_to'] ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$target = wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ), '' );
		if ( $target ) {
			printf( '<input type="hidden" name="redirect" value="%s">', esc_url( $target ) );
		}
	}
);

/**
 * Customers with a filled cart continue to checkout after logging in.
 */
add_filter(
	'woocommerce_login_redirect',
	function ( $redirect ) {
		$account = untrailingslashit( wc_get_page_permalink( 'myaccount' ) );
		$target  = untrailingslashit( (string) strtok( (string) $redirect, '?' ) );
		if ( $target === $account && WC()->cart && ! WC()->cart->is_empty() ) {
			return wc_get_checkout_url();
		}
		return $redirect;
	},
	20
);

/**
 * Checkout fields: nothing ships, so skip street address for digital-only
 * carts; ask for a project brief when a service package is in the cart.
 */
add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		if ( ! WC()->cart ) {
			return $fields;
		}

		if ( ! WC()->cart->needs_shipping() ) {
			foreach ( array( 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_state' ) as $key ) {
				unset( $fields['billing'][ $key ] );
			}
			if ( isset( $fields['billing']['billing_phone'] ) ) {
				$fields['billing']['billing_phone']['required'] = false;
			}
		}

		if ( qvc_cart_has_service() && isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['label']       = __( 'Project brief', 'quantivista' );
			$fields['order']['order_comments']['placeholder'] = __( 'Goals, target launch date, reference websites, existing branding or content…', 'quantivista' );
		}

		return $fields;
	}
);

add_filter( 'woocommerce_order_button_text', fn() => __( 'Place secure order', 'quantivista' ) );

/**
 * Personalised account menu labels.
 */
add_filter(
	'woocommerce_account_menu_items',
	function ( $items ) {
		if ( isset( $items['orders'] ) ) {
			$items['orders'] = __( 'My Orders', 'quantivista' );
		}
		if ( isset( $items['downloads'] ) ) {
			$items['downloads'] = __( 'My Downloads', 'quantivista' );
		}
		if ( isset( $items['edit-address'] ) ) {
			$items['edit-address'] = __( 'Billing Details', 'quantivista' );
		}
		if ( isset( $items['edit-account'] ) ) {
			$items['edit-account'] = __( 'Account Settings', 'quantivista' );
		}
		return $items;
	},
	10
);

/**
 * Shop filter: delivery type (?delivery=download|service).
 */
add_action(
	'woocommerce_product_query',
	function ( $query ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$delivery = isset( $_GET['delivery'] ) ? sanitize_key( wp_unslash( $_GET['delivery'] ) ) : '';
		if ( ! in_array( $delivery, array( 'download', 'service' ), true ) ) {
			return;
		}
		$meta_query   = (array) $query->get( 'meta_query' );
		$meta_query[] = array(
			'key'   => 'qv_delivery',
			'value' => $delivery,
		);
		$query->set( 'meta_query', $meta_query );
	}
);
