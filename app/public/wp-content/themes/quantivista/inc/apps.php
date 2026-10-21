<?php
/**
 * Product sales pages (Persian and English): SafeJot (Android app),
 * Aesthetic Clinic Suite and the acupuncture clinic software (websites). Texts, screenshots and assets. Orders are
 * handled in mu-plugins/quantivista-core/app-orders.php.
 *
 * Screenshots: assets/img/apps/<app>-<shot>.webp, English ones with an -en
 * suffix; a missing file is skipped. Phone screens are 540x1170 (SafeJot's are
 * rendered by its repo, test_shots/shots_test.dart); a product with
 * 'shots_kind' => 'web' has browser screens, 1280x800 (the clinic suite's are
 * taken from its site and panel with sample data).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/** Sales page templates and the product each one shows. */
const QV_APP_PAGES = array(
	'page-templates/app-safejot.php'             => 'safejot',
	'page-templates/app-safejot-en.php'          => 'safejot',
	'page-templates/app-aesthetic-clinic.php'    => 'aesthetic-clinic',
	'page-templates/app-aesthetic-clinic-en.php' => 'aesthetic-clinic',
	// The acupuncture clinic software keeps its original addresses (/clinic-software-fa/, /clinic-software/).
	'page-templates/clinic-software.php'         => 'acupuncture-clinic',
	'page-templates/clinic-software-en.php'      => 'acupuncture-clinic',
);
define( 'QV_APP_TEMPLATES', array_keys( QV_APP_PAGES ) );

/**
 * The product shown on the current page, or '' when it is not a sales page.
 */
function qv_current_app() {
	foreach ( QV_APP_PAGES as $template => $app ) {
		if ( is_page_template( $template ) ) {
			return $app;
		}
	}
	return '';
}

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
		$en   = "assets/img/apps/{$app}-{$shot}-en.webp";
		if ( 'en' === $lang && file_exists( QV_DIR . '/' . $en ) ) {
			$file = $en;
		}
		if ( file_exists( QV_DIR . '/' . $file ) ) {
			$shots[] = array( QV_URI . '/' . $file, $caption );
		}
	}
	return $shots;
}

/**
 * Price line for the page from App Orders settings: US dollars on the
 * English page when set, otherwise toman.
 */
