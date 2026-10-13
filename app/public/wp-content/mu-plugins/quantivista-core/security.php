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

// Theme and plugin files cannot be edited from the dashboard (also set in wp-config.php on the host).
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Login throttling (wp-login.php and the My Account form): after 5 wrong
 * passwords from one address in 15 minutes, that address waits 15 minutes.
 * Works with or without Wordfence.
 */
const QVC_LOGIN_MAX_FAILS = 5;
const QVC_LOGIN_WINDOW    = 15 * MINUTE_IN_SECONDS;

function qvc_login_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'qvc_lf_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 24 );
}

add_filter(
	'authenticate',
	function ( $user, $username ) {
		if ( '' === (string) $username ) {
			return $user;
		}
		if ( (int) get_transient( qvc_login_key() ) >= QVC_LOGIN_MAX_FAILS ) {
			/* translators: %d: minutes */
			return new WP_Error( 'qvc_too_many_logins', sprintf( __( 'Too many failed login attempts. Please try again in %d minutes.', 'quantivista' ), (int) ( QVC_LOGIN_WINDOW / MINUTE_IN_SECONDS ) ) );
		}
		return $user;
	},
	30,
	2
);

add_action(
	'wp_login_failed',
	function () {
		$key = qvc_login_key();
		set_transient( $key, (int) get_transient( $key ) + 1, QVC_LOGIN_WINDOW );
	}
);

add_action(
	'wp_login',
	function () {
		delete_transient( qvc_login_key() );
	}
);

/**
 * On HTTPS: HSTS and a content security policy that blocks plugins
 * (Flash and the like), foreign <base> tags and framing by other sites.
 */
add_action(
	'send_headers',
	function () {
		if ( headers_sent() || ! is_ssl() ) {
			return;
		}
		header( 'Strict-Transport-Security: max-age=31536000' );
		header( "Content-Security-Policy: object-src 'none'; base-uri 'self'; frame-ancestors 'self'; upgrade-insecure-requests" );
	},
	11
);

/**
 * Asset URLs don't carry the WordPress version (?ver=7.1).
 */
function qvc_strip_wp_version( $src ) {
	if ( $src && str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'qvc_strip_wp_version', 20 );
add_filter( 'script_loader_src', 'qvc_strip_wp_version', 20 );

/**
 * Author pages show the login name in the address (/author/<login>/):
 * point author links at the About page instead.
 */
add_filter(
	'author_link',
	function ( $link ) {
		$about = function_exists( 'qv_page_by_path' ) ? qv_page_by_path( 'about' ) : get_page_by_path( 'about' );
		return $about ? get_permalink( $about ) : home_url( '/' );
	}
);
add_action(
	'template_redirect',
	function () {
		if ( is_author() ) {
			wp_safe_redirect( get_author_posts_url( (int) get_queried_object_id() ), 301 );
			exit;
		}
	},
	2
);

/**
 * Comments from visitors wait for approval (spam links never go live).
 */
add_filter(
	'pre_comment_approved',
	function ( $approved ) {
		if ( 'spam' === $approved || 'trash' === $approved || current_user_can( 'moderate_comments' ) ) {
			return $approved;
		}
		return 0;
	},
	99
);
