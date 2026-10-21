<?php
/**
 * App orders: the Persian order form on an app's sales page (e.g. SafeJot),
 * payment through Zarinpal in toman, the order list in the dashboard and
 * delivery of the download link after payment.
 *
 * Orders are a private post type (App Orders). Zarinpal merchant ID and test
 * mode are shared with Clinic Orders → Settings. App Orders → Settings holds
 * each app's price (toman) and download link. Without a merchant ID or a
 * price, an order is taken as a request and followed up by phone.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

const QVC_APP_OPTION = 'qvc_app_settings';

/**
 * Products sold this way: slug => name, default prices (toman, and US
 * dollars shown on the English page) and, in Persian, what the buyer gets
 * after paying when there is no download link.
 */
function qvc_apps() {
	return array(
		'safejot'          => array(
			'name'    => 'SafeJot',
			'price'   => 1000000,
			'usd'     => 5,
			'deliver' => 'فایل نصب برنامه را به‌زودی برایتان می‌فرستیم.',
		),
		'aesthetic-clinic' => array(
			'name'    => 'Aesthetic Clinic Suite',
			'price'   => 30000000,
			'usd'     => 150,
			'deliver' => 'برای تحویل سورس کامل و هماهنگی نصب وب‌سایت روی سرور و دامنه شما به‌زودی تماس می‌گیریم.',
		),
	);
}

/**
 * What the buyer gets after paying when no download link is set (Persian).
 */
function qvc_app_deliver_text( $slug ) {
	return qvc_apps()[ $slug ]['deliver'] ?? 'فایل محصول را به‌زودی برایتان می‌فرستیم.';
}

/**
 * Settings with defaults: price_<slug> (toman), usd_<slug> (US dollars) and
 * link_<slug> (download link).
 */
function qvc_app_settings() {
	$defaults = array( 'notify_email' => get_option( 'admin_email' ) );
	foreach ( qvc_apps() as $slug => $app ) {
		$defaults[ 'price_' . $slug ] = (string) $app['price'];
		$defaults[ 'usd_' . $slug ]   = (string) ( $app['usd'] ?? '' );
		$defaults[ 'link_' . $slug ]  = '';
	}
	return wp_parse_args( (array) get_option( QVC_APP_OPTION, array() ), $defaults );
}

/**
 * Price of an app in toman (0 = request without payment).
 */
function qvc_app_price( $slug ) {
	return (int) ( qvc_app_settings()[ 'price_' . $slug ] ?? 0 );
}

/**
 * Price of an app in US dollars for the English page (0 = not shown).
 */
function qvc_app_price_usd( $slug ) {
	return (float) ( qvc_app_settings()[ 'usd_' . $slug ] ?? 0 );
}

/**
 * Whether the app can be paid online now.
 */
function qvc_app_payable( $slug ) {
	return qvc_app_price( $slug ) > 0 && '' !== qvc_clinic_settings()['merchant_id'];
}

/**
 * Link to the order's download, for the buyer (checked against a random key).
 */
function qvc_app_download_url( $order_id ) {
	$key = (string) get_post_meta( $order_id, 'qvc_key', true );
	$app = (string) get_post_meta( $order_id, 'qvc_app', true );
	if ( '' === $key || '' === (string) ( qvc_app_settings()[ 'link_' . $app ] ?? '' ) ) {
		return '';
	}
	return add_query_arg( array( 'action' => 'qvc_app_download', 'oid' => $order_id, 'k' => $key ), admin_url( 'admin-post.php' ) );
}

/**
 * Let administrators upload Android packages (.apk) to Media for the
 * download link. An APK is a zip file, so its detected type is accepted too.
 */
add_filter(
	'upload_mimes',
	function ( $mimes ) {
		if ( current_user_can( 'manage_options' ) ) {
			$mimes['apk'] = 'application/vnd.android.package-archive';
		}
		return $mimes;
	}
);

add_filter(
	'wp_check_filetype_and_ext',
	function ( $data, $file, $filename ) {
		if ( current_user_can( 'manage_options' ) && 'apk' === strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) ) ) {
			return array( 'ext' => 'apk', 'type' => 'application/vnd.android.package-archive', 'proper_filename' => false );
		}
		return $data;
	},
	10,
	3
);

