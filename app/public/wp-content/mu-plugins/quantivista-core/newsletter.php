<?php
/**
 * Footer newsletter signup with double opt-in. Subscribers are stored as a
 * private post type (Site Content → Newsletter Subscribers) and can be
 * exported to CSV for an ESP (Mailchimp, Brevo, …).
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_qv_newsletter', 'qvc_newsletter_subscribe' );
add_action( 'admin_post_qv_newsletter', 'qvc_newsletter_subscribe' );

function qvc_newsletter_subscribe() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( 'newsletter', $back );
	$done = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'newsletter', $status, $back ) . '#newsletter-title' );
		exit;
	};

	if ( ! isset( $_POST['qv_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qv_newsletter_nonce'] ) ), 'qv_newsletter' ) ) {
		$done( 'invalid' );
	}

	// Honeypot: bots fill every field. Pretend success.
	if ( ! empty( $_POST['website'] ) ) {
		$done( 'success' );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		$done( 'invalid' );
	}

	// Basic rate limit per IP hash.
	$ip_hash = hash_hmac( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '', wp_salt( 'nonce' ) );
	$hits    = (int) get_transient( 'qv_nl_' . substr( $ip_hash, 0, 20 ) );
	if ( $hits >= 5 ) {
		$done( 'invalid' );
	}
	set_transient( 'qv_nl_' . substr( $ip_hash, 0, 20 ), $hits + 1, HOUR_IN_SECONDS );

	$existing = get_posts(
		array(
			'post_type'      => 'qv_subscriber',
			'post_status'    => array( 'publish', 'qv_pending' ),
			'title'          => $email,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( $existing && 'publish' === get_post_status( $existing[0] ) ) {
		$done( 'exists' );
	}

	$subscriber_id = $existing ? $existing[0] : wp_insert_post(
		array(
			'post_type'   => 'qv_subscriber',
			'post_title'  => $email,
			'post_status' => 'qv_pending',
		)
	);

	if ( ! $subscriber_id || is_wp_error( $subscriber_id ) ) {
		$done( 'invalid' );
	}

	$token = wp_generate_password( 32, false, false );
	update_post_meta( $subscriber_id, 'qv_token_hash', hash_hmac( 'sha256', $token, wp_salt( 'auth' ) ) );
	update_post_meta( $subscriber_id, 'qv_consent_at', gmdate( 'c' ) );
	update_post_meta( $subscriber_id, 'qv_source', esc_url_raw( $back ) );

	$confirm = add_query_arg( array( 'qv_nl_confirm' => $token, 'sid' => $subscriber_id ), home_url( '/' ) );
	$body    = '<p>' . esc_html__( 'Please confirm your subscription to the Hassani Labs newsletter:', 'quantivista' ) . '</p>'
		. '<p><a href="' . esc_url( $confirm ) . '">' . esc_html__( 'Yes, subscribe me', 'quantivista' ) . '</a></p>'
		. '<p style="font-size:13px;">' . esc_html__( 'If you did not request this, just ignore this email.', 'quantivista' ) . '</p>';

	if ( function_exists( 'WC' ) && WC()->mailer() ) {
		$mailer = WC()->mailer();
		$mailer->send( $email, __( 'Confirm your subscription', 'quantivista' ), $mailer->wrap_message( __( 'One more step', 'quantivista' ), $body ) );
	} else {
		wp_mail( $email, __( 'Confirm your subscription', 'quantivista' ), $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}

	$done( 'success' );
}

add_action(
	'template_redirect',
	function () {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- HMAC token link.
		if ( empty( $_GET['qv_nl_confirm'] ) || empty( $_GET['sid'] ) ) {
			return;
		}
		$id    = absint( $_GET['sid'] );
		$token = sanitize_text_field( wp_unslash( $_GET['qv_nl_confirm'] ) );
		// phpcs:enable
		$hash = (string) get_post_meta( $id, 'qv_token_hash', true );
		if ( 'qv_subscriber' === get_post_type( $id ) && $hash && hash_equals( $hash, hash_hmac( 'sha256', $token, wp_salt( 'auth' ) ) ) ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
			delete_post_meta( $id, 'qv_token_hash' );
			update_post_meta( $id, 'qv_confirmed_at', gmdate( 'c' ) );
		}
		wp_safe_redirect( add_query_arg( 'newsletter', 'success', home_url( '/' ) ) . '#newsletter-title' );
		exit;
	}
);

/**
 * CSV export (Site Content → Newsletter Subscribers → "Export confirmed").
 */
add_action(
	'admin_post_qv_newsletter_export',
	function () {
		if ( ! current_user_can( 'edit_pages' ) || ! check_admin_referer( 'qv_newsletter_export' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'quantivista' ) );
		}
		$ids = get_posts(
			array(
				'post_type'      => 'qv_subscriber',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=quantivista-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'email', 'confirmed_at' ) );
		foreach ( $ids as $id ) {
			fputcsv( $out, array( get_the_title( $id ), get_post_meta( $id, 'qv_confirmed_at', true ) ) );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
);

add_action(
	'manage_posts_extra_tablenav',
	function ( $which ) {
		if ( 'top' !== $which || 'qv_subscriber' !== get_current_screen()->post_type ) {
			return;
		}
		printf(
			'<a class="button" href="%s">%s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=qv_newsletter_export' ), 'qv_newsletter_export' ) ),
			esc_html__( 'Export confirmed (CSV)', 'quantivista' )
		);
	}
);
