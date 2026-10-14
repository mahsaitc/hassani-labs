<?php
/**
 * App sales pages (Persian and English), e.g. SafeJot: texts, screenshots
 * and assets. Orders are handled in mu-plugins/quantivista-core/app-orders.php.
 *
 * Screenshots: assets/img/apps/<app>-<shot>.webp (phone screens, 9:19.5);
 * a missing file is skipped, so the page works before they are added.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

const QV_APP_TEMPLATES = array( 'page-templates/app-safejot.php', 'page-templates/app-safejot-en.php' );

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_page_template( QV_APP_TEMPLATES ) ) {
			return;
		}
		wp_enqueue_style( 'quantivista-clinic', QV_URI . '/assets/css/clinic-software.css', array( 'quantivista' ), qv_asset_version( 'assets/css/clinic-software.css' ) );
		wp_enqueue_style( 'quantivista-apps', QV_URI . '/assets/css/apps.css', array( 'quantivista-clinic' ), qv_asset_version( 'assets/css/apps.css' ) );
	}
);

/**
 * Screenshots that exist: array of array( url, caption ).
 */
function qv_app_shots( $app, $lang ) {
	$shots = array();
	foreach ( qv_app_strings( $app, $lang )['shots'] as $shot => $caption ) {
		$file = "assets/img/apps/{$app}-{$shot}.webp";
		if ( file_exists( QV_DIR . '/' . $file ) ) {
			$shots[] = array( QV_URI . '/' . $file, $caption );
		}
	}
	return $shots;
}

/**
 * Price line for the page: toman from App Orders settings.
 */
function qv_app_price_text( $app, $lang ) {
	$price = function_exists( 'qvc_app_price' ) ? qvc_app_price( $app ) : 0;
	if ( $price <= 0 ) {
		return 'en' === $lang ? 'Price on request' : 'استعلام قیمت';
	}
	if ( 'en' === $lang ) {
		return number_format( $price ) . ' toman';
	}
	return strtr( number_format( $price ), array_combine( array_merge( range( 0, 9 ), array( ',' ) ), array_merge( mb_str_split( '۰۱۲۳۴۵۶۷۸۹' ), array( '٬' ) ) ) ) . ' تومان';
}

/**
 * Page texts.
 */
