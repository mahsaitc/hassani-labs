<?php
/**
 * Persian order form for an app (posts to App Orders).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qvc_app_payable' ) ) {
	return;
}

$qv_app  = sanitize_key( $args['app'] ?? '' );
$qv_name = $args['name'] ?? '';
// Wording for a phone app unless the product's texts (inc/apps.php) say otherwise.
$qv_text = wp_parse_args(
	(array) ( $args['texts'] ?? array() ),
	array(
		'order_lead'    => 'لینک دانلود و رسید خرید به ایمیل شما فرستاده می‌شود؛ ایمیل را درست وارد کنید.',
		'order_notes'   => 'مدل گوشی یا هر نکته‌ای که لازم است بدانیم',
		'order_consent' => 'موافقم اطلاعات این فرم برای ثبت سفارش، ارسال برنامه و پشتیبانی استفاده شود.',
		'order_pay'     => 'و دریافت برنامه',
	)
);

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display-only status flags; the download link is checked against the order key.
$qv_status = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : '';
$qv_ref    = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
$qv_order  = 'paid' === $qv_status && isset( $_GET['oid'], $_GET['k'] ) ? qvc_app_paid_order( absint( $_GET['oid'] ), sanitize_text_field( wp_unslash( $_GET['k'] ) ) ) : 0;
// phpcs:enable
$qv_download = $qv_order ? qvc_app_download_url( $qv_order ) : '';

$qv_messages = array(
	'paid'      => array( 'ok', 'پرداخت با موفقیت انجام شد. کد پیگیری زرین‌پال: ' . $qv_ref . '. ' . ( $qv_download ? 'لینک دانلود را پایین همین پیام و در ایمیل خود می‌بینید.' : qvc_app_deliver_text( $qv_app ) ) ),
	'received'  => array( 'ok', 'درخواست شما ثبت شد. به‌زودی برای هماهنگی خرید با شما تماس می‌گیریم.' ),
	'cancelled' => array( 'error', 'پرداخت انجام نشد. اگر مبلغی از حساب شما کم شده باشد، طی ۷۲ ساعت برمی‌گردد. می‌توانید دوباره سفارش دهید.' ),
	'failed'    => array( 'error', 'اتصال به درگاه پرداخت ممکن نشد. لطفاً کمی بعد دوباره امتحان کنید یا با ما تماس بگیرید.' ),
	'invalid'   => array( 'error', 'لطفاً همه فیلدهای ستاره‌دار را درست پر کنید. شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' ),
	'busy'      => array( 'error', 'تعداد درخواست‌ها زیاد بود. لطفاً یک ساعت دیگر دوباره امتحان کنید.' ),
	'error'     => array( 'error', 'خطایی پیش آمد. لطفاً دوباره امتحان کنید.' ),
);
$qv_payable = qvc_app_payable( $qv_app );
?>
<div class="form-card clinic-order">
	<h2 id="app-order">خرید <?php echo esc_html( $qv_name ); ?></h2>
	<p class="muted"><?php echo esc_html( $qv_text['order_lead'] ); ?></p>

	<?php if ( isset( $qv_messages[ $qv_status ] ) ) : ?>
		<div class="clinic-alert clinic-alert--<?php echo esc_attr( $qv_messages[ $qv_status ][0] ); ?>" role="status"><?php echo esc_html( $qv_messages[ $qv_status ][1] ); ?></div>
		<?php if ( $qv_download ) : ?>
			<p><a class="btn btn--accent btn--lg btn--block" href="<?php echo esc_url( $qv_download ); ?>" rel="nofollow"><?php qv_the_icon( 'download', array( 'size' => 18 ) ); ?> دانلود <?php echo esc_html( $qv_name ); ?></a></p>
		<?php endif; ?>
	<?php endif; ?>

	<form class="form-grid" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="qvc_app_order">
		<input type="hidden" name="app" value="<?php echo esc_attr( $qv_app ); ?>">
		<?php wp_nonce_field( 'qvc_app_order', 'qvc_nonce' ); ?>
		<p class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

		<p><label>نام و نام خانوادگی *<input type="text" name="buyer" required maxlength="120" autocomplete="name"></label></p>
		<p><label>موبایل *<input type="tel" name="mobile" required inputmode="numeric" pattern="[0-9۰-۹]{11}" placeholder="09121234567" dir="ltr" autocomplete="tel"></label></p>
		<p class="full"><label>ایمیل *<input type="email" name="email" required maxlength="120" dir="ltr" autocomplete="email"></label></p>
		<p class="full"><label>توضیحات<textarea name="notes" rows="2" maxlength="1000" placeholder="<?php echo esc_attr( $qv_text['order_notes'] ); ?>"></textarea></label></p>

		<p class="full"><label class="clinic-order__consent"><input type="checkbox" name="consent" value="1" required> <span><?php echo esc_html( $qv_text['order_consent'] ); ?></span></label></p>

		<p class="full">
			<button type="submit" class="btn btn--accent btn--lg btn--block">
				<?php echo esc_html( $qv_payable ? 'پرداخت ' . qv_app_price_text( $qv_app, 'fa' ) . ' ' . $qv_text['order_pay'] : 'ثبت درخواست خرید' ); ?>
			</button>
		</p>
		<?php if ( $qv_payable ) : ?>
			<p class="full muted clinic-order__secure"><?php qv_the_icon( 'lock', array( 'size' => 16 ) ); ?> پرداخت امن از درگاه زرین‌پال</p>
		<?php endif; ?>
	</form>
</div>
