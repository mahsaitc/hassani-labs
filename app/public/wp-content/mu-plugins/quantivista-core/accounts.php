<?php
/**
 * Customer accounts: email verification for new registrations, account
 * notices, and locally hosted avatars.
 *
 * Flow:
 *  1. Customer registers on My Account → meta qv_email_verified = '0',
 *     verification email sent, no auto-login.
 *  2. Login is refused until the link is clicked (with a resend link).
 *  3. Link validates an HMAC'd, expiring token → meta = '1'.
 * Users without the meta (admins, legacy or admin-created accounts) are
 * treated as verified.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

function qvc_user_is_unverified( $user_id ) {
	return '0' === get_user_meta( (int) $user_id, 'qv_email_verified', true );
}

function qvc_account_url( $args = array() ) {
	$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
	return add_query_arg( $args, $base );
}

function qvc_resend_signature( $user_id ) {
	return hash_hmac( 'sha256', 'qv-resend|' . (int) $user_id, wp_salt( 'nonce' ) );
}

/**
 * Create a token and email the verification link.
 */
function qvc_send_verification_email( $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return false;
	}

	$token = wp_generate_password( 40, false, false );
	update_user_meta( $user_id, 'qv_verify_hash', hash_hmac( 'sha256', $token, wp_salt( 'auth' ) ) );
	update_user_meta( $user_id, 'qv_verify_expires', time() + 2 * DAY_IN_SECONDS );

	$url     = qvc_account_url( array( 'qv_verify' => $token, 'uid' => $user_id ) );
	$name    = $user->first_name ? $user->first_name : $user->display_name;
	$subject = __( 'Verify your Quantivista account', 'quantivista' );
	$heading = __( 'Confirm your email address', 'quantivista' );
	$body    = '<p>' . sprintf( /* translators: %s: name */ esc_html__( 'Hi %s,', 'quantivista' ), esc_html( $name ) ) . '</p>'
		. '<p>' . esc_html__( 'Thanks for creating a Quantivista account. Please confirm your email address to activate it and start shopping.', 'quantivista' ) . '</p>'
		. '<p style="margin:28px 0;"><a href="' . esc_url( $url ) . '" style="display:inline-block;padding:12px 22px;border-radius:10px;background:#070B24;color:#ffffff;font-weight:600;text-decoration:none;">' . esc_html__( 'Verify my email', 'quantivista' ) . '</a></p>'
		. '<p style="font-size:13px;">' . esc_html__( 'This link expires in 48 hours. If you did not create an account, you can safely ignore this email.', 'quantivista' ) . '</p>';

	if ( function_exists( 'WC' ) && WC()->mailer() ) {
		$mailer = WC()->mailer();
		return $mailer->send( $user->user_email, $subject, $mailer->wrap_message( $heading, $body ) );
	}
	return wp_mail( $user->user_email, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
}

/**
 * 1. New front-end registrations start unverified.
 */
add_action(
	'woocommerce_created_customer',
	function ( $customer_id ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return; // Accounts created by staff in wp-admin are trusted.
		}
		update_user_meta( $customer_id, 'qv_email_verified', '0' );
		qvc_send_verification_email( $customer_id );
	},
	5
);

// No automatic login for unverified customers.
add_filter(
	'woocommerce_registration_auth_new_customer',
	function ( $auth, $customer_id ) {
		return qvc_user_is_unverified( $customer_id ) ? false : $auth;
	},
	10,
	2
);

add_filter(
	'woocommerce_registration_redirect',
	function ( $redirect ) {
		return is_user_logged_in() ? $redirect : qvc_account_url( array( 'qv_notice' => 'verify_sent' ) );
	},
	20
);

/**
 * 2. Refuse login until verified (runs after the password check).
 */
add_filter(
	'authenticate',
	function ( $user ) {
		if ( $user instanceof WP_User && qvc_user_is_unverified( $user->ID ) ) {
			$resend = qvc_account_url(
				array(
					'qv_resend' => $user->ID,
					'sig'       => qvc_resend_signature( $user->ID ),
				)
			);
			return new WP_Error(
				'qv_unverified',
				sprintf(
					/* translators: %s: resend URL */
					__( '<strong>Please verify your email address first.</strong> Check your inbox for the verification link, or <a href="%s">send it again</a>.', 'quantivista' ),
					esc_url( $resend )
				)
			);
		}
		return $user;
	},
	50
);

/**
 * 3. Verification + resend links.
 */
