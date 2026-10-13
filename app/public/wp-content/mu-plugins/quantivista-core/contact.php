<?php
/**
 * Contact form on the Contact page (both languages), built in so it works
 * without any form plugin. Messages are saved as a private post type
 * (Messages in the dashboard) and emailed to the Customizer contact email.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'qv_message',
			array(
				'labels'          => array(
					'name'          => __( 'Messages', 'quantivista' ),
					'singular_name' => __( 'Message', 'quantivista' ),
					'edit_item'     => __( 'Message', 'quantivista' ),
					'search_items'  => __( 'Search messages', 'quantivista' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 56,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

/**
 * Topics offered in the "What do you need?" list: key => label.
 */
function qvc_contact_topics() {
	return array(
		'clinic'  => __( 'Clinic software', 'quantivista' ),
		'webapp'  => __( 'A custom web application', 'quantivista' ),
		'website' => __( 'A website', 'quantivista' ),
		'product' => __( 'Changes to a product I bought', 'quantivista' ),
		'other'   => __( 'Something else', 'quantivista' ),
	);
}

/**
 * Print the contact form with the result of the last submission, if any.
 */
function qvc_contact_form() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$status   = isset( $_GET['sent'] ) ? sanitize_key( $_GET['sent'] ) : '';
	$messages = array(
		'ok'      => array( 'ok', __( 'Thanks! Your message has been sent. I will reply within two business days.', 'quantivista' ) ),
		'invalid' => array( 'error', __( 'Please fill in every field marked * correctly and tick the consent box.', 'quantivista' ) ),
		'busy'    => array( 'error', __( 'Too many messages were sent from here. Please try again in an hour or email me.', 'quantivista' ) ),
	);
	$privacy  = get_privacy_policy_url();
	if ( function_exists( 'pll_get_post' ) && (int) get_option( 'wp_page_for_privacy_policy' ) ) {
		$privacy_id = pll_get_post( (int) get_option( 'wp_page_for_privacy_policy' ) );
		$privacy    = $privacy_id ? get_permalink( $privacy_id ) : $privacy;
	}
	?>
	<?php if ( isset( $messages[ $status ] ) ) : ?>
		<div class="form-alert form-alert--<?php echo esc_attr( $messages[ $status ][0] ); ?>" role="status"><?php echo esc_html( $messages[ $status ][1] ); ?></div>
	<?php endif; ?>
	<form class="form-grid contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="qvc_contact">
		<input type="hidden" name="lang" value="<?php echo esc_attr( is_rtl() ? 'fa' : 'en' ); ?>">
		<?php wp_nonce_field( 'qvc_contact', 'qvc_nonce' ); ?>
		<p class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

		<p><label><?php esc_html_e( 'Full name', 'quantivista' ); ?> *<input type="text" name="your_name" required maxlength="120" autocomplete="name"></label></p>
		<p><label><?php esc_html_e( 'Email', 'quantivista' ); ?> *<input type="email" name="your_email" required maxlength="120" dir="ltr" autocomplete="email" placeholder="you@example.com"></label></p>
		<p><label><?php esc_html_e( 'Phone', 'quantivista' ); ?><input type="tel" name="your_phone" maxlength="20" dir="ltr" autocomplete="tel" placeholder="<?php echo is_rtl() ? '09121234567' : '+98 912 123 4567'; ?>"></label></p>
		<p><label><?php esc_html_e( 'Clinic or company', 'quantivista' ); ?><input type="text" name="your_company" maxlength="120" autocomplete="organization"></label></p>
		<p class="full"><label><?php esc_html_e( 'What do you need?', 'quantivista' ); ?> *
			<select name="topic" required>
				<option value=""><?php esc_html_e( 'Choose one', 'quantivista' ); ?></option>
				<?php foreach ( qvc_contact_topics() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label></p>
		<p class="full"><label><?php esc_html_e( 'Message', 'quantivista' ); ?> *<textarea name="your_message" required maxlength="3000" rows="6" placeholder="<?php esc_attr_e( 'Goals, timeline, links to references…', 'quantivista' ); ?>"></textarea></label></p>
		<p class="full contact-form__consent"><label><input type="checkbox" name="consent" value="1" required>
			<span>
				<?php
				printf(
					/* translators: %s: link to the privacy policy */
					esc_html__( 'I agree that Hassani Labs may use my details to answer this message, as described in the %s.', 'quantivista' ),
					'<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Privacy Policy', 'quantivista' ) . '</a>'
				);
				?>
			</span>
		</label></p>
		<p class="full"><button type="submit" class="btn btn--accent btn--lg"><?php esc_html_e( 'Send message', 'quantivista' ); ?></button></p>
	</form>
	<?php
}

add_action( 'admin_post_nopriv_qvc_contact', 'qvc_contact_submit' );
add_action( 'admin_post_qvc_contact', 'qvc_contact_submit' );

function qvc_contact_submit() {
	$back = wp_get_referer() ? remove_query_arg( 'sent', wp_get_referer() ) : home_url( '/' );
	$done = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'sent', $status, $back ) . '#contact-form' );
		exit;
	};

	if ( ! isset( $_POST['qvc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qvc_nonce'] ) ), 'qvc_contact' ) ) {
		$done( 'invalid' );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot: pretend it worked.
		$done( 'ok' );
	}

	$ip_hash = substr( hash_hmac( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '', wp_salt( 'nonce' ) ), 0, 20 );
	$hits    = (int) get_transient( 'qvc_cm_' . $ip_hash );
	if ( $hits >= 5 ) {
		$done( 'busy' );
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
	$text    = function ( $key, $max = 120 ) {
		return isset( $_POST[ $key ] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), 0, $max ) : '';
	};
	$name    = $text( 'your_name' );
	$email   = sanitize_email( $text( 'your_email' ) );
	$phone   = qvc_latin_digits( $text( 'your_phone', 20 ) );
	$company = $text( 'your_company' );
	$topic   = $text( 'topic', 20 );
	$lang    = 'fa' === $text( 'lang', 2 ) ? 'fa' : 'en';
	$message = isset( $_POST['your_message'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['your_message'] ) ), 0, 3000 ) : '';
	$consent = ! empty( $_POST['consent'] );
	// phpcs:enable

	$topics = qvc_contact_topics();
	if ( '' === $name || ! is_email( $email ) || '' === trim( $message ) || ! isset( $topics[ $topic ] ) || ! $consent ) {
		$done( 'invalid' );
	}
	set_transient( 'qvc_cm_' . $ip_hash, $hits + 1, HOUR_IN_SECONDS );

	$lines = array(
		'Name: ' . $name,
		'Email: ' . $email,
		'Phone: ' . ( $phone ? $phone : '-' ),
		'Clinic or company: ' . ( $company ? $company : '-' ),
		'Topic: ' . $topics[ $topic ],
		'Language: ' . ( 'fa' === $lang ? 'Persian' : 'English' ),
		'',
		$message,
	);

	$id = wp_insert_post(
		array(
			'post_type'    => 'qv_message',
			'post_status'  => 'private',
			'post_title'   => $name . ' — ' . $topics[ $topic ],
			'post_content' => implode( "\n", $lines ),
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_qv_message_email', $email );
	}

	$to = function_exists( 'qv_mod' ) && qv_mod( 'contact_email' ) ? qv_mod( 'contact_email' ) : get_option( 'admin_email' );
	wp_mail( $to, '[Hassani Labs] ' . $name . ' — ' . $topics[ $topic ], implode( "\n", $lines ), array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );

	$done( 'ok' );
}
