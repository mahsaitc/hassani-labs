<?php
/**
 * Baseline hardening that complements Wordfence.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

// XML-RPC is a common brute-force vector and unused here.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

// Don't advertise the WordPress version.
add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'wp_generator' );

/**
 * Security headers for front-end and admin responses.
 */
add_action(
	'send_headers',
	function () {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()' );
	}
);

/**
 * Block user enumeration via the REST API for anonymous visitors.
 */
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( is_user_logged_in() ) {
			return $endpoints;
		}
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( str_starts_with( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}
);

/**
 * Block ?author=N scans (pretty /author/slug/ archives keep working).
 */
add_action(
	'template_redirect',
	function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_user_logged_in() && isset( $_GET['author'] ) && is_numeric( $_GET['author'] ) ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);

/**
 * Generic error on wp-login.php so it doesn't reveal which part was wrong.
 */
add_filter(
	'login_errors',
	function ( $error ) {
		global $errors;
		if ( is_wp_error( $errors ) && array_intersect( $errors->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
			return '<strong>' . esc_html__( 'Error:', 'quantivista' ) . '</strong> ' . esc_html__( 'The login details you entered are incorrect.', 'quantivista' );
		}
		return $error;
	}
);

// Application passwords are not needed for this site.
add_filter( 'wp_is_application_passwords_available', '__return_false' );