/* ------------------------------------------------------------------------
 * Post type + dashboard list
 * --------------------------------------------------------------------- */
add_action(
	'init',
	function () {
		register_post_type(
			'qv_app_order',
			array(
				'labels'          => array(
					'name'          => __( 'App Orders', 'quantivista' ),
					'singular_name' => __( 'App Order', 'quantivista' ),
					'edit_item'     => __( 'App Order', 'quantivista' ),
					'search_items'  => __( 'Search orders', 'quantivista' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-smartphone',
				'menu_position'   => 58,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

add_filter(
	'manage_qv_app_order_posts_columns',
	function ( $columns ) {
		return array(
			'cb'         => $columns['cb'],
			'title'      => __( 'Buyer', 'quantivista' ),
			'qvc_app'    => __( 'App', 'quantivista' ),
			'qvc_mobile' => __( 'Mobile', 'quantivista' ),
			'qvc_amount' => __( 'Amount', 'quantivista' ),
			'qvc_status' => __( 'Status', 'quantivista' ),
			'date'       => $columns['date'],
		);
	}
);

add_action(
	'manage_qv_app_order_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'qvc_app':
				$app = get_post_meta( $post_id, 'qvc_app', true );
				echo esc_html( qvc_apps()[ $app ]['name'] ?? $app );
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
	'add_meta_boxes_qv_app_order',
	function () {
		add_meta_box(
			'qvc_app_order',
			__( 'Order details', 'quantivista' ),
			function ( $post ) {
				$labels = qvc_clinic_status_labels();
				$amount = (int) get_post_meta( $post->ID, 'qvc_amount', true );
				$rows   = array(
					__( 'App', 'quantivista' )            => qvc_apps()[ get_post_meta( $post->ID, 'qvc_app', true ) ]['name'] ?? '',
					__( 'Buyer', 'quantivista' )          => get_the_title( $post ),
					__( 'Mobile', 'quantivista' )         => get_post_meta( $post->ID, 'qvc_mobile', true ),
					__( 'Email', 'quantivista' )          => get_post_meta( $post->ID, 'qvc_email', true ),
					__( 'Amount', 'quantivista' )         => $amount ? qvc_toman( $amount ) : '—',
					__( 'Status', 'quantivista' )         => $labels[ get_post_meta( $post->ID, 'qvc_status', true ) ] ?? '',
					__( 'Zarinpal ref', 'quantivista' )   => get_post_meta( $post->ID, 'qvc_ref_id', true ),
					__( 'Paid with card', 'quantivista' ) => get_post_meta( $post->ID, 'qvc_card_pan', true ),
					__( 'Downloads', 'quantivista' )      => (int) get_post_meta( $post->ID, 'qvc_downloads', true ),
					__( 'Notes', 'quantivista' )          => get_post_meta( $post->ID, 'qvc_notes', true ),
				);
				echo '<table class="widefat striped" dir="auto"><tbody>';
				foreach ( $rows as $label => $value ) {
					printf( '<tr><th style="width:180px">%s</th><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( (string) $value ) ) );
				}
				echo '</tbody></table>';
			},
			'qv_app_order',
			'normal',
			'high'
		);
	}
);

/* ------------------------------------------------------------------------
 * Settings page (App Orders → Settings)
 * --------------------------------------------------------------------- */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=qv_app_order',
			__( 'App Orders Settings', 'quantivista' ),
			__( 'Settings', 'quantivista' ),
			'manage_options',
			'qvc-app-settings',
			'qvc_app_settings_page'
		);
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'qvc_app',
			QVC_APP_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => function ( $input ) {
					$input = (array) $input;
					$out   = array( 'notify_email' => sanitize_email( $input['notify_email'] ?? '' ) );
					foreach ( array_keys( qvc_apps() ) as $slug ) {
						$digits                = preg_replace( '/\D/', '', qvc_latin_digits( $input[ 'price_' . $slug ] ?? '' ) );
						$out[ 'price_' . $slug ] = '' === $digits ? '' : (string) (int) $digits;
						$usd                   = preg_replace( '/[^\d.]/', '', qvc_latin_digits( $input[ 'usd_' . $slug ] ?? '' ) );
						$out[ 'usd_' . $slug ]   = is_numeric( $usd ) ? (string) round( (float) $usd, 2 ) : '';
						$out[ 'link_' . $slug ]  = esc_url_raw( $input[ 'link_' . $slug ] ?? '' );
					}
					return $out;
				},
			)
		);
	}
);

function qvc_app_settings_page() {
	$s    = qvc_app_settings();
	$name = QVC_APP_OPTION;
	$row  = function ( $key, $label, $help = '', $type = 'text' ) use ( $s, $name ) {
		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%5$s" class="regular-text" id="%1$s" name="%3$s[%1$s]" value="%4$s" dir="ltr">%6$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $s[ $key ] ?? '' ),
			esc_attr( $type ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
		);
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'App Orders Settings', 'quantivista' ); ?></h1>
		<p><?php esc_html_e( 'Zarinpal merchant ID and test mode are set in Clinic Orders → Settings and used here too.', 'quantivista' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'qvc_app' ); ?>
			<table class="form-table" role="presentation">
				<?php
				foreach ( qvc_apps() as $slug => $app ) {
					/* translators: %s: app name */
					$row( 'price_' . $slug, sprintf( __( '%s: price (toman)', 'quantivista' ), $app['name'] ), __( 'Empty = "price on request".', 'quantivista' ) );
					/* translators: %s: app name */
					$row( 'usd_' . $slug, sprintf( __( '%s: price on the English page (US dollars)', 'quantivista' ), $app['name'] ), __( 'Shown to English visitors, who order through the contact page until a foreign payment gateway is added. Empty = the toman price is shown.', 'quantivista' ) );
					/* translators: %s: app name */
					$row( 'link_' . $slug, sprintf( __( '%s: download link', 'quantivista' ), $app['name'] ), __( 'The file (an APK or a zip uploaded in Media, or a store page). Buyers get it only after payment, through a personal link. Empty = you deliver it yourself.', 'quantivista' ), 'url' );
				}
				$row( 'notify_email', __( 'Send new orders to', 'quantivista' ), '', 'email' );
				?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------------
 * Form submission
 * --------------------------------------------------------------------- */
add_action( 'admin_post_nopriv_qvc_app_order', 'qvc_app_order_submit' );
add_action( 'admin_post_qvc_app_order', 'qvc_app_order_submit' );

function qvc_app_order_submit() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'order', 'ref', 'oid', 'k' ), $back );
	$done = function ( $status, $args = array() ) use ( $back ) {
		wp_safe_redirect( add_query_arg( array_merge( array( 'order' => $status ), $args ), $back ) . '#order' );
		exit;
	};

	if ( ! isset( $_POST['qvc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qvc_nonce'] ) ), 'qvc_app_order' ) ) {
		$done( 'invalid' );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot.
		$done( 'received' );
	}

	$ip_hash = substr( hash_hmac( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '', wp_salt( 'nonce' ) ), 0, 20 );
	$hits    = (int) get_transient( 'qvc_ao_' . $ip_hash );
	if ( $hits >= 5 ) {
		$done( 'busy' );
	}
	set_transient( 'qvc_ao_' . $ip_hash, $hits + 1, HOUR_IN_SECONDS );

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
	$text   = fn( $key, $max = 120 ) => isset( $_POST[ $key ] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), 0, $max ) : '';
	$app    = sanitize_key( $text( 'app', 30 ) );
	$buyer  = $text( 'buyer' );
	$mobile = preg_replace( '/\D/', '', qvc_latin_digits( $text( 'mobile', 20 ) ) );
	$email  = sanitize_email( $text( 'email' ) );
	$notes  = isset( $_POST['notes'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ), 0, 1000 ) : '';
	$consent = ! empty( $_POST['consent'] );
	// phpcs:enable

	if ( ! isset( qvc_apps()[ $app ] ) || '' === $buyer || ! preg_match( '/^09\d{9}$/', $mobile ) || ! is_email( $email ) || ! $consent ) {
		$done( 'invalid' );
	}

	$amount  = qvc_app_price( $app );
	$payable = qvc_app_payable( $app );

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'qv_app_order',
			'post_status' => 'private',
			'post_title'  => $buyer,
		)
	);
	if ( ! $order_id || is_wp_error( $order_id ) ) {
		$done( 'error' );
	}
	$meta = array(
		'qvc_app'    => $app,
		'qvc_mobile' => $mobile,
		'qvc_email'  => $email,
		'qvc_notes'  => $notes,
		'qvc_amount' => $payable ? $amount : 0,
		'qvc_status' => $payable ? 'pending' : 'request',
		'qvc_key'    => wp_generate_password( 32, false ),
		'qvc_return' => esc_url_raw( $back ),
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $order_id, $key, $value );
	}

	if ( ! $payable ) {
		qvc_app_order_notify( $order_id );
		$done( 'received' );
	}

	$settings = qvc_clinic_settings();
	$result   = qvc_zarinpal_call(
		'request',
		array(
			'merchant_id'  => $settings['merchant_id'],
			'amount'       => $amount * 10,
			'callback_url' => add_query_arg( array( 'action' => 'qvc_app_verify', 'oid' => $order_id ), admin_url( 'admin-post.php' ) ),
			'description'  => 'خرید ' . qvc_apps()[ $app ]['name'] . ' - سفارش #' . $order_id,
			'metadata'     => array( 'mobile' => $mobile, 'email' => $email, 'order_id' => (string) $order_id ),
		)
	);

	if ( 100 !== (int) ( $result['code'] ?? 0 ) || empty( $result['authority'] ) ) {
		update_post_meta( $order_id, 'qvc_status', 'failed' );
		update_post_meta( $order_id, 'qvc_error', sanitize_text_field( ( $result['code'] ?? '' ) . ' ' . ( $result['message'] ?? '' ) ) );
		qvc_app_order_notify( $order_id );
		$done( 'failed' );
	}

	update_post_meta( $order_id, 'qvc_authority', sanitize_text_field( $result['authority'] ) );
	wp_redirect( qvc_zarinpal_host() . '/pg/StartPay/' . rawurlencode( $result['authority'] ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Zarinpal gateway.
	exit;
}

/* ------------------------------------------------------------------------
 * Return from Zarinpal
 * --------------------------------------------------------------------- */
add_action( 'admin_post_nopriv_qvc_app_verify', 'qvc_app_order_verify' );
add_action( 'admin_post_qvc_app_verify', 'qvc_app_order_verify' );

function qvc_app_order_verify() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- gateway callback, checked against the stored authority and verified with Zarinpal.
	$order_id  = isset( $_GET['oid'] ) ? absint( $_GET['oid'] ) : 0;
	$authority = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : '';
	$ok        = isset( $_GET['Status'] ) && 'OK' === $_GET['Status'];
	// phpcs:enable

	if ( 'qv_app_order' !== get_post_type( $order_id ) || '' === $authority || ! hash_equals( (string) get_post_meta( $order_id, 'qvc_authority', true ), $authority ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$back = get_post_meta( $order_id, 'qvc_return', true );
	$back = $back ? $back : home_url( '/' );
	$paid = function () use ( $back, $order_id ) {
		$args = array(
			'order' => 'paid',
			'ref'   => get_post_meta( $order_id, 'qvc_ref_id', true ),
			'oid'   => $order_id,
			'k'     => get_post_meta( $order_id, 'qvc_key', true ),
		);
		wp_safe_redirect( add_query_arg( $args, $back ) . '#order' );
		exit;
	};
	$done = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'order', $status, $back ) . '#order' );
		exit;
	};

	if ( 'paid' === get_post_meta( $order_id, 'qvc_status', true ) ) {
		$paid();
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
			qvc_app_order_notify( $order_id );
		}
		$paid();
	}

	update_post_meta( $order_id, 'qvc_status', 'failed' );
	update_post_meta( $order_id, 'qvc_error', sanitize_text_field( $code . ' ' . ( $result['message'] ?? '' ) ) );
	$done( 'failed' );
}

