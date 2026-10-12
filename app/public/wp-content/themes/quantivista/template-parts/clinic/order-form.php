<?php
/**
 * Persian order form for the clinic software (posts to Clinic Orders).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qvc_clinic_plans' ) ) {
	return;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display-only status flags.
$qv_status = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : '';
$qv_ref    = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
// phpcs:enable

$qv_messages = array(
	'paid'      => array( 'ok', 'پرداخت با موفقیت انجام شد. کد پیگیری زرین‌پال: ' . $qv_ref . '. به‌زودی برای هماهنگی نصب با شما تماس می‌گیریم.' ),
	'received'  => array( 'ok', 'درخواست شما ثبت شد. به‌زودی برای هماهنگی و اعلام هزینه با شما تماس می‌گیریم.' ),
	'cancelled' => array( 'error', 'پرداخت انجام نشد. اگر مبلغی از حساب شما کم شده باشد، طی ۷۲ ساعت برمی‌گردد. می‌توانید دوباره سفارش دهید.' ),
	'failed'    => array( 'error', 'اتصال به درگاه پرداخت ممکن نشد. لطفاً کمی بعد دوباره امتحان کنید یا با ما تماس بگیرید.' ),
	'invalid'   => array( 'error', 'لطفاً همه فیلدهای ستاره‌دار را درست پر کنید. شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' ),
	'busy'      => array( 'error', 'تعداد درخواست‌ها زیاد بود. لطفاً یک ساعت دیگر دوباره امتحان کنید.' ),
	'error'     => array( 'error', 'خطایی پیش آمد. لطفاً دوباره امتحان کنید.' ),
);
$qv_settings = qvc_clinic_settings();
?>
<div class="form-card clinic-order">
	<h2 id="clinic-order">فرم سفارش</h2>
	<p class="muted">فیلدهای ستاره‌دار لازم هستند. اطلاعات ورود به هاست را اینجا ننویسید؛ بعد از تماس، از راه امن هماهنگ می‌کنیم.</p>

	<?php if ( isset( $qv_messages[ $qv_status ] ) ) : ?>
		<div class="clinic-alert clinic-alert--<?php echo esc_attr( $qv_messages[ $qv_status ][0] ); ?>" role="status"><?php echo esc_html( $qv_messages[ $qv_status ][1] ); ?></div>
	<?php endif; ?>

	<form class="form-grid" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="qvc_clinic_order">
		<?php wp_nonce_field( 'qvc_clinic_order', 'qvc_nonce' ); ?>
		<p class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

		<p><label>نام کلینیک *<input type="text" name="clinic" required maxlength="120" autocomplete="organization"></label></p>
		<p><label>نام پزشک یا مسئول *<input type="text" name="contact" required maxlength="120" autocomplete="name"></label></p>
		<p><label>موبایل *<input type="tel" name="mobile" required inputmode="numeric" pattern="[0-9۰-۹]{11}" placeholder="09121234567" dir="ltr" autocomplete="tel"></label></p>
		<p><label>ایمیل<input type="email" name="email" maxlength="120" dir="ltr" autocomplete="email"></label></p>
		<p><label>شهر<input type="text" name="city" maxlength="60" autocomplete="address-level2"></label></p>
		<p><label>دامنه (اگر دارید)<input type="text" name="domain" maxlength="120" placeholder="example.ir" dir="ltr"></label></p>

		<p class="full"><label>هاست *
			<select name="hosting" required>
				<option value="">انتخاب کنید</option>
				<?php foreach ( qvc_clinic_hosting_options() as $qv_key => $qv_label ) : ?>
					<option value="<?php echo esc_attr( $qv_key ); ?>"><?php echo esc_html( $qv_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label></p>

		<fieldset class="full clinic-order__plans">
			<legend>پلن *</legend>
			<?php foreach ( qvc_clinic_plans() as $qv_key => $qv_plan ) : ?>
				<label class="clinic-order__plan">
					<input type="radio" name="plan" value="<?php echo esc_attr( $qv_key ); ?>" required <?php checked( ! empty( $qv_plan['popular'] ) ); ?>>
					<span><strong><?php echo esc_html( $qv_plan['title'] ); ?></strong>
					<small><?php echo esc_html( $qv_plan['price'] > 0 ? qvc_toman( $qv_plan['price'] ) : 'استعلام قیمت' ); ?></small></span>
				</label>
			<?php endforeach; ?>
		</fieldset>

		<p class="full"><label>توضیحات<textarea name="notes" rows="3" maxlength="1000" placeholder="تعداد پزشکان، زمان مناسب برای تماس، …"></textarea></label></p>

		<p class="full"><label class="clinic-order__consent"><input type="checkbox" name="consent" value="1" required> <span>موافقم اطلاعات این فرم برای پیگیری سفارش و تماس با من استفاده شود.</span></label></p>

		<p class="full">
			<button type="submit" class="btn btn--accent btn--lg btn--block">
				<?php echo esc_html( '' !== $qv_settings['merchant_id'] ? 'ثبت سفارش و پرداخت' : 'ثبت درخواست' ); ?>
			</button>
		</p>
		<?php if ( '' !== $qv_settings['merchant_id'] ) : ?>
			<p class="full muted clinic-order__secure"><?php qv_the_icon( 'lock', array( 'size' => 16 ) ); ?> پرداخت امن از درگاه زرین‌پال. برای پلن‌های «استعلام قیمت» پرداختی انجام نمی‌شود و با شما تماس می‌گیریم.</p>
		<?php endif; ?>
	</form>
</div>
