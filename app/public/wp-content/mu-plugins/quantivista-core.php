<?php
/**
 * Plugin Name: Quantivista Core
 * Description: Site functionality for Hassani Labs — content types, custom fields, store extensions (license keys, wishlist, checkout rules), account email verification, Store Manager role, newsletter, clinic software orders and security hardening.
 * Version:     1.0.0
 * Author:      Mahsa Hassani
 * License:     GPL-2.0-or-later
 *
 * Lives in mu-plugins so it cannot be deactivated from the dashboard and
 * keeps working if the theme is switched.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

define( 'QVC_VERSION', '1.0.0' );
define( 'QVC_DIR', __DIR__ . '/quantivista-core' );

require_once QVC_DIR . '/post-types.php';
require_once QVC_DIR . '/acf-fields.php';
require_once QVC_DIR . '/security.php';
require_once QVC_DIR . '/roles.php';
require_once QVC_DIR . '/accounts.php';
require_once QVC_DIR . '/newsletter.php';
require_once QVC_DIR . '/clinic-orders.php';
require_once QVC_DIR . '/contact.php';
require_once QVC_DIR . '/portfolio.php';

// Store modules depend on WooCommerce, which loads after mu-plugins.
add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once QVC_DIR . '/shop.php';
		require_once QVC_DIR . '/licenses.php';
		require_once QVC_DIR . '/wishlist.php';
	}
);