/**
 * Paid order matching the id and key from a buyer's link, or 0.
 */
function qvc_app_paid_order( $order_id, $key ) {
	$order_id = absint( $order_id );
	if ( 'qv_app_order' !== get_post_type( $order_id ) || 'paid' !== get_post_meta( $order_id, 'qvc_status', true ) ) {
		return 0;
	}
	$stored = (string) get_post_meta( $order_id, 'qvc_key', true );
	return '' !== $stored && hash_equals( $stored, (string) $key ) ? $order_id : 0;
}

/* ------------------------------------------------------------------------
 * Download (personal link, paid orders only)
 * --------------------------------------------------------------------- */
add_action( 'admin_post_nopriv_qvc_app_download', 'qvc_app_download' );
add_action( 'admin_post_qvc_app_download', 'qvc_app_download' );

function qvc_app_download() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- personal link checked against the order's random key.
	$order_id = qvc_app_paid_order( $_GET['oid'] ?? 0, isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '' );
	// phpcs:enable
	$link = $order_id ? (string) ( qvc_app_settings()[ 'link_' . get_post_meta( $order_id, 'qvc_app', true ) ] ?? '' ) : '';
	if ( '' === $link ) {
		wp_die( esc_html( 'این لینک معتبر نیست. اگر خرید کرده‌اید، با ما تماس بگیرید.' ), '', array( 'response' => 403 ) );
	}
	$count = (int) get_post_meta( $order_id, 'qvc_downloads', true );
	if ( $count >= 20 ) {
		wp_die( esc_html( 'تعداد دفعات دانلود این لینک تمام شده است. لطفاً با ما تماس بگیرید.' ), '', array( 'response' => 403 ) );
	}
	update_post_meta( $order_id, 'qvc_downloads', $count + 1 );
	wp_redirect( $link ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- set by the site admin (media file or store page).
	exit;
}