function qv_app_strings( $app, $lang ) {
	$texts = array(
		'safejot' => array(
			'fa' => array(
				'name'          => 'SafeJot',
				'eyebrow'       => 'اپلیکیشن اندروید · حریم خصوصی',
				'title'         => 'SafeJot؛ یادداشت‌هایتان، فقط برای چشم خودتان',
				'lead'          => 'متن را بنویسید یا بچسبانید و با یک لمس آن را به متنی رمزی و ناخوانا تبدیل کنید؛ بعد هر جا خواستید نگهش دارید یا بفرستید. برای خواندن دوباره، کافی است متن رمزی را در SafeJot برگردانید. همه‌چیز روی خود گوشی انجام می‌شود: بدون اینترنت، بدون حساب کاربری و بدون ذخیره هیچ متنی.',
				'buy'           => 'خرید SafeJot',
				'more'          => 'امکانات',
				'points'        => array( 'کاملاً آفلاین', 'بدون ثبت‌نام و تبلیغات', 'هیچ متنی ذخیره نمی‌شود' ),
				'icon_alt'      => 'آیکون اپلیکیشن SafeJot',
				'features_eye'  => 'امکانات',
				'features_title' => 'ساده، سریع و خصوصی',
				'features'      => array(
					array( 'lock', 'رمز کردن با یک لمس', 'متن را در کادر بنویسید و دکمه Encrypt را بزنید؛ نسخه رمزی بلافاصله آماده کپی است.' ),
					array( 'refresh', 'برگرداندن متن اصلی', 'متن رمزی را در بخش Decrypt بچسبانید تا متن اصلی، با همان حروف بزرگ و کوچک، برگردد.' ),
					array( 'shield', 'بدون اینترنت', 'برنامه مجوز دسترسی به اینترنت ندارد؛ متن شما هیچ‌وقت از گوشی بیرون نمی‌رود.' ),
					array( 'eye', 'بدون ذخیره‌سازی', 'SafeJot هیچ متنی را نگه نمی‌دارد و با بستن برنامه چیزی از متن‌های شما باقی نمی‌ماند.' ),
					array( 'layers', 'واژه‌نامه ۴۰۰ هزار کلمه‌ای', 'هر کلمه انگلیسی با کلمه رمزی ثابت خودش جایگزین می‌شود و علائم و ساختار جمله حفظ می‌شود.' ),
					array( 'file-text', 'کپی سریع', 'نتیجه با یک لمس کپی می‌شود تا در پیام‌رسان، ایمیل یا برنامه یادداشت بچسبانید.' ),
					array( 'smartphone', 'رابط ساده', 'دو صفحه و چند دکمه؛ بدون آموزش و از همان دقیقه اول قابل استفاده است.' ),
					array( 'zap', 'سبک و سریع', 'حجم کم و اجرای بی‌معطلی، روی گوشی‌های اندروید ۵ و بالاتر.' ),
				),
				'shots_eye'     => 'تصاویر برنامه',
				'shots_title'   => 'نگاهی به SafeJot',
				'shots'         => array(
					'home'    => 'صفحه اصلی: انتخاب رمز کردن یا برگرداندن متن',
					'encrypt' => 'رمز کردن متن و کپی نتیجه',
					'decrypt' => 'برگرداندن متن رمزی به متن اصلی',
				),
				'steps_eye'     => 'نحوه کار',
				'steps_title'   => 'در سه قدم',
				'steps'         => array(
					array( 'بنویسید', 'متن یا یادداشت خود را در بخش Encrypt بنویسید یا بچسبانید.' ),
					array( 'رمز کنید', 'دکمه Encrypt را بزنید و متن رمزی را کپی کنید.' ),
					array( 'نگه دارید یا بفرستید', 'متن رمزی را هر جا خواستید ذخیره کنید؛ هر وقت لازم شد، با Decrypt آن را برگردانید.' ),
				),
				'plan_eye'      => 'قیمت',
				'plan_title'    => 'یک بار بخرید، همیشه استفاده کنید',
				'plan_name'     => 'لایسنس SafeJot برای اندروید',
				'plan_lead'     => 'پرداخت یک‌باره، بدون اشتراک ماهانه',
				'plan_features' => array( 'فایل نصب اندروید (APK)', 'استفاده نامحدود روی گوشی خودتان', 'لینک دانلود شخصی بلافاصله بعد از پرداخت', 'راهنمایی برای نصب' ),
				'faq_eye'       => 'پرسش‌های رایج',
				'faq_title'     => 'قبل از خرید بدانید',
				'faq'           => array(
					array( 'SafeJot متن را چطور رمز می‌کند؟', 'هر کلمه انگلیسی با یک کلمه رمزی ثابت از واژه‌نامه‌ای با بیش از ۴۰۰ هزار کلمه جایگزین می‌شود و کلمه‌هایی که در واژه‌نامه نیستند، وارونه نوشته می‌شوند. علائم، فاصله‌ها و حروف بزرگ و کوچک حفظ می‌شوند.' ),
					array( 'متن فارسی را هم رمز می‌کند؟', 'واژه‌نامه برنامه انگلیسی است؛ کلمه‌های فارسی فقط وارونه نوشته می‌شوند. بهترین نتیجه با متن انگلیسی یا فینگلیش به دست می‌آید. رابط برنامه هم انگلیسی است.' ),
					array( 'برای چه اطلاعاتی مناسب است؟', 'برای دور نگه داشتن یادداشت‌ها و پیام‌های روزمره از چشم دیگران. SafeJot رمز عبور ندارد و هر کسی که SafeJot داشته باشد می‌تواند متن را برگرداند؛ برای اطلاعات بسیار حساس مثل رمزهای بانکی، از ابزارهای رمزنگاری استاندارد استفاده کنید.' ),
					array( 'به اینترنت نیاز دارد؟', 'نه. برنامه مجوز اینترنت ندارد و همه کارها روی خود گوشی انجام می‌شود.' ),
					array( 'بعد از پرداخت، برنامه را چطور دریافت می‌کنم؟', 'بعد از پرداخت موفق، لینک دانلود شخصی در همین صفحه نمایش داده و به ایمیل شما فرستاده می‌شود. اگر مشکلی پیش آمد، فایل را از طریق پشتیبانی برایتان می‌فرستیم.' ),
					array( 'روی آیفون هم کار می‌کند؟', 'این نسخه برای گوشی‌ها و تبلت‌های اندروید است.' ),
					array( 'پرداخت امن است؟', 'پرداخت از درگاه زرین‌پال انجام می‌شود و اطلاعات کارت شما به سایت ما نمی‌رسد. بعد از پرداخت، کد پیگیری به شما نمایش داده می‌شود.' ),
				),
			),
			'en' => array(
				'name'          => 'SafeJot',
				'eyebrow'       => 'Android app · Privacy',
				'title'         => 'SafeJot: your notes, for your eyes only',
				'lead'          => 'Type or paste a text and turn it into unreadable coded text with one tap, then keep it or send it anywhere. To read it again, paste it back into SafeJot. Everything happens on your phone: no internet, no account and nothing stored.',
				'buy'           => 'Buy SafeJot',
				'more'          => 'Features',
				'points'        => array( 'Works fully offline', 'No sign-up, no ads', 'Nothing is stored' ),
				'icon_alt'      => 'SafeJot app icon',
				'features_eye'  => 'Features',
				'features_title' => 'Simple, fast and private',
				'features'      => array(
					array( 'lock', 'One-tap encoding', 'Type your text and tap Encrypt; the coded version is ready to copy right away.' ),
					array( 'refresh', 'Get the original back', 'Paste coded text into Decrypt to restore the original, with its capital letters.' ),
					array( 'shield', 'No internet', 'The app has no internet permission, so your text never leaves the phone.' ),
					array( 'eye', 'Nothing stored', 'SafeJot keeps no text; close the app and nothing of yours is left behind.' ),
					array( 'layers', '400,000-word dictionary', 'Each English word is replaced by its own fixed code word, keeping punctuation and sentence shape.' ),
					array( 'file-text', 'Quick copy', 'Copy the result with one tap and paste it into a messenger, email or notes app.' ),
					array( 'smartphone', 'Simple interface', 'Two screens and a few buttons: usable from the first minute, no training needed.' ),
					array( 'zap', 'Light and fast', 'Small download and instant results, on Android 5.0 and newer.' ),
				),
				'shots_eye'     => 'Screenshots',
				'shots_title'   => 'A look at SafeJot',
				'shots'         => array(
					'home'    => 'Home screen: choose to encode or decode',
					'encrypt' => 'Encoding a text and copying the result',
					'decrypt' => 'Turning coded text back into the original',
				),
				'steps_eye'     => 'How it works',
				'steps_title'   => 'Three steps',
				'steps'         => array(
					array( 'Write', 'Type or paste your text or note into Encrypt.' ),
					array( 'Encode', 'Tap Encrypt and copy the coded text.' ),
					array( 'Keep or send', 'Store the coded text anywhere; decode it with Decrypt whenever you need it.' ),
				),
				'plan_eye'      => 'Price',
				'plan_title'    => 'Buy once, use it for good',
				'plan_name'     => 'SafeJot licence for Android',
				'plan_lead'     => 'One-time payment, no subscription',
				'plan_features' => array( 'Android install file (APK)', 'Unlimited use on your own phone', 'Personal download link right after payment', 'Help with installation' ),
				'buy_note'      => 'Online payment is in toman with an Iranian bank card (Zarinpal). Buying from outside Iran? Contact us.',
				'buy_fa'        => 'Pay with an Iranian card',
				'contact'       => 'Contact us',
				'faq_eye'       => 'FAQ',
				'faq_title'     => 'Good to know before you buy',
				'faq'           => array(
					array( 'How does SafeJot encode text?', 'Each English word is replaced by a fixed code word from a dictionary of more than 400,000 words; words that are not in the dictionary are written backwards. Punctuation, spaces and capital letters are kept.' ),
					array( 'What is it good for?', 'Keeping everyday notes and messages away from other people\'s eyes. SafeJot has no password, and anyone with SafeJot can decode the text, so use standard encryption tools for highly sensitive data such as bank passwords.' ),
					array( 'Does it need the internet?', 'No. The app has no internet permission and does everything on the phone.' ),
					array( 'How do I get the app after paying?', 'After a successful payment, a personal download link is shown on the page and emailed to you. If anything goes wrong, we send you the file through support.' ),
					array( 'Does it work on iPhone?', 'This version is for Android phones and tablets.' ),
				),
			),
		),
	);
	return $texts[ $app ][ 'en' === $lang ? 'en' : 'fa' ];
}
