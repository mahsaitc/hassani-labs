<?php
/**
 * "Store Manager" role: products, orders, coupons, reviews and customer
 * accounts — no access to site settings, themes, plugins, pages or other
 * staff accounts.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

const QVC_ROLES_VERSION = '2';

add_action( 'init', 'qvc_maybe_register_roles' );
function qvc_maybe_register_roles() {
	if ( get_option( 'qvc_roles_version' ) === QVC_ROLES_VERSION ) {
		return;
	}

	$caps = array(
		'read'                     => true,
		'view_admin_dashboard'     => true, // Lets WooCommerce allow wp-admin access without edit_posts.
		'upload_files'             => true,
		'list_users'               => true,
		'edit_users'               => true,
		'moderate_comments'        => true, // Product reviews screen.
		'view_woocommerce_reports' => true,
	);

	foreach ( array( 'product', 'shop_order', 'shop_coupon' ) as $type ) {
		foreach ( array( 'edit_%s', 'read_%s', 'delete_%s', 'edit_%ss', 'edit_others_%ss', 'publish_%ss', 'read_private_%ss', 'delete_%ss', 'delete_private_%ss', 'delete_published_%ss', 'delete_others_%ss', 'edit_private_%ss', 'edit_published_%ss' ) as $pattern ) {
			$caps[ sprintf( $pattern, $type ) ] = true;
		}
		foreach ( array( 'manage_%s_terms', 'edit_%s_terms', 'delete_%s_terms', 'assign_%s_terms' ) as $pattern ) {
			$caps[ sprintf( $pattern, $type ) ] = true;
		}
	}

	remove_role( 'qv_store_manager' );
	add_role( 'qv_store_manager', __( 'Store Manager', 'quantivista' ), $caps );
	update_option( 'qvc_roles_version', QVC_ROLES_VERSION );
}

function qvc_is_store_manager( $user = null ) {
	$user = $user ? $user : wp_get_current_user();
	return $user instanceof WP_User && in_array( 'qv_store_manager', (array) $user->roles, true );
}

/**
 * Store managers may only assign/edit the customer role.
 */
add_filter(
	'editable_roles',
	function ( $roles ) {
		if ( qvc_is_store_manager() ) {
			return array_intersect_key( $roles, array( 'customer' => true ) );
		}
		return $roles;
	},
	20
);

/**
 * Store managers may only edit customer accounts (never staff or themselves'
 * role).
 */
add_filter(
	'map_meta_cap',
	function ( $caps, $cap, $user_id, $args ) {
		if ( ! in_array( $cap, array( 'edit_user', 'delete_user', 'promote_user', 'remove_user' ), true ) || empty( $args[0] ) ) {
			return $caps;
		}
		$actor = get_userdata( $user_id );
		if ( ! qvc_is_store_manager( $actor ) || (int) $args[0] === (int) $user_id ) {
			return $caps;
		}
		$target = get_userdata( (int) $args[0] );
		if ( ! $target || array_diff( (array) $target->roles, array( 'customer' ) ) ) {
			return array( 'do_not_allow' );
		}
		if ( in_array( $cap, array( 'delete_user', 'promote_user', 'remove_user' ), true ) ) {
			return array( 'do_not_allow' );
		}
		return $caps;
	},
	20,
	4
);

/**
 * Keep store managers focused: trim unrelated menu items and make sure
 * Coupons is reachable even though the Marketing hub needs manage_woocommerce.
 */
add_action(
	'admin_menu',
	function () {
		if ( ! qvc_is_store_manager() ) {
			return;
		}
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'edit-comments.php' );
		add_submenu_page(
			'woocommerce',
			__( 'Coupons', 'quantivista' ),
			__( 'Coupons', 'quantivista' ),
			'edit_shop_coupons',
			'edit.php?post_type=shop_coupon'
		);
		add_submenu_page(
			'woocommerce',
			__( 'Reviews', 'quantivista' ),
			__( 'Reviews', 'quantivista' ),
			'moderate_comments',
			'edit.php?post_type=product&page=product-reviews'
		);
	},
	999
);

/**
 * Store managers land on the Orders screen after logging in to wp-admin.
 */
add_filter(
	'login_redirect',
	function ( $redirect, $requested, $user ) {
		if ( $user instanceof WP_User && qvc_is_store_manager( $user ) && ( ! $requested || admin_url() === $requested ) ) {
			return admin_url( 'admin.php?page=wc-orders' );
		}
		return $redirect;
	},
	10,
	3
);
