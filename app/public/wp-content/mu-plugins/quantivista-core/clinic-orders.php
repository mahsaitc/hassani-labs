<?php
/**
 * Clinic software orders: the Persian order form on the "Clinic Software"
 * page, payment through Zarinpal (in rials, independent of the store's
 * currency) and the order list in the dashboard.
 *
 * Orders are a private post type (Clinic Orders). Settings (Clinic Orders →
 * Settings): Zarinpal merchant ID, sandbox switch, demo link, plan prices in
 * toman. A plan without a price is taken as a request and followed up by
 * phone, so the form works before a merchant ID exists.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

const QVC_CLINIC_OPTION = 'qvc_clinic_settings';

/**
 * Settings with defaults.
 */
function qvc_clinic_settings() {
	return wp_parse_args(
		(array) get_option( QVC_CLINIC_OPTION, array() ),
		array(
			'merchant_id'   => '',
			'sandbox'       => '1',
			'demo_url'      => '',
			'notify_email'  => get_option( 'admin_email' ),
			'price_basic'   => '',
			'price_pro'     => '',
			'price_renewal' => '',
		)
	);
}

/**
 * Plans offered on the page. Prices are in toman; empty = "price on request".
 */
function qvc_clinic_plans() {
	$s = qvc_clinic_settings();
	return array(
		'basic'   => array(
			'title'    => 'راه‌اندازی پایه',
			'lead'     => 'نصب روی هاست کلینیک و یک سال پشتیبانی',
			'price'    => (int) $s['price_basic'],
			'features' => array( 'نصب روی هاست و دامنه خود کلینیک', 'SSL و بکاپ شبانه رمزدار', 'لوگو، نام و رنگ‌های کلینیک', 'یک سال به‌روزرسانی و پشتیبانی' ),
		),
		'pro'     => array(
			'title'    => 'حرفه‌ای',
			'lead'     => 'آماده کار از روز اول',
			'price'    => (int) $s['price_pro'],
			'popular'  => true,
			'features' => array( 'همه امکانات راه‌اندازی پایه', 'ورود خدمات، پزشکان و ساعات کاری', 'اتصال درگاه پرداخت و ایمیل کلینیک', 'آموزش آنلاین کارکنان' ),
		),
		'renewal' => array(
			'title'    => 'تمدید سالانه',
			'lead'     => 'برای کلینیک‌هایی که نرم‌افزار را دارند',
			'price'    => (int) $s['price_renewal'],
			'features' => array( 'یک سال دیگر به‌روزرسانی', 'پشتیبانی و رفع اشکال', 'بررسی بکاپ‌ها و امنیت' ),
		),
	);
}

/**
 * Hosting answers on the form.
 */
function qvc_clinic_hosting_options() {
	return array(
		'vps'    => 'سرور مجازی لینوکس دارم',
		'shared' => 'هاست ویندوزی (Plesk) دارم',
		'none'   => 'هنوز هاست ندارم و راهنمایی می‌خواهم',
	);
}

/**
 * Persian and Arabic digits to Latin.
 */
function qvc_latin_digits( $value ) {
	return strtr( (string) $value, array_combine( array_merge( mb_str_split( '۰۱۲۳۴۵۶۷۸۹' ), mb_str_split( '٠١٢٣٤٥٦٧٨٩' ) ), array_merge( range( 0, 9 ), range( 0, 9 ) ) ) );
}

function qvc_toman( $amount ) {
	return number_format( (int) $amount ) . ' تومان';
}

/* ------------------------------------------------------------------------
 * Post type + dashboard list
 * --------------------------------------------------------------------- */