/* ------------------------------------------------------------------------
 * Emails
 * --------------------------------------------------------------------- */
function qvc_app_order_notify( $order_id ) {
	$labels = qvc_clinic_status_labels();
	$get    = fn( $key ) => (string) get_post_meta( $order_id, $key, true );
	$status = $get( 'qvc_status' );
	$amount = (int) $get( 'qvc_amount' );
	$name   = qvc_apps()[ $get( 'qvc_app' ) ]['name'] ?? $get( 'qvc_app' );

	$to = qvc_app_settings()['notify_email'];
	if ( $to ) {
		$body = implode(
			"\n",
			array(
				'App: ' . $name,
				'Buyer: ' . get_the_title( $order_id ),
				'Mobile: ' . $get( 'qvc_mobile' ),
				'Email: ' . $get( 'qvc_email' ),
				'Amount: ' . ( $amount ? qvc_toman( $amount ) : '-' ),
				'Status: ' . ( $labels[ $status ] ?? $status ),
				'Zarinpal ref: ' . $get( 'qvc_ref_id' ),
				'',
				$get( 'qvc_notes' ),
				'',
				admin_url( 'post.php?post=' . $order_id . '&action=edit' ),
			)
		);
		wp_mail( $to, sprintf( '[App order #%d] %s — %s', $order_id, $name, $labels[ $status ] ?? $status ), $body );
	}

	$email = $get( 'qvc_email' );
	if ( ! $email || ! in_array( $status, array( 'paid', 'request' ), true ) ) {
		return;
	}
	$download = 'paid' === $status ? qvc_app_download_url( $order_id ) : '';
	$lines    = array( 'سلام ' . get_the_title( $order_id ) . '،', '' );
	if ( 'paid' === $status ) {
		$lines[] = 'پرداخت شما برای ' . $name . ' انجام شد. کد پیگیری: ' . $get( 'qvc_ref_id' );
		$lines[] = $download
			? 'لینک دانلود شخصی شما (آن را با دیگران به اشتراک نگذارید): ' . $download
			: qvc_app_deliver_text( $get( 'qvc_app' ) );
	} else {
		$lines[] = 'درخواست شما برای خرید ' . $name . ' ثبت شد. به‌زودی با شماره ' . $get( 'qvc_mobile' ) . ' تماس می‌گیریم.';
	}
	$lines[] = '';
	$lines[] = 'شماره سفارش: ' . $order_id;
	$lines[] = get_bloginfo( 'name' ) . ' — ' . home_url( '/' );
	wp_mail( $email, 'سفارش ' . $name . ' #' . $order_id, implode( "\n", $lines ) );
}