function qv_app_price_text( $app, $lang ) {
	$usd = 'en' === $lang && function_exists( 'qvc_app_price_usd' ) ? qvc_app_price_usd( $app ) : 0;
	if ( $usd > 0 ) {
		return '$' . ( floor( $usd ) == $usd ? number_format( $usd ) : number_format( $usd, 2 ) );
	}
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
				'eyebrow'       => 'اپلیکیشن اندروید · امنیت و حریم خصوصی',
				'title'         => 'SafeJot؛ رازهای شما فقط با رمز خودتان باز می‌شوند',
				'lead'          => 'رمز کارت، یادداشت محرمانه، آدرس یا پیامی که نباید به دست دیگران برسد را بنویسید، با رمز عبور خودتان قفل کنید و متن رمزی را با خیال راحت در پیام‌رسان بفرستید یا هر جا خواستید نگه دارید. با رمزنگاری استاندارد AES-256، کاملاً آفلاین، بدون حساب کاربری و بدون ذخیره هیچ متن یا رمزی. بدون رمز شما، هیچ‌کس، حتی سازنده برنامه، نمی‌تواند آن را باز کند.',
				'buy'           => 'خرید SafeJot',
				'more'          => 'امکانات',
				'points'        => array( 'رمزنگاری AES-256', 'کاملاً آفلاین', 'فارسی و انگلیسی' ),
				'icon_alt'      => 'آیکون اپلیکیشن SafeJot',
				'features_eye'  => 'امکانات',
				'features_title' => 'امنیت جدی، با سادگی یک یادداشت',
				'features'      => array(
					array( 'shield', 'رمزنگاری استاندارد AES-256', 'همان استانداردی که بانک‌ها و پیام‌رسان‌های امن به کار می‌برند؛ کلید رمزنگاری از رمز عبور شما ساخته می‌شود.' ),
					array( 'key', 'قفل با رمز عبور شما', 'هر متن فقط با رمز عبوری که خودتان انتخاب کرده‌اید باز می‌شود؛ حتی سازنده برنامه هم راهی برای باز کردن آن ندارد.' ),
					array( 'lock', 'تشخیص دستکاری', 'اگر رمز اشتباه باشد یا متن رمزی تغییر کرده باشد، برنامه به‌جای متن نامفهوم، پیام خطای روشن نشان می‌دهد.' ),
					array( 'eye', 'بدون اینترنت و بدون ذخیره', 'برنامه مجوز اینترنت ندارد و هیچ متن یا رمزی را نگه نمی‌دارد؛ همه‌چیز روی خود گوشی انجام می‌شود.' ),
					array( 'globe', 'فارسی و انگلیسی', 'زبان برنامه با زبان گوشی هماهنگ می‌شود و با یک لمس عوض می‌شود. متن رمزی بین هر دو زبان یکسان است.' ),
					array( 'file-text', 'کپی و چسباندن سریع', 'متن رمزی را با یک لمس کپی کنید و در پیام‌رسان یا ایمیل بفرستید؛ برای باز کردن هم یک دکمه چسباندن دارد.' ),
					array( 'sparkles', 'هر بار یک نتیجه تازه', 'حتی اگر یک متن را دو بار قفل کنید، دو متن رمزی کاملاً متفاوت می‌گیرید و از روی آن‌ها چیزی قابل حدس نیست.' ),
					array( 'smartphone', 'طراحی ساده و زیبا', 'دو دکمه اصلی، فونت وزیرمتن و حالت روشن و تیره؛ از همان دقیقه اول قابل استفاده است.' ),
				),
				'shots_eye'     => 'تصاویر برنامه',
				'shots_title'   => 'نگاهی به SafeJot',
				'shots'         => array(
					'home'    => 'صفحه اصلی: قفل کردن یا باز کردن متن',
					'encrypt' => 'قفل کردن متن با رمز عبور و کپی متن رمزی',
					'decrypt' => 'باز کردن متن رمزی با همان رمز عبور',
				),
				'tech_eye'      => 'پشت صحنه',
				'tech_title'    => 'مهندسی‌شده با استانداردهای امنیتی روز',
				'tech_lead'     => 'SafeJot یک قفل نمایشی نیست. با همان الگوریتم‌ها و روش‌هایی ساخته شده که نرم‌افزارهای حرفه‌ای امنیتی به کار می‌برند، و هر نسخه پیش از انتشار با تست خودکار بررسی می‌شود.',
				'stats'         => array(
					array( '۲۵۶', 'بیت طول کلید رمزنگاری' ),
					array( '۲۰۰٬۰۰۰', 'دور محاسبه برای ساخت کلید از رمز' ),
					array( '۰', 'مجوز اینترنت و ذخیره‌سازی' ),
					array( '۲', 'زبان: فارسی و انگلیسی' ),
				),
				'tech'          => array(
					array( 'cpu', 'Flutter و Dart', 'رابط کاربری روان با فریم‌ورک Flutter گوگل، کامپایل‌شده به کد بومی اندروید برای سرعت و پایداری.' ),
					array( 'shield', 'AES-256-GCM', 'رمزنگاری احراز هویت‌شده: هم محتوای متن را پنهان می‌کند و هم هر تغییری در متن رمزی را تشخیص می‌دهد.' ),
					array( 'key', 'PBKDF2-SHA256 با ۲۰۰ هزار تکرار', 'کلید با ۲۰۰ هزار دور محاسبه از رمز عبور ساخته می‌شود تا حدس زدن رمز، حتی با رایانه‌های قدرتمند، بسیار کند و پرهزینه باشد.' ),
					array( 'sparkles', 'نمک و نانس تصادفی', 'برای هر متن، نمک ۱۶ بایتی و نانس ۱۲ بایتی تازه با مولد تصادفی امن ساخته می‌شود؛ هیچ دو خروجی‌ای یکسان نیستند.' ),
					array( 'zap', 'پیاده‌سازی بهینه‌شده', 'محاسبه SHA-256 برای همین برنامه بهینه شده تا با وجود ۲۰۰ هزار تکرار، قفل و باز کردن متن سریع بماند.' ),
					array( 'layers', 'پردازش در پس‌زمینه', 'محاسبات سنگین در یک Isolate جداگانه اجرا می‌شود؛ صفحه هیچ‌وقت هنگ نمی‌کند.' ),
					array( 'lock', 'کمترین سطح حمله', 'بدون مجوز اینترنت، بدون پایگاه داده و بدون دسترسی به فایل‌ها؛ چیزی برای نشت کردن وجود ندارد.' ),
					array( 'check-circle', 'تست و ساخت خودکار', 'ساخت کلید با بردار آزمون استاندارد RFC 7914 تست می‌شود و هر نسخه با تست‌های خودکار، به‌طور خودکار در GitHub ساخته می‌شود.' ),
				),
				'steps_eye'     => 'نحوه کار',
				'steps_title'   => 'در سه قدم',
				'steps'         => array(
					array( 'بنویسید', 'متن یا یادداشت خود را در بخش «قفل کردن متن» بنویسید یا بچسبانید.' ),
					array( 'قفل کنید', 'یک رمز عبور انتخاب کنید و «قفل کن» را بزنید؛ متن رمزی آماده کپی است.' ),
					array( 'هر وقت لازم شد باز کنید', 'متن رمزی را در «باز کردن متن» بچسبانید و با همان رمز عبور به متن اصلی برسید.' ),
				),
				'plan_eye'      => 'قیمت',
				'plan_title'    => 'یک بار بخرید، همیشه استفاده کنید',
				'plan_name'     => 'لایسنس SafeJot برای اندروید',
				'plan_lead'     => 'پرداخت یک‌باره، بدون اشتراک ماهانه',
				'plan_features' => array( 'فایل نصب اندروید (APK)', 'رابط فارسی و انگلیسی', 'استفاده نامحدود روی گوشی خودتان', 'لینک دانلود شخصی بلافاصله بعد از پرداخت' ),
				'faq_eye'       => 'پرسش‌های رایج',
				'faq_title'     => 'قبل از خرید بدانید',
				'faq'           => array(
					array( 'SafeJot چقدر امن است؟', 'SafeJot از رمزنگاری AES-256-GCM استفاده می‌کند و کلید آن با PBKDF2 از رمز عبور شما ساخته می‌شود. امنیت متن به رمز عبور شما بستگی دارد: رمزی طولانی و غیرقابل حدس انتخاب کنید. برنامه هنگام انتخاب رمز، قدرت آن را نشان می‌دهد.' ),
					array( 'اگر رمز عبور را فراموش کنم چه می‌شود؟', 'رمز عبور هیچ‌جا ذخیره نمی‌شود و راه بازیابی ندارد؛ این همان چیزی است که متن شما را امن نگه می‌دارد. پس رمز را جایی امن به خاطر بسپارید.' ),
					array( 'متن فارسی را هم قفل می‌کند؟', 'بله. هر متنی، به هر زبانی و حتی همراه با ایموجی، قفل و دوباره کامل باز می‌شود. رمز عبور هم می‌تواند فارسی باشد.' ),
					array( 'گیرنده پیام هم باید SafeJot داشته باشد؟', 'بله. برای باز کردن متن رمزی، گیرنده به SafeJot و رمز عبوری که شما به او گفته‌اید نیاز دارد. رمز را از راهی جدا از خود پیام به او بدهید.' ),
					array( 'به اینترنت نیاز دارد؟', 'نه. برنامه مجوز اینترنت ندارد و همه کارها روی خود گوشی انجام می‌شود.' ),
					array( 'بعد از پرداخت، برنامه را چطور دریافت می‌کنم؟', 'بعد از پرداخت موفق، لینک دانلود شخصی در همین صفحه نمایش داده و به ایمیل شما فرستاده می‌شود. اگر مشکلی پیش آمد، فایل را از طریق پشتیبانی برایتان می‌فرستیم.' ),
					array( 'روی آیفون هم کار می‌کند؟', 'این نسخه برای گوشی‌ها و تبلت‌های اندروید ۵ و بالاتر است.' ),
					array( 'پرداخت امن است؟', 'پرداخت از درگاه زرین‌پال انجام می‌شود و اطلاعات کارت شما به سایت ما نمی‌رسد. بعد از پرداخت، کد پیگیری به شما نمایش داده می‌شود.' ),
				),
			),
			'en' => array(
				'name'          => 'SafeJot',
				'eyebrow'       => 'Android app · Security & privacy',
				'title'         => 'SafeJot: your secrets open only with your password',
				'lead'          => 'Card PINs, private notes, addresses or any message that must not fall into the wrong hands: lock it with your own password, then send the coded text by any messenger or keep it anywhere, worry-free. Standard AES-256 encryption, fully offline, no account, and no text or password ever stored. Without your password nobody can open it, not even the app\'s maker.',
				'buy'           => 'Buy SafeJot',
				'more'          => 'Features',
				'points'        => array( 'AES-256 encryption', 'Fully offline', 'English and Persian' ),
				'icon_alt'      => 'SafeJot app icon',
				'features_eye'  => 'Features',
				'features_title' => 'Serious security, as simple as a note',
				'features'      => array(
					array( 'shield', 'Standard AES-256 encryption', 'The standard used by banks and secure messengers; the key is derived from your password.' ),
					array( 'key', 'Locked with your password', 'Each text opens only with the password you chose. Not even the app\'s maker can open it.' ),
					array( 'lock', 'Tamper detection', 'A wrong password or an edited coded text gives a clear error instead of garbled text.' ),
					array( 'eye', 'No internet, nothing stored', 'The app has no internet permission and keeps no text or password; everything happens on the phone.' ),
					array( 'globe', 'English and Persian', 'The app follows the phone\'s language and switches with one tap. Coded texts work in both.' ),
					array( 'file-text', 'Quick copy and paste', 'Copy coded text with one tap to send it by messenger or email; unlocking has a paste button.' ),
					array( 'sparkles', 'A fresh result every time', 'Lock the same text twice and you get two completely different coded texts that reveal nothing.' ),
					array( 'smartphone', 'Clean, simple design', 'Two main buttons, light and dark mode: usable from the first minute.' ),
				),
				'shots_eye'     => 'Screenshots',
				'shots_title'   => 'A look at SafeJot',
				'shots'         => array(
					'home'    => 'Home screen: lock or unlock a text',
					'encrypt' => 'Locking a text with a password and copying the coded text',
					'decrypt' => 'Unlocking coded text with the same password',
				),
				'tech_eye'      => 'Under the hood',
				'tech_title'    => 'Engineered to today\'s security standards',
				'tech_lead'     => 'SafeJot is not a toy lock. It is built with the same algorithms and methods professional security software uses, and every release is checked by automated tests.',
				'stats'         => array(
					array( '256', 'bit encryption key' ),
					array( '200,000', 'rounds to derive the key from your password' ),
					array( '0', 'internet or storage permissions' ),
					array( '2', 'languages: English and Persian' ),
				),
				'tech'          => array(
					array( 'cpu', 'Flutter and Dart', 'A smooth interface built with Google\'s Flutter, compiled to native Android code for speed and stability.' ),
					array( 'shield', 'AES-256-GCM', 'Authenticated encryption: it hides the content and detects any change to the coded text.' ),
					array( 'key', 'PBKDF2-SHA256, 200,000 rounds', 'The key is derived from your password in 200,000 rounds, so guessing passwords is slow and costly even on powerful computers.' ),
					array( 'sparkles', 'Random salt and nonce', 'Every text gets a fresh 16-byte salt and 12-byte nonce from a secure random generator; no two outputs are alike.' ),
					array( 'zap', 'Optimised implementation', 'SHA-256 is tuned for this app, so locking and unlocking stay quick despite 200,000 rounds.' ),
					array( 'layers', 'Background processing', 'The heavy work runs in a separate isolate, so the screen never freezes.' ),
					array( 'lock', 'Minimal attack surface', 'No internet permission, no database, no file access: nothing to leak.' ),
					array( 'check-circle', 'Automated tests and builds', 'Key derivation is tested against the standard RFC 7914 test vector, and every release is built and tested automatically on GitHub.' ),
				),
				'steps_eye'     => 'How it works',
				'steps_title'   => 'Three steps',
				'steps'         => array(
					array( 'Write', 'Type or paste your text or note into "Lock a text".' ),
					array( 'Lock', 'Choose a password and tap Lock; the coded text is ready to copy.' ),
					array( 'Unlock any time', 'Paste the coded text into "Unlock a text" and enter the same password.' ),
				),
				'plan_eye'      => 'Price',
				'plan_title'    => 'Buy once, use it for good',
				'plan_name'     => 'SafeJot licence for Android',
				'plan_lead'     => 'One-time payment, no subscription',
				'plan_features' => array( 'Android install file (APK)', 'English and Persian interface', 'Unlimited use on your own phone', 'Personal download link right after payment' ),
				'buy_note'      => 'Buying from outside Iran? Contact us and we will send you a payment link and the app. In Iran, you can pay online in toman with an Iranian bank card (Zarinpal).',
				'buy_fa'        => 'Pay with an Iranian card',
				'contact'       => 'Contact us to buy',
				'faq_eye'       => 'FAQ',
				'faq_title'     => 'Good to know before you buy',
				'faq'           => array(
					array( 'How secure is SafeJot?', 'SafeJot uses AES-256-GCM encryption with a key derived from your password by PBKDF2. Its strength depends on your password, so choose a long one that is hard to guess; the app shows how strong it is.' ),
					array( 'What if I forget my password?', 'Your password is never stored and cannot be recovered; that is what keeps your text safe. Keep it somewhere safe.' ),
					array( 'Does the recipient need SafeJot too?', 'Yes. To open coded text, the recipient needs SafeJot and the password you gave them. Share the password separately from the message.' ),
					array( 'Does it need the internet?', 'No. The app has no internet permission and does everything on the phone.' ),
					array( 'How do I get the app after paying?', 'After a successful payment, a personal download link is shown on the page and emailed to you. If anything goes wrong, we send you the file through support.' ),
					array( 'Does it work on iPhone?', 'This version is for Android 5.0 and newer phones and tablets.' ),
				),
			),
		),
		'aesthetic-clinic' => array(
			'fa' => array(
				'name'           => 'Aesthetic Clinic Suite',
				'eyebrow'        => 'وب‌سایت پزشکی · کلینیک زیبایی، تزریق و لیزر',
				'title'          => 'وب‌سایت و پنل مدیریت آماده برای کلینیک زیبایی',
				'lead'           => 'سایت دوزبانه کلینیک، نوبت‌دهی آنلاین با بیعانه، پیش‌نمایش درمان روی عکس بیمار، پرونده الکترونیک، پیامک یادآوری و گزارش‌های مدیریتی؛ همه در یک سیستم که روی سرور و دامنه خود کلینیک نصب می‌شود. کلید پیامک و درگاه پرداخت را از داخل پنل تنظیم می‌کنید؛ بدون اشتراک ماهانه.',
				'buy'            => 'خرید وب‌سایت',
				'more'           => 'امکانات',
				'points'         => array( 'فارسی و انگلیسی', 'روی سرور خود کلینیک', 'بدون هزینه ماهانه' ),
				'icon_alt'       => 'نماد Aesthetic Clinic Suite',
				'shots_kind'     => 'web',
				'features_eye'   => 'امکانات',
				'features_title' => 'هر آنچه یک کلینیک زیبایی آنلاین لازم دارد',
				'features'       => array(
					array( 'globe', 'سایت دوزبانه و آماده گوگل', 'صفحه خدمات، پزشکان، مقالات و پرسش‌های متداول، با آدرس‌های خوانا، نقشه سایت و داده‌های ساختاریافته برای دیده شدن در جستجو.' ),
					array( 'calendar', 'نوبت‌دهی آنلاین', 'بیمار با شماره موبایل وارد می‌شود و برای خدمت و پزشک دلخواه نوبت می‌گیرد؛ منشی در پنل تأیید می‌کند.' ),
					array( 'sparkles', 'پیش‌نمایش درمان روی عکس', 'بیمار عکس خود را انتخاب می‌کند و اثر تقریبی فیلر، بوتاکس و جوان‌سازی را می‌بیند. عکس فقط در مرورگر خودش پردازش می‌شود.' ),
					array( 'file-text', 'پرونده الکترونیک بیمار', 'سوابق پزشکی، ویزیت‌ها، خدمات انجام‌شده و مدارک؛ فایل‌ها رمزنگاری‌شده نگه داشته می‌شوند.' ),
					array( 'bag', 'پرداخت آنلاین و پکیج درمانی', 'بیعانه نوبت و خرید پکیج‌های چندجلسه‌ای با زرین‌پال یا زیبال؛ جلسات مصرف‌شده هر پکیج در پرونده ثبت می‌شود.' ),
					array( 'message', 'پیامک یادآوری', 'یادآوری خودکار نوبت یک روز قبل و یادآوری جلسه بعدی هر خدمت، با کاوه‌نگار، SMS.ir، فراز اس‌ام‌اس (IPPanel) یا ملی‌پیامک.' ),
					array( 'trending', 'گزارش‌های مدیریتی', 'درآمد ماهانه در مطب و آنلاین، پرطرفدارترین خدمات، مراجعان جدید و درصد مراجعان بازگشتی، با ماه‌های شمسی.' ),
					array( 'smartphone', 'اپ موبایل بیمار', 'بیمار سایت را مثل یک اپ روی گوشی نصب می‌کند و نوبت‌ها، پکیج‌ها و پرداخت‌هایش همیشه در دسترس است؛ بدون نیاز به فروشگاه اپ.' ),
				),
				'shots_eye'      => 'تصاویر سیستم',
				'shots_title'    => 'نگاهی به سایت و پنل',
				'shots'          => array(
					'home'               => 'صفحه اصلی سایت کلینیک',
					'service'            => 'صفحه هر خدمت، با پزشکان ارائه‌دهنده و فرم نوبت',
					'simulator'          => 'پیش‌نمایش درمان روی عکس بیمار',
					'account'            => 'حساب بیمار: نوبت‌ها، پکیج‌ها و پرداخت‌ها',
					'panel-appointments' => 'تقویم نوبت‌ها در پنل، روزانه، هفتگی و ماهانه',
					'panel-reports'      => 'گزارش‌های مدیریتی: درآمد، جلسات و مراجعان',
					'panel-client'       => 'پرونده مراجع: خدمات دریافت‌شده و مبلغ هر جلسه',
					'panel-website'      => 'مدیریت محتوای سایت از داخل پنل',
				),
				'tech_eye'       => 'فنی و امنیت',
				'tech_title'     => 'ساخته‌شده با فناوری‌های روز و امنیت جدی',
				'tech_lead'      => 'اطلاعات بیماران حساس است؛ این سیستم از ابتدا با همین نگاه ساخته شده و با تست خودکار بررسی می‌شود.',
				'stats'          => array(
					array( '۲', 'زبان: فارسی و انگلیسی' ),
					array( '۴', 'نقش کاربری با دسترسی جدا' ),
					array( '۴', 'سرویس پیامک قابل اتصال' ),
					array( '۲', 'درگاه پرداخت: زرین‌پال و زیبال' ),
				),
				'tech'           => array(
					array( 'cpu', 'ASP.NET Core و React', 'سرور با ASP.NET Core و پایگاه داده PostgreSQL، سایت و پنل با React؛ سریع و قابل توسعه.' ),
					array( 'layers', 'نصب با Docker', 'یک فرمان، سایت، پنل و پایگاه داده را روی سرور لینوکس بالا می‌آورد؛ گواهی HTTPS خودکار گرفته و تمدید می‌شود.' ),
					array( 'key', 'ورود دومرحله‌ای', 'کارکنان می‌توانند ورود با کد Google Authenticator را روشن کنند؛ با کدهای بازیابی برای روز مبادا.' ),
					array( 'users', 'نقش‌ها و دسترسی‌ها', 'مدیرکل، مدیر، پزشک و منشی؛ هر نقش فقط بخش‌های مربوط به خودش را می‌بیند.' ),
					array( 'eye', 'گزارش فعالیت کاربران', 'ورودها، تغییرات و باز کردن پرونده هر بیمار، با نام کاربر، زمان و IP ثبت می‌شود.' ),
					array( 'lock', 'رمزنگاری و حفاظت', 'مدارک پرونده و کلیدهای پیامک و درگاه رمزنگاری‌شده‌اند؛ سیاست امنیت محتوا و خروج خودکار پس از بی‌کاری.' ),
					array( 'zap', 'سرعت و سئو', 'صفحه‌ها برای موتورهای جستجو از سمت سرور ساخته می‌شوند و عکس‌ها هنگام بارگذاری خودکار بهینه می‌شوند.' ),
					array( 'check-circle', 'تست خودکار و پشتیبان‌گیری', 'منطق اصلی با تست‌های خودکار بررسی می‌شود و برای پشتیبان‌گیری روزانه از پایگاه داده اسکریپت آماده دارد.' ),
				),
				'steps_eye'      => 'راه‌اندازی',
				'steps_title'    => 'در سه قدم آنلاین شوید',
				'steps'          => array(
					array( 'سفارش دهید', 'فرم پایین صفحه را پر کنید و پرداخت را انجام دهید.' ),
					array( 'نصب روی سرور', 'سورس کامل و راهنمای نصب گام‌به‌گام را دریافت می‌کنید و سیستم روی سرور و دامنه شما نصب می‌شود.' ),
					array( 'تکمیل اطلاعات در پنل', 'خدمات، پزشکان، کلید پیامک و درگاه پرداخت را از داخل پنل وارد کنید.' ),
				),
				'plan_eye'       => 'قیمت',
				'plan_title'     => 'یک بار بخرید، روی سرور خودتان',
				'plan_name'      => 'Aesthetic Clinic Suite',
				'plan_lead'      => 'پرداخت یک‌باره، بدون اشتراک ماهانه',
				'plan_features'  => array( 'سورس کامل سایت، پنل مدیریت و API', 'نسخه فارسی و انگلیسی', 'راهنمای نصب گام‌به‌گام با Docker', 'تنظیم پیامک و درگاه پرداخت از داخل پنل', 'اطلاعات بیماران فقط روی سرور خود کلینیک' ),
				'order_lead'     => 'رسید خرید به ایمیل شما فرستاده می‌شود و برای تحویل و نصب با شما تماس می‌گیریم؛ ایمیل و موبایل را درست وارد کنید.',
				'order_notes'    => 'نام کلینیک، دامنه یا هر نکته‌ای که لازم است بدانیم',
				'order_consent'  => 'موافقم اطلاعات این فرم برای ثبت سفارش، تحویل و نصب وب‌سایت و پشتیبانی استفاده شود.',
				'order_pay'      => 'و ثبت سفارش',
				'faq_eye'        => 'پرسش‌های رایج',
				'faq_title'      => 'قبل از خرید بدانید',
				'faq'            => array(
					array( 'برای نصب چه چیزی لازم است؟', 'یک سرور مجازی لینوکس (Ubuntu 22.04 یا 24.04) با حداقل ۲ گیگابایت رم و ۲۰ گیگابایت فضا، و یک دامنه. نصب با Docker انجام می‌شود و گواهی HTTPS خودکار گرفته می‌شود.' ),
					array( 'هزینه ماهانه دارد؟', 'نه. سیستم یک بار خریداری می‌شود و روی سرور خود کلینیک اجرا می‌شود؛ فقط هزینه سرور، دامنه و پیامک‌هایی که می‌فرستید با خودتان است.' ),
					array( 'رنگ و فونت سایت قابل تغییر است؟', 'بله. رنگ‌بندی و فونت سایت قابل تغییر است.' ),
					array( 'کدام سرویس‌های پیامک و درگاه‌ها پشتیبانی می‌شوند؟', 'پیامک: کاوه‌نگار، SMS.ir، فراز اس‌ام‌اس (IPPanel) و ملی‌پیامک. پرداخت: زرین‌پال و زیبال، با حالت آزمایشی. کلیدها را خودتان در پنل وارد می‌کنید و رمزنگاری‌شده ذخیره می‌شوند.' ),
					array( 'پیش‌نمایش درمان روی عکس چطور کار می‌کند؟', 'عکس بیمار فقط در مرورگر خودش پردازش می‌شود و به سرور فرستاده یا ذخیره نمی‌شود. نتیجه یک پیش‌نمایش تقریبی برای گفت‌وگو با بیمار است، نه پیش‌بینی پزشکی.' ),
					array( 'اطلاعات بیماران کجا نگه داشته می‌شود؟', 'فقط روی سرور خود کلینیک. مدارک پرونده رمزنگاری‌شده ذخیره می‌شوند و هر بار باز شدن پرونده‌ها در گزارش فعالیت ثبت می‌شود.' ),
					array( 'می‌توانم قبل از اتصال درگاه واقعی، پرداخت را امتحان کنم؟', 'بله. سیستم یک درگاه نمایشی داخلی دارد که مسیر کامل پرداخت بیعانه و پکیج را بدون جابه‌جا شدن پول نشان می‌دهد.' ),
					array( 'بعد از پرداخت چه اتفاقی می‌افتد؟', 'کد پیگیری زرین‌پال در همین صفحه نمایش داده و به ایمیل شما فرستاده می‌شود. سپس برای تحویل سورس و نصب روی سرور شما تماس می‌گیریم.' ),
				),
			),
			'en' => array(
				'name'           => 'Aesthetic Clinic Suite',
				'eyebrow'        => 'Medical website · Aesthetic, injection & laser clinics',
				'title'          => 'A ready-made website and admin panel for aesthetic clinics',
				'lead'           => 'A bilingual clinic website, online booking with deposits, treatment previews on the patient\'s own photo, electronic patient records, SMS reminders and management reports: one system, installed on the clinic\'s own server and domain. The SMS key and payment gateway are set from the panel, with no monthly fee.',
				'buy'            => 'Buy the website',
				'more'           => 'Features',
				'points'         => array( 'English and Persian', 'On the clinic\'s own server', 'No monthly fee' ),
				'icon_alt'       => 'Aesthetic Clinic Suite icon',
				'shots_kind'     => 'web',
				'features_eye'   => 'Features',
				'features_title' => 'Everything an aesthetic clinic needs online',
				'features'       => array(
					array( 'globe', 'Bilingual site, ready for Google', 'Services, doctors, articles and FAQs, with readable addresses, a sitemap and structured data so the clinic is found in search.' ),
					array( 'calendar', 'Online booking', 'Patients sign in with their mobile number and book a service and doctor; the front desk confirms in the panel.' ),
					array( 'sparkles', 'Treatment preview on a photo', 'Patients pick a photo and see the approximate effect of fillers, Botox and skin rejuvenation. The photo is processed only in their own browser.' ),
					array( 'file-text', 'Electronic patient records', 'Medical history, visits, services received and documents; files are stored encrypted.' ),
					array( 'bag', 'Online payments and packages', 'Booking deposits and multi-session treatment packages through ZarinPal or Zibal; sessions used are tracked in the file.' ),
					array( 'message', 'SMS reminders', 'Automatic reminders the day before an appointment and when the next session is due, through Kavenegar, SMS.ir, IPPanel (FarazSMS) or MeliPayamak.' ),
					array( 'trending', 'Management reports', 'Monthly revenue in the clinic and online, top services, new clients and the share of returning clients.' ),
					array( 'smartphone', 'Patient mobile app', 'Patients install the site on their phone like an app, with their appointments, packages and payments always at hand; no app store needed.' ),
				),
				'shots_eye'      => 'Screenshots',
				'shots_title'    => 'A look at the site and the panel',
				'shots'          => array(
					'home'               => 'The clinic website\'s home page',
					'service'            => 'A service page, with its doctors and the booking form',
					'simulator'          => 'Treatment preview on the patient\'s photo',
					'account'            => 'Patient account: appointments, packages and payments',
					'panel-appointments' => 'Appointment calendar in the panel: day, week and month',
					'panel-reports'      => 'Management reports: revenue, sessions and clients',
					'panel-client'       => 'Client file: services received and the amount of each session',
					'panel-website'      => 'Managing the website\'s content from the panel',
				),
				'tech_eye'       => 'Technology & security',
				'tech_title'     => 'Built with current technology and serious security',
				'tech_lead'      => 'Patient data is sensitive. The system was designed with that in mind from the start, and is checked by automated tests.',
				'stats'          => array(
					array( '2', 'languages: English and Persian' ),
					array( '4', 'user roles with separate access' ),
					array( '4', 'SMS services to choose from' ),
					array( '2', 'payment gateways: ZarinPal and Zibal' ),
				),
				'tech'           => array(
					array( 'cpu', 'ASP.NET Core and React', 'An ASP.NET Core server with a PostgreSQL database; the site and panel are built with React. Fast and easy to extend.' ),
					array( 'layers', 'Docker install', 'One command starts the site, panel and database on a Linux server; the HTTPS certificate is obtained and renewed automatically.' ),
					array( 'key', 'Two-step sign-in', 'Staff can turn on sign-in with a Google Authenticator code, with recovery codes for a rainy day.' ),
					array( 'users', 'Roles and permissions', 'General manager, manager, doctor and secretary; each role sees only its own parts.' ),
					array( 'eye', 'Staff activity log', 'Sign-ins, changes and every opening of a patient\'s file are recorded with the user, time and IP.' ),
					array( 'lock', 'Encryption and protection', 'File documents and the SMS and gateway keys are encrypted; a content security policy and automatic sign-out after inactivity.' ),
					array( 'zap', 'Speed and SEO', 'Pages are rendered on the server for search engines, and uploaded images are optimised automatically.' ),
					array( 'check-circle', 'Automated tests and backups', 'The core logic is checked by automated tests, and a ready script backs up the database daily.' ),
				),
				'steps_eye'      => 'Getting started',
				'steps_title'    => 'Online in three steps',
				'steps'          => array(
					array( 'Order', 'Get in touch, or pay with an Iranian card on the Persian page.' ),
					array( 'Install on your server', 'You receive the full source code and a step-by-step installation guide, and the system is installed on your server and domain.' ),
					array( 'Complete it in the panel', 'Enter the services, doctors, SMS key and payment gateway in the panel.' ),
				),
				'plan_eye'       => 'Price',
				'plan_title'     => 'Buy once, run it on your own server',
				'plan_name'      => 'Aesthetic Clinic Suite',
				'plan_lead'      => 'One-time payment, no subscription',
				'plan_features'  => array( 'Full source code: website, admin panel and API', 'English and Persian versions', 'Step-by-step Docker installation guide', 'SMS and payment gateway set from the panel', 'Patient data stays on the clinic\'s own server' ),
				'buy_note'       => 'Buying from outside Iran? Contact us and we will send you a payment link. In Iran, you can pay online in toman with an Iranian bank card (Zarinpal) on the Persian page.',
				'buy_fa'         => 'Pay with an Iranian card',
				'contact'        => 'Contact us to buy',
				'faq_eye'        => 'FAQ',
				'faq_title'      => 'Good to know before you buy',
				'faq'            => array(
					array( 'What do I need to install it?', 'A Linux virtual server (Ubuntu 22.04 or 24.04) with at least 2 GB of RAM and 20 GB of disk, and a domain. It installs with Docker and gets its HTTPS certificate automatically.' ),
					array( 'Is there a monthly fee?', 'No. You buy it once and it runs on the clinic\'s own server; you only pay for the server, the domain and the text messages you send.' ),
					array( 'Can the colours and font be changed?', 'Yes. The site\'s colours and font can be changed.' ),
					array( 'Which SMS services and payment gateways are supported?', 'SMS: Kavenegar, SMS.ir, IPPanel (FarazSMS) and MeliPayamak. Payments: ZarinPal and Zibal, with test mode. You enter the keys in the panel and they are stored encrypted. These services are Iranian; others can be added on request.' ),
					array( 'How does the treatment preview work?', 'The patient\'s photo is processed only in their own browser and is never sent to or stored on the server. The result is an approximate preview to support the conversation with the patient, not a medical prediction.' ),
					array( 'Where is patient data kept?', 'Only on the clinic\'s own server. File documents are stored encrypted, and every opening of a patient\'s file is recorded in the activity log.' ),
					array( 'Can I try payments before connecting a real gateway?', 'Yes. A built-in demo gateway shows the full deposit and package payment flow without moving any money.' ),
				),
			),
		),
		'acupuncture-clinic' => array(
			'fa' => array(
				'name'           => 'نرم‌افزار کلینیک طب سوزنی',
				'eyebrow'        => 'وب‌سایت پزشکی · کلینیک طب سوزنی و کاشت نخ',
				'title'          => 'سایت، نوبت‌دهی و پرونده الکترونیک کلینیک طب سوزنی',
				'lead'           => 'سایت دوزبانه کلینیک، نوبت‌دهی آنلاین با پیش‌پرداخت، پرونده بیمار با فرم شرح حال طب سوزنی و کاشت نخ، ثبت نقاط روی تصاویر سه‌بعدی آناتومی، داشبورد بیمار و گزارش‌های درآمد و مراجعات؛ همه در یک سیستم که روی هاست و دامنه خود کلینیک نصب می‌شود. طراحی‌شده توسط پزشکی که خودش طب سوزنی کار می‌کند؛ بدون اشتراک ماهانه.',
				'buy'            => 'خرید نرم‌افزار',
				'more'           => 'امکانات',
				'points'         => array( 'فارسی و انگلیسی', 'روی هاست خود کلینیک', 'بدون هزینه ماهانه' ),
				'icon_alt'       => 'نماد نرم‌افزار کلینیک طب سوزنی',
				'shots_kind'     => 'web',
				'os'             => 'Web (Linux or Windows server, ASP.NET Core)',
				'features_eye'   => 'امکانات',
				'features_title' => 'هر آنچه یک کلینیک طب سوزنی لازم دارد، یک‌جا',
				'features'       => array(
					array( 'calendar', 'نوبت‌دهی آنلاین با پیش‌پرداخت', 'بیمار پزشک، خدمت و ساعت را انتخاب می‌کند و در صورت نیاز پیش‌پرداخت را با زرین‌پال می‌دهد؛ نوبت‌های پرداخت‌نشده خودکار آزاد می‌شوند.' ),
					array( 'file-text', 'پرونده الکترونیک بیمار', 'فرم شرح حال ۱۰ بخشی طب سوزنی و کاشت نخ، جلسات درمان، شدت درد، فایل‌ها و تصاویر بیمار و چاپ A4 پرونده.' ),
					array( 'target', 'نقشه ۳۹۰ نقطه', 'ثبت نقاط روی ۱۰ تصویر سه‌بعدی آناتومی بدن، سر، دست، پا و گوش، با پروتکل‌های آماده و کپی نقاط جلسه قبل.' ),
					array( 'smartphone', 'داشبورد بیمار', 'بیمار نوبت می‌گیرد، سوابق مراجعه و فایل‌هایش را می‌بیند و بعد از هر ویزیت به پزشکش امتیاز و نظر می‌دهد.' ),
					array( 'trending', 'گزارش درآمد و مراجعات', 'درآمد ماهانه به تفکیک مطب و آنلاین، پرفروش‌ترین خدمات، بیماران جدید و بازگشتی، و نمودار مراجعات بر اساس سن، جنس، شغل و نوع درمان.' ),
					array( 'users', 'چند پزشک و منشی', 'هر پزشک فقط بیماران و نوبت‌های خودش را می‌بیند؛ مدیر، پزشک، منشی و بیمار هر کدام دسترسی جدا دارند. پزشکان جدید با تأیید مدیر اضافه می‌شوند.' ),
					array( 'globe', 'سایت دوزبانه و آماده گوگل', 'صفحه خدمات با پرسش‌های متداول، پزشکان، بلاگ و مقالات پزشکی، با آدرس‌های خوانا، نقشه سایت و اتصال به Search Console و Google Analytics.' ),
					array( 'star', 'نظرات و مدارک پزشکان', 'نظر بیماران پس از تأیید مدیر در صفحه هر پزشک نمایش داده می‌شود؛ مدارک و افتخارات هر پزشک هم در صفحه او دیده می‌شود.' ),
				),
				'shots_eye'      => 'تصاویر سیستم',
				'shots_title'    => 'نگاهی به سایت و پنل',
				'shots'          => array(
					'home'               => 'صفحه اصلی سایت کلینیک',
					'service'            => 'صفحه هر خدمت، با توضیحات و پرسش‌های متداول',
					'dashboard'          => 'داشبورد بیمار: نوبت‌ها، سوابق مراجعه و رزرو',
					'panel-dashboard'    => 'پیشخوان پنل مدیریت',
					'panel-appointments' => 'تقویم نوبت‌ها: روزانه، هفتگی، ماهانه و سالانه',
					'panel-session'      => 'جلسه درمان: ثبت نقاط روی تصاویر سه‌بعدی آناتومی',
					'panel-revenue'      => 'گزارش درآمد و خدمات، بیماران جدید و بازگشتی',
					'panel-charts'       => 'نمودارهای آماری مراجعات',
				),
				'tech_eye'       => 'فنی و امنیت',
				'tech_title'     => 'ساخته‌شده با فناوری روز و امنیت جدی',
				'tech_lead'      => 'اطلاعات بیماران حساس است؛ این سیستم از ابتدا با همین نگاه ساخته شده و با بیش از ۲۰۰ تست خودکار بررسی می‌شود.',
				'stats'          => array(
					array( '۳۹۰', 'نقطه طب سوزنی روی تصاویر' ),
					array( '۱۰', 'تصویر سه‌بعدی آناتومی' ),
					array( '۴', 'نقش کاربری با دسترسی جدا' ),
					array( '۲', 'زبان: فارسی و انگلیسی' ),
				),
				'tech'           => array(
					array( 'cpu', 'ASP.NET Core 10', 'سریع و سبک، روی سرور لینوکس یا هاست ویندوزی؛ پایگاه داده داخلی و بدون نیاز به سرویس جداگانه.' ),
					array( 'key', 'ورود دومرحله‌ای کارکنان', 'کارکنان فقط با کد Google Authenticator وارد پنل می‌شوند؛ با کدهای بازیابی و خروج خودکار پس از ۳۰ دقیقه بی‌کاری.' ),
					array( 'eye', 'گزارش دسترسی و فعالیت', 'هر بار باز شدن پرونده پزشکی، هر تغییر کارکنان در پنل و هر ورود، با نام کاربر، زمان و IP ثبت می‌شود.' ),
					array( 'lock', 'رمزنگاری و بکاپ', 'اطلاعات حساس بیمار رمزنگاری‌شده ذخیره می‌شود و هر شب از کل اطلاعات بکاپ رمزدار گرفته می‌شود.' ),
					array( 'shield', 'حفاظت از سایت', 'سیاست امنیت محتوا، محدودیت تلاش ورود، تشخیص ربات در فرم‌ها و رمزهای عبور قوی برای کارکنان.' ),
					array( 'zap', 'سرعت و سئو', 'صفحه‌ها سریع بارگذاری می‌شوند و عکس‌ها هنگام بارگذاری خودکار کوچک و بهینه می‌شوند؛ داده‌های ساختاریافته برای گوگل.' ),
					array( 'smartphone', 'نصب روی گوشی', 'بیمار سایت را مثل یک اپ روی گوشی نصب می‌کند؛ بدون نیاز به فروشگاه اپ.' ),
					array( 'check-circle', 'آمار بازدید داخلی', 'سایت بدون فرستادن اطلاعات به جای دیگر، بازدیدها و منبع آن‌ها (اینستاگرام، تلگرام، واتس‌اپ) را می‌شمارد.' ),
				),
				'steps_eye'      => 'راه‌اندازی',
				'steps_title'    => 'در سه قدم آنلاین شوید',
				'steps'          => array(
					array( 'سفارش دهید', 'فرم پایین صفحه را پر کنید و پرداخت را انجام دهید.' ),
					array( 'هاست و دامنه', 'هاست و دامنه بخرید یا از همان که دارید استفاده کنید؛ در انتخاب کمکتان می‌کنیم.' ),
					array( 'نصب و تحویل', 'سایت را با لوگو و نام کلینیک شما نصب می‌کنیم، SSL و بکاپ شبانه را فعال می‌کنیم و کار با پنل را آموزش می‌دهیم.' ),
				),
				'plan_eye'       => 'قیمت',
				'plan_title'     => 'یک بار بخرید، روی هاست خودتان',
				'plan_name'      => 'نرم‌افزار کلینیک طب سوزنی',
				'plan_lead'      => 'پرداخت یک‌باره، بدون اشتراک ماهانه',
				'plan_features'  => array( 'سایت، پنل مدیریت و پرونده الکترونیک بیمار', 'نسخه فارسی و انگلیسی', 'نصب روی هاست و دامنه کلینیک با SSL و بکاپ شبانه', 'یک سال به‌روزرسانی و پشتیبانی', 'اطلاعات بیماران فقط روی سرور خود کلینیک' ),
				'order_lead'     => 'رسید خرید به ایمیل شما فرستاده می‌شود و برای نصب با شما تماس می‌گیریم؛ ایمیل و موبایل را درست وارد کنید.',
				'order_notes'    => 'نام کلینیک، دامنه، نوع هاست یا هر نکته‌ای که لازم است بدانیم',
				'order_consent'  => 'موافقم اطلاعات این فرم برای ثبت سفارش، نصب سایت و پشتیبانی استفاده شود.',
				'order_pay'      => 'و ثبت سفارش',
				'faq_eye'        => 'پرسش‌های رایج',
				'faq_title'      => 'قبل از خرید بدانید',
				'faq'            => array(
					array( 'برای نصب چه چیزی لازم است؟', 'یک سرور مجازی لینوکس (Ubuntu) با ۲ گیگابایت رم، یا هاست ویندوزی با پشتیبانی ASP.NET Core 10، به‌همراه یک دامنه. اگر ندارید، در انتخاب و خرید کمکتان می‌کنیم.' ),
					array( 'هزینه ماهانه دارد؟', 'نه. نرم‌افزار یک بار خریداری می‌شود و روی هاست خود کلینیک اجرا می‌شود؛ فقط هزینه هاست و دامنه با خودتان است. برای ادامه به‌روزرسانی و پشتیبانی بعد از سال اول، می‌توانید تمدید کنید.' ),
					array( 'رنگ سایت قابل تغییر است؟', 'بله. سایت با لوگو و نام کلینیک شما نصب می‌شود و رنگ‌بندی آن قابل تغییر است.' ),
					array( 'اطلاعات بیماران مال کیست و کجا نگه داشته می‌شود؟', 'مال کلینیک شما و فقط روی سرور خود کلینیک. اطلاعات حساس رمزنگاری‌شده ذخیره می‌شود و هر بار باز شدن پرونده‌ها ثبت می‌شود. ما فقط برای پشتیبانی و با اجازه شما به آن دسترسی داریم.' ),
					array( 'پرداخت آنلاین بیماران چطور کار می‌کند؟', 'برای هر خدمت می‌توانید مبلغ پیش‌پرداخت تعیین کنید. بیمار هنگام نوبت گرفتن آن را با زرین‌پال می‌پردازد و مبلغ کل ویزیت را هنگام انجام ویزیت در پنل ثبت می‌کنید.' ),
					array( 'نقاط طب سوزنی چقدر دقیق‌اند؟', 'جای ۳۹۰ نقطه روی تصاویر سه‌بعدی آناتومی تقریبی است و برای ثبت در پرونده طراحی شده، نه برای آموزش یا تعیین دقیق محل نقطه.' ),
					array( 'می‌توانم اول امتحانش کنم؟', 'بله. نسخه نمایشی پزشک، بیمار و نوبت ساختگی دارد و برای هر نقش یک حساب آزمایشی دارد.' ),
					array( 'بعد از پرداخت چه اتفاقی می‌افتد؟', 'کد پیگیری زرین‌پال در همین صفحه نمایش داده و به ایمیل شما فرستاده می‌شود. سپس برای نصب سایت روی هاست و دامنه شما تماس می‌گیریم.' ),
				),
			),
			'en' => array(
				'name'           => 'Acupuncture Clinic Software',
				'eyebrow'        => 'Medical website · Acupuncture & catgut embedding clinics',
				'title'          => 'Website, online booking and patient records for acupuncture clinics',
				'lead'           => 'A bilingual clinic website, online booking with prepayment, patient records with an acupuncture and catgut embedding intake form, acupoints recorded on 3D anatomy images, a patient dashboard, and revenue and visit reports: one system, installed on the clinic\'s own host and domain. Designed by a physician who practises acupuncture, with no monthly fee.',
				'buy'            => 'Buy the software',
				'more'           => 'Features',
				'points'         => array( 'English and Persian', 'On the clinic\'s own host', 'No monthly fee' ),
				'icon_alt'       => 'Acupuncture Clinic Software icon',
				'shots_kind'     => 'web',
				'os'             => 'Web (Linux or Windows server, ASP.NET Core)',
				'features_eye'   => 'Features',
				'features_title' => 'Everything an acupuncture clinic needs, in one place',
				'features'       => array(
					array( 'calendar', 'Online booking with prepayment', 'Patients choose a doctor, service and time, and pay a prepayment online when one is set; unpaid bookings are released automatically.' ),
					array( 'file-text', 'Electronic patient records', 'A 10-section acupuncture and catgut embedding intake form, treatment sessions, pain scores, patient files and images, and A4 printouts.' ),
					array( 'target', '390-point charts', 'Record points on 10 3D anatomy images of the body, head, arm, leg and ear, with ready-made protocols and copying from the last session.' ),
					array( 'smartphone', 'Patient dashboard', 'Patients book, see their visit history and files, and rate their doctor after each visit.' ),
					array( 'trending', 'Revenue and visit reports', 'Monthly revenue in the clinic and online, top services, new and returning patients, and visit charts by age, sex, occupation and treatment.' ),
					array( 'users', 'Doctors and reception', 'Each doctor sees only their own patients and appointments; admin, doctor, reception and patients have separate access. New doctors join after the admin approves them.' ),
					array( 'globe', 'Bilingual site, ready for Google', 'Service pages with FAQs, doctors, a blog and medical articles, with readable addresses, a sitemap and Search Console and Google Analytics connection.' ),
					array( 'star', 'Reviews and credentials', 'Patient reviews appear on each doctor\'s page once the admin approves them, together with the doctor\'s credentials.' ),
				),
				'shots_eye'      => 'Screenshots',
				'shots_title'    => 'A look at the site and the panel',
				'shots'          => array(
					'home'               => 'The clinic website\'s home page',
					'service'            => 'A service page, with its description and FAQs',
					'dashboard'          => 'Patient dashboard: appointments, visit history and booking',
					'panel-dashboard'    => 'The management panel\'s dashboard',
					'panel-appointments' => 'Appointment calendar: day, week, month and year',
					'panel-session'      => 'Treatment session: points recorded on 3D anatomy images',
					'panel-revenue'      => 'Revenue and services, new and returning patients',
					'panel-charts'       => 'Visit statistics charts',
				),
				'tech_eye'       => 'Technology & security',
				'tech_title'     => 'Built with current technology and serious security',
				'tech_lead'      => 'Patient data is sensitive. The system was designed with that in mind from the start, and is checked by more than 200 automated tests.',
				'stats'          => array(
					array( '390', 'acupoints on the charts' ),
					array( '10', '3D anatomy images' ),
					array( '4', 'user roles with separate access' ),
					array( '2', 'languages: English and Persian' ),
				),
				'tech'           => array(
					array( 'cpu', 'ASP.NET Core 10', 'Fast and light, on a Linux server or a Windows host, with a built-in database and no separate service to run.' ),
					array( 'key', 'Two-step sign-in for staff', 'Staff sign in to the panel only with a Google Authenticator code, with recovery codes and automatic sign-out after 30 minutes idle.' ),
					array( 'eye', 'Access and activity logs', 'Every opening of a medical record, every change staff make in the panel and every sign-in is recorded with the user, time and IP.' ),
					array( 'lock', 'Encryption and backups', 'Sensitive patient data is stored encrypted, and an encrypted backup of everything is taken every night.' ),
					array( 'shield', 'Site protection', 'A content security policy, sign-in attempt limits, bot checks on forms and strong passwords for staff.' ),
					array( 'zap', 'Speed and SEO', 'Pages load fast, uploaded pictures are resized and optimised automatically, and structured data is ready for Google.' ),
					array( 'smartphone', 'Install on a phone', 'Patients install the site on their phone like an app, with no app store needed.' ),
					array( 'check-circle', 'Built-in visitor statistics', 'The site counts visits and where they came from (Instagram, Telegram, WhatsApp) without sending data anywhere else.' ),
				),
				'steps_eye'      => 'Getting started',
				'steps_title'    => 'Online in three steps',
				'steps'          => array(
					array( 'Order', 'Get in touch, or pay with an Iranian card on the Persian page.' ),
					array( 'Host and domain', 'Buy a host and domain, or use the ones you have; we help you choose.' ),
					array( 'Installation and hand-over', 'We install the site with your clinic\'s logo and name, turn on SSL and nightly backups, and show your staff how to use the panel.' ),
				),
				'plan_eye'       => 'Price',
				'plan_title'     => 'Buy once, run it on your own host',
				'plan_name'      => 'Acupuncture Clinic Software',
				'plan_lead'      => 'One-time payment, no subscription',
				'plan_features'  => array( 'Website, management panel and electronic patient records', 'English and Persian versions', 'Installed on your host and domain with SSL and nightly backups', 'One year of updates and support', 'Patient data stays on the clinic\'s own server' ),
				'buy_note'       => 'Buying from outside Iran? Contact us and we will send you a payment link. In Iran, you can pay online in toman with an Iranian bank card (Zarinpal) on the Persian page.',
				'buy_fa'         => 'Pay with an Iranian card',
				'contact'        => 'Contact us to buy',
				'faq_eye'        => 'FAQ',
				'faq_title'      => 'Good to know before you buy',
				'faq'            => array(
					array( 'What do I need to install it?', 'A Linux virtual server (Ubuntu) with 2 GB of RAM, or a Windows host that supports ASP.NET Core 10, and a domain. If you don\'t have them yet, we help you choose.' ),
					array( 'Is there a monthly fee?', 'No. You buy it once and it runs on the clinic\'s own host; you only pay for the host and the domain. To keep receiving updates and support after the first year, you can renew.' ),
					array( 'Can the site\'s colours be changed?', 'Yes. The site is installed with your clinic\'s logo and name, and its colours can be changed.' ),
					array( 'Who owns patient data, and where is it kept?', 'Your clinic, and only on the clinic\'s own server. Sensitive data is stored encrypted and every opening of a record is logged. We access it only for support, with your permission.' ),
					array( 'How do patients pay online?', 'You can set a prepayment for each service. Patients pay it through Zarinpal when they book, and you record the visit\'s full fee in the panel when the visit is done.' ),
					array( 'How accurate are the acupoints?', 'The positions of the 390 points on the 3D anatomy images are approximate. They are meant for recording in the patient file, not for teaching or locating points precisely.' ),
					array( 'Can I try it first?', 'Yes. The demo has made-up doctors, patients and appointments, with a test login for each role.' ),
				),
			),
		),
	);
	return $texts[ $app ][ 'en' === $lang ? 'en' : 'fa' ];
}