add_action(
	'init',
	function () {
		register_post_type(
			'qv_clinic_order',
			array(
				'labels'          => array(
					'name'          => __( 'Clinic Orders', 'quantivista' ),
					'singular_name' => __( 'Clinic Order', 'quantivista' ),
					'edit_item'     => __( 'Clinic Order', 'quantivista' ),
					'search_items'  => __( 'Search orders', 'quantivista' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-heart',
				'menu_position'   => 57,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

function qvc_clinic_status_labels() {
	return array(
		'request' => __( 'Request (no payment)', 'quantivista' ),
		'pending' => __( 'Awaiting payment', 'quantivista' ),
		'paid'    => __( 'Paid', 'quantivista' ),
		'failed'  => __( 'Payment failed', 'quantivista' ),
	);
}

add_filter(
	'manage_qv_clinic_order_posts_columns',
	function ( $columns ) {
		return array(
			'cb'         => $columns['cb'],
			'title'      => __( 'Clinic', 'quantivista' ),
			'qvc_plan'   => __( 'Plan', 'quantivista' ),
			'qvc_mobile' => __( 'Mobile', 'quantivista' ),
			'qvc_amount' => __( 'Amount', 'quantivista' ),
			'qvc_status' => __( 'Status', 'quantivista' ),
			'date'       => $columns['date'],
		);
	}
);

add_action(
	'manage_qv_clinic_order_posts_custom_column',
	function ( $column, $post_id ) {
		$plans = qvc_clinic_plans();
		switch ( $column ) {
			case 'qvc_plan':
				$plan = get_post_meta( $post_id, 'qvc_plan', true );
				echo esc_html( $plans[ $plan ]['title'] ?? $plan );
				break;
			case 'qvc_mobile':
				echo esc_html( get_post_meta( $post_id, 'qvc_mobile', true ) );
				break;
			case 'qvc_amount':
				$amount = (int) get_post_meta( $post_id, 'qvc_amount', true );
				echo $amount ? esc_html( qvc_toman( $amount ) ) : '—';
				break;
			case 'qvc_status':
				$labels = qvc_clinic_status_labels();
				$status = get_post_meta( $post_id, 'qvc_status', true );
				echo esc_html( $labels[ $status ] ?? $status );
				$ref = get_post_meta( $post_id, 'qvc_ref_id', true );
				if ( $ref ) {
					echo '<br><small>' . esc_html__( 'Ref:', 'quantivista' ) . ' ' . esc_html( $ref ) . '</small>';
				}
				break;
		}
	},
	10,
	2
);

add_action(
	'add_meta_boxes_qv_clinic_order',
	function () {
		add_meta_box(
			'qvc_clinic_order',
			__( 'Order details', 'quantivista' ),
			function ( $post ) {
				$plans   = qvc_clinic_plans();
				$hosting = qvc_clinic_hosting_options();
				$labels  = qvc_clinic_status_labels();
				$rows    = array(
					__( 'Doctor / contact', 'quantivista' ) => get_post_meta( $post->ID, 'qvc_contact', true ),
					__( 'Mobile', 'quantivista' )           => get_post_meta( $post->ID, 'qvc_mobile', true ),
					__( 'Email', 'quantivista' )            => get_post_meta( $post->ID, 'qvc_email', true ),
					__( 'City', 'quantivista' )             => get_post_meta( $post->ID, 'qvc_city', true ),
					__( 'Domain', 'quantivista' )           => get_post_meta( $post->ID, 'qvc_domain', true ),
					__( 'Hosting', 'quantivista' )          => $hosting[ get_post_meta( $post->ID, 'qvc_hosting', true ) ] ?? '',
					__( 'Plan', 'quantivista' )             => $plans[ get_post_meta( $post->ID, 'qvc_plan', true ) ]['title'] ?? '',
					__( 'Amount', 'quantivista' )           => (int) get_post_meta( $post->ID, 'qvc_amount', true ) ? qvc_toman( get_post_meta( $post->ID, 'qvc_amount', true ) ) : '—',
					__( 'Status', 'quantivista' )           => $labels[ get_post_meta( $post->ID, 'qvc_status', true ) ] ?? '',
					__( 'Zarinpal ref', 'quantivista' )     => get_post_meta( $post->ID, 'qvc_ref_id', true ),
					__( 'Paid with card', 'quantivista' )   => get_post_meta( $post->ID, 'qvc_card_pan', true ),
					__( 'Notes', 'quantivista' )            => get_post_meta( $post->ID, 'qvc_notes', true ),
				);
				echo '<table class="widefat striped" dir="auto"><tbody>';
				foreach ( $rows as $label => $value ) {
					printf( '<tr><th style="width:180px">%s</th><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( (string) $value ) ) );
				}
				echo '</tbody></table>';
			},
			'qv_clinic_order',
			'normal',
			'high'
		);
	}
);

/* ------------------------------------------------------------------------
 * Settings page (Clinic Orders → Settings)
 * --------------------------------------------------------------------- */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=qv_clinic_order',
			__( 'Clinic Orders Settings', 'quantivista' ),
			__( 'Settings', 'quantivista' ),
			'manage_options',
			'qvc-clinic-settings',
			'qvc_clinic_settings_page'
		);
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'qvc_clinic',
			QVC_CLINIC_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => function ( $input ) {
					$input = (array) $input;
					$out   = array(
						'merchant_id'  => sanitize_text_field( $input['merchant_id'] ?? '' ),
						'sandbox'      => empty( $input['sandbox'] ) ? '' : '1',
						'demo_url'     => esc_url_raw( $input['demo_url'] ?? '' ),
						'notify_email' => sanitize_email( $input['notify_email'] ?? '' ),
					);
					foreach ( array( 'price_basic', 'price_pro', 'price_renewal' ) as $key ) {
						$digits      = preg_replace( '/\D/', '', qvc_latin_digits( $input[ $key ] ?? '' ) );
						$out[ $key ] = '' === $digits ? '' : (string) (int) $digits;
					}
					return $out;
				},
			)
		);
	}
);

function qvc_clinic_settings_page() {
	$s     = qvc_clinic_settings();
	$name  = QVC_CLINIC_OPTION;
	$field = function ( $key, $label, $help = '', $type = 'text' ) use ( $s, $name ) {
		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%5$s" class="regular-text" id="%1$s" name="%3$s[%1$s]" value="%4$s" dir="ltr">%6$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $s[ $key ] ),
			esc_attr( $type ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
		);
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Clinic Orders Settings', 'quantivista' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'qvc_clinic' ); ?>
			<table class="form-table" role="presentation">
				<?php
				$field( 'merchant_id', __( 'Zarinpal merchant ID', 'quantivista' ), __( '36 characters, from your Zarinpal panel. Empty = orders are taken as requests without payment.', 'quantivista' ) );
				?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Test mode', 'quantivista' ); ?></th>
					<td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[sandbox]" value="1" <?php checked( $s['sandbox'], '1' ); ?>> <?php esc_html_e( 'Use the Zarinpal sandbox (no real money). Untick after testing.', 'quantivista' ); ?></label></td>
				</tr>
				<?php
				$field( 'price_basic', __( 'Price: basic setup (toman)', 'quantivista' ), __( 'Empty = "price on request".', 'quantivista' ) );
				$field( 'price_pro', __( 'Price: professional (toman)', 'quantivista' ) );
				$field( 'price_renewal', __( 'Price: yearly renewal (toman)', 'quantivista' ) );
				$field( 'demo_url', __( 'Demo site link', 'quantivista' ), __( 'A clinic installation with Demo__Enabled=true.', 'quantivista' ), 'url' );
				$field( 'notify_email', __( 'Send new orders to', 'quantivista' ), '', 'email' );
				?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------------
 * Zarinpal (REST v4, amounts in rials)
 * --------------------------------------------------------------------- */
function qvc_zarinpal_host() {
	return '1' === qvc_clinic_settings()['sandbox'] ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com';
}

function qvc_zarinpal_call( $path, array $body ) {
	$response = wp_remote_post(
		qvc_zarinpal_host() . '/pg/v4/payment/' . $path . '.json',
		array(
			'timeout' => 20,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'code' => 0, 'message' => $response->get_error_message() );
	}
	$json = json_decode( wp_remote_retrieve_body( $response ), true );
	$data = is_array( $json['data'] ?? null ) ? $json['data'] : array();
	if ( empty( $data['code'] ) && ! empty( $json['errors']['code'] ) ) {
		$data = array( 'code' => (int) $json['errors']['code'], 'message' => $json['errors']['message'] ?? '' );
	}
	return $data;
}

/* ------------------------------------------------------------------------
 * Form submission
 * --------------------------------------------------------------------- */
add_action( 'admin_post_nopriv_qvc_clinic_order', 'qvc_clinic_order_submit' );
add_action( 'admin_post_qvc_clinic_order', 'qvc_clinic_order_submit' );

function qvc_clinic_order_submit() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'order', 'ref' ), $back );
	$done = function ( $status, $args = array() ) use ( $back ) {
		wp_safe_redirect( add_query_arg( array_merge( array( 'order' => $status ), $args ), $back ) . '#order' );
		exit;
	};

	if ( ! isset( $_POST['qvc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qvc_nonce'] ) ), 'qvc_clinic_order' ) ) {
		$done( 'invalid' );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot.
		$done( 'received' );
	}

	$ip_hash = substr( hash_hmac( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '', wp_salt( 'nonce' ) ), 0, 20 );
	$hits    = (int) get_transient( 'qvc_co_' . $ip_hash );
	if ( $hits >= 5 ) {
		$done( 'busy' );
	}
	set_transient( 'qvc_co_' . $ip_hash, $hits + 1, HOUR_IN_SECONDS );

	$text = function ( $key, $max = 120 ) {
		return isset( $_POST[ $key ] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), 0, $max ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.
	};

	$clinic  = $text( 'clinic' );
	$contact = $text( 'contact' );
	$city    = $text( 'city', 60 );
	$mobile  = preg_replace( '/\D/', '', qvc_latin_digits( $text( 'mobile', 20 ) ) );
	$email   = sanitize_email( $text( 'email' ) );
	$domain  = strtolower( trim( preg_replace( '#^https?://|/.*$#i', '', $text( 'domain' ) ) ) );
	$hosting = $text( 'hosting', 10 );
	$plan    = $text( 'plan', 10 );
	$notes   = isset( $_POST['notes'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ), 0, 1000 ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$plans   = qvc_clinic_plans();

	if ( '' === $clinic || '' === $contact || ! preg_match( '/^09\d{9}$/', $mobile ) || ( $email && ! is_email( $email ) )
		|| ( $domain && ! preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain ) )
		|| ! isset( $plans[ $plan ] ) || ! isset( qvc_clinic_hosting_options()[ $hosting ] ) || empty( $_POST['consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$done( 'invalid' );
	}

	$settings = qvc_clinic_settings();
	$amount   = $plans[ $plan ]['price'];
	$payable  = $amount > 0 && '' !== $settings['merchant_id'];

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'qv_clinic_order',
			'post_status' => 'private',
			'post_title'  => $clinic,
		)
	);
	if ( ! $order_id || is_wp_error( $order_id ) ) {
		$done( 'error' );
	}
	$meta = array(
		'qvc_contact' => $contact,
		'qvc_city'    => $city,
		'qvc_mobile'  => $mobile,
		'qvc_email'   => $email,
		'qvc_domain'  => $domain,
		'qvc_hosting' => $hosting,
		'qvc_plan'    => $plan,
		'qvc_notes'   => $notes,
		'qvc_amount'  => $payable ? $amount : 0,
		'qvc_status'  => $payable ? 'pending' : 'request',
		'qvc_return'  => esc_url_raw( $back ),
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $order_id, $key, $value );
	}

	if ( ! $payable ) {
		qvc_clinic_order_notify( $order_id );
		$done( 'received' );
	}

	$result = qvc_zarinpal_call(
		'request',
		array(
			'merchant_id'  => $settings['merchant_id'],
			'amount'       => $amount * 10,
			'callback_url' => add_query_arg( array( 'action' => 'qvc_clinic_verify', 'oid' => $order_id ), admin_url( 'admin-post.php' ) ),
			'description'  => 'سفارش نرم‌افزار کلینیک #' . $order_id . ' - ' . $plans[ $plan ]['title'],
			'metadata'     => array_filter( array( 'mobile' => $mobile, 'email' => $email, 'order_id' => (string) $order_id ) ),
		)
	);

	if ( 100 !== (int) ( $result['code'] ?? 0 ) || empty( $result['authority'] ) ) {
		update_post_meta( $order_id, 'qvc_status', 'failed' );
		update_post_meta( $order_id, 'qvc_error', sanitize_text_field( ( $result['code'] ?? '' ) . ' ' . ( $result['message'] ?? '' ) ) );
		qvc_clinic_order_notify( $order_id );
		$done( 'failed' );
	}

	update_post_meta( $order_id, 'qvc_authority', sanitize_text_field( $result['authority'] ) );
	wp_redirect( qvc_zarinpal_host() . '/pg/StartPay/' . rawurlencode( $result['authority'] ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Zarinpal gateway.
	exit;
}

/* ------------------------------------------------------------------------
 * Return from Zarinpal
 * --------------------------------------------------------------------- */
add_action( 'admin_post_nopriv_qvc_clinic_verify', 'qvc_clinic_order_verify' );
add_action( 'admin_post_qvc_clinic_verify', 'qvc_clinic_order_verify' );

function qvc_clinic_order_verify() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- gateway callback, checked against the stored authority and verified with Zarinpal.
	$order_id  = isset( $_GET['oid'] ) ? absint( $_GET['oid'] ) : 0;
	$authority = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : '';
	$ok        = isset( $_GET['Status'] ) && 'OK' === $_GET['Status'];
	// phpcs:enable

	if ( 'qv_clinic_order' !== get_post_type( $order_id ) || '' === $authority || ! hash_equals( (string) get_post_meta( $order_id, 'qvc_authority', true ), $authority ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$back = get_post_meta( $order_id, 'qvc_return', true );
	$back = $back ? $back : home_url( '/' );
	$done = function ( $status, $args = array() ) use ( $back ) {
		wp_safe_redirect( add_query_arg( array_merge( array( 'order' => $status ), $args ), $back ) . '#order' );
		exit;
	};

	if ( 'paid' === get_post_meta( $order_id, 'qvc_status', true ) ) {
		$done( 'paid', array( 'ref' => get_post_meta( $order_id, 'qvc_ref_id', true ) ) );
	}
	if ( ! $ok ) {
		update_post_meta( $order_id, 'qvc_status', 'failed' );
		$done( 'cancelled' );
	}

	$result = qvc_zarinpal_call(
		'verify',
		array(
			'merchant_id' => qvc_clinic_settings()['merchant_id'],
			'amount'      => (int) get_post_meta( $order_id, 'qvc_amount', true ) * 10,
			'authority'   => $authority,
		)
	);
	$code = (int) ( $result['code'] ?? 0 );

	if ( 100 === $code || 101 === $code ) {
		update_post_meta( $order_id, 'qvc_status', 'paid' );
		update_post_meta( $order_id, 'qvc_ref_id', sanitize_text_field( (string) ( $result['ref_id'] ?? '' ) ) );
		update_post_meta( $order_id, 'qvc_card_pan', sanitize_text_field( (string) ( $result['card_pan'] ?? '' ) ) );
		update_post_meta( $order_id, 'qvc_paid_at', gmdate( 'c' ) );
		if ( 100 === $code ) {
			qvc_clinic_order_notify( $order_id );
		}
		$done( 'paid', array( 'ref' => get_post_meta( $order_id, 'qvc_ref_id', true ) ) );
	}

	update_post_meta( $order_id, 'qvc_status', 'failed' );
	update_post_meta( $order_id, 'qvc_error', sanitize_text_field( $code . ' ' . ( $result['message'] ?? '' ) ) );
	$done( 'failed' );
}

/* ------------------------------------------------------------------------
 * Emails
 * --------------------------------------------------------------------- */
function qvc_clinic_order_notify( $order_id ) {
	$plans   = qvc_clinic_plans();
	$hosting = qvc_clinic_hosting_options();
	$labels  = qvc_clinic_status_labels();
	$get     = fn( $key ) => (string) get_post_meta( $order_id, $key, true );
	$status  = $get( 'qvc_status' );
	$amount  = (int) $get( 'qvc_amount' );

	$admin_body = implode(
		"\n",
		array(
			'Clinic: ' . get_the_title( $order_id ),
			'Contact: ' . $get( 'qvc_contact' ),
			'Mobile: ' . $get( 'qvc_mobile' ),
			'Email: ' . $get( 'qvc_email' ),
			'City: ' . $get( 'qvc_city' ),
			'Domain: ' . $get( 'qvc_domain' ),
			'Hosting: ' . ( $hosting[ $get( 'qvc_hosting' ) ] ?? '' ),
			'Plan: ' . ( $plans[ $get( 'qvc_plan' ) ]['title'] ?? '' ),
			'Amount: ' . ( $amount ? qvc_toman( $amount ) : '-' ),
			'Status: ' . ( $labels[ $status ] ?? $status ),
			'Zarinpal ref: ' . $get( 'qvc_ref_id' ),
			'',
			$get( 'qvc_notes' ),
			'',
			admin_url( 'post.php?post=' . $order_id . '&action=edit' ),
		)
	);
	$to = qvc_clinic_settings()['notify_email'];
	if ( $to ) {
		wp_mail( $to, sprintf( '[Clinic order #%d] %s — %s', $order_id, get_the_title( $order_id ), $labels[ $status ] ?? $status ), $admin_body );
	}

	$email = $get( 'qvc_email' );
	if ( $email && in_array( $status, array( 'paid', 'request' ), true ) ) {
		$lines = array(
			'سلام ' . $get( 'qvc_contact' ) . '،',
			'',
			'paid' === $status
				? 'پرداخت شما برای «' . ( $plans[ $get( 'qvc_plan' ) ]['title'] ?? '' ) . '» انجام شد. کد پیگیری: ' . $get( 'qvc_ref_id' )
				: 'درخواست شما برای نرم‌افزار کلینیک ثبت شد.',
			'به‌زودی برای هماهنگی نصب با شماره ' . $get( 'qvc_mobile' ) . ' تماس می‌گیریم.',
			'',
			'شماره سفارش: ' . $order_id,
			get_bloginfo( 'name' ) . ' — ' . home_url( '/' ),
		);
		wp_mail( $email, 'سفارش نرم‌افزار کلینیک #' . $order_id, implode( "\n", $lines ) );
	}
}