add_action( 'template_redirect', 'qvc_handle_verification_links', 5 );
function qvc_handle_verification_links() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- links are authenticated by HMAC tokens.
	if ( isset( $_GET['qv_verify'], $_GET['uid'] ) ) {
		$user_id = absint( $_GET['uid'] );
		$token   = sanitize_text_field( wp_unslash( $_GET['qv_verify'] ) );
		$hash    = (string) get_user_meta( $user_id, 'qv_verify_hash', true );
		$expires = (int) get_user_meta( $user_id, 'qv_verify_expires', true );
		$valid   = $hash && $expires >= time() && hash_equals( $hash, hash_hmac( 'sha256', $token, wp_salt( 'auth' ) ) );

		if ( $valid ) {
			update_user_meta( $user_id, 'qv_email_verified', '1' );
			delete_user_meta( $user_id, 'qv_verify_hash' );
			delete_user_meta( $user_id, 'qv_verify_expires' );
			do_action( 'qvc_email_verified', $user_id );
		}
		wp_safe_redirect( qvc_account_url( array( 'qv_notice' => $valid ? 'verified' : 'invalid_link' ) ) );
		exit;
	}

	if ( isset( $_GET['qv_resend'], $_GET['sig'] ) ) {
		$user_id = absint( $_GET['qv_resend'] );
		$sig     = sanitize_text_field( wp_unslash( $_GET['sig'] ) );
		if ( hash_equals( qvc_resend_signature( $user_id ), $sig ) && qvc_user_is_unverified( $user_id ) && ! get_transient( 'qv_resend_' . $user_id ) ) {
			qvc_send_verification_email( $user_id );
			set_transient( 'qv_resend_' . $user_id, 1, 5 * MINUTE_IN_SECONDS );
		}
		wp_safe_redirect( qvc_account_url( array( 'qv_notice' => 'resent' ) ) );
		exit;
	}
	// phpcs:enable
}

/**
 * Notices passed via query arg (guest sessions don't persist WC notices
 * across redirects).
 */
function qvc_account_notice_messages() {
	return array(
		'verify_sent'    => array( 'success', __( 'Almost there! We sent a verification link to your email address. Please confirm it, then log in.', 'quantivista' ) ),
		'verified'       => array( 'success', __( 'Your email address is verified. You can now log in.', 'quantivista' ) ),
		'invalid_link'   => array( 'error', __( 'That verification link is invalid or has expired. Try logging in to request a new one.', 'quantivista' ) ),
		'resent'         => array( 'notice', __( 'If your account still needs verification, a new link is on its way.', 'quantivista' ) ),
		'login_required' => array( 'notice', __( 'Please log in or create an account to complete your purchase.', 'quantivista' ) ),
	);
}

add_action(
	'woocommerce_before_customer_login_form',
	function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key      = isset( $_GET['qv_notice'] ) ? sanitize_key( wp_unslash( $_GET['qv_notice'] ) ) : '';
		$messages = qvc_account_notice_messages();
		if ( isset( $messages[ $key ] ) ) {
			wc_print_notice( $messages[ $key ][1], $messages[ $key ][0] );
		}
	}
);

/**
 * Admin: verification status on profiles and in the users list.
 */
function qvc_render_verification_field( $user ) {
	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}
	$status = get_user_meta( $user->ID, 'qv_email_verified', true );
	?>
	<h2><?php esc_html_e( 'Account verification', 'quantivista' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Email verified', 'quantivista' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="qv_email_verified" value="1" <?php checked( '0' !== $status ); ?>>
					<?php esc_html_e( 'This customer has confirmed their email address', 'quantivista' ); ?>
				</label>
				<input type="hidden" name="qv_email_verified_present" value="1">
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'qvc_render_verification_field' );
add_action( 'edit_user_profile', 'qvc_render_verification_field' );

function qvc_save_verification_field( $user_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- core verifies the update-user nonce before these hooks.
	if ( ! current_user_can( 'edit_user', $user_id ) || empty( $_POST['qv_email_verified_present'] ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$verified = ! empty( $_POST['qv_email_verified'] );
	$current  = get_user_meta( $user_id, 'qv_email_verified', true );
	if ( $verified && '0' === $current ) {
		update_user_meta( $user_id, 'qv_email_verified', '1' );
	} elseif ( ! $verified ) {
		update_user_meta( $user_id, 'qv_email_verified', '0' );
	}
}
add_action( 'personal_options_update', 'qvc_save_verification_field' );
add_action( 'edit_user_profile_update', 'qvc_save_verification_field' );

add_filter(
	'manage_users_columns',
	function ( $columns ) {
		$columns['qv_verified'] = __( 'Email', 'quantivista' );
		return $columns;
	}
);

add_filter(
	'manage_users_custom_column',
	function ( $output, $column, $user_id ) {
		if ( 'qv_verified' !== $column ) {
			return $output;
		}
		return qvc_user_is_unverified( $user_id )
			? '<span style="color:#b45309;font-weight:600;">' . esc_html__( 'Pending', 'quantivista' ) . '</span>'
			: '<span style="color:#15803d;">' . esc_html__( 'Verified', 'quantivista' ) . '</span>';
	},
	10,
	3
);

/**
 * Local avatars: users with a qv_avatar_id attachment use it instead of Gravatar.
 */
add_filter(
	'pre_get_avatar_data',
	function ( $args, $id_or_email ) {
		$user_id = 0;
		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( $id_or_email instanceof WP_User ) {
			$user_id = $id_or_email->ID;
		} elseif ( $id_or_email instanceof WP_Post ) {
			$user_id = (int) $id_or_email->post_author;
		} elseif ( $id_or_email instanceof WP_Comment ) {
			$user_id = (int) $id_or_email->user_id;
		} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user    = get_user_by( 'email', $id_or_email );
			$user_id = $user ? $user->ID : 0;
		}

		$attachment = $user_id ? (int) get_user_meta( $user_id, 'qv_avatar_id', true ) : 0;
		if ( $attachment ) {
			$size = (int) ( $args['size'] ?? 96 ) * 2;
			$src  = wp_get_attachment_image_url( $attachment, array( $size, $size ) );
			if ( $src ) {
				$args['url']          = $src;
				$args['found_avatar'] = true;
			}
		}
		return $args;
	},
	10,
	2
);
