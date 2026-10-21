<?php
/**
 * Product sales pages (Persian and English): SafeJot (Android app) and
 * Aesthetic Clinic Suite (website). Texts, screenshots and assets. Orders are
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
				'lead'           => 'سایت دوزبانه کلینیک، نوبت‌دهی آنلاین با بیعانه، پیش‌نمایش درمان روی عکس بیمار، پرونده الکترونیک، پیامک یادآوری و گزارش‌های مدیریتی؛ همه در یک سیستم که روی سرور و دامنه خود کلینیک نصب می‌شود. نام، لوگو، رنگ‌ها، کلید پیامک و درگاه پرداخت را از داخل پنل تنظیم می‌کنید؛ بدون اشتراک ماهانه.',
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
					array( 'شخصی‌سازی در پنل', 'نام و لوگو، رنگ‌ها، خدمات، پزشکان و کلید پیامک و درگاه پرداخت را از داخل پنل وارد کنید.' ),
				),
				'plan_eye'       => 'قیمت',
				'plan_title'     => 'یک بار بخرید، روی سرور خودتان',
				'plan_name'      => 'Aesthetic Clinic Suite',
				'plan_lead'      => 'پرداخت یک‌باره، بدون اشتراک ماهانه',
				'plan_features'  => array( 'سورس کامل سایت، پنل مدیریت و API', 'نسخه فارسی و انگلیسی', 'راهنمای نصب گام‌به‌گام با Docker', 'تنظیم پیامک، درگاه و ظاهر سایت از داخل پنل', 'اطلاعات بیماران فقط روی سرور خود کلینیک' ),
				'order_lead'     => 'رسید خرید به ایمیل شما فرستاده می‌شود و برای تحویل و نصب با شما تماس می‌گیریم؛ ایمیل و موبایل را درست وارد کنید.',
				'order_notes'    => 'نام کلینیک، دامنه یا هر نکته‌ای که لازم است بدانیم',
				'order_consent'  => 'موافقم اطلاعات این فرم برای ثبت سفارش، تحویل و نصب وب‌سایت و پشتیبانی استفاده شود.',
				'order_pay'      => 'و ثبت سفارش',
				'faq_eye'        => 'پرسش‌های رایج',
				'faq_title'      => 'قبل از خرید بدانید',
				'faq'            => array(
					array( 'برای نصب چه چیزی لازم است؟', 'یک سرور مجازی لینوکس (Ubuntu 22.04 یا 24.04) با حداقل ۲ گیگابایت رم و ۲۰ گیگابایت فضا، و یک دامنه. نصب با Docker انجام می‌شود و گواهی HTTPS خودکار گرفته می‌شود.' ),
					array( 'هزینه ماهانه دارد؟', 'نه. سیستم یک بار خریداری می‌شود و روی سرور خود کلینیک اجرا می‌شود؛ فقط هزینه سرور، دامنه و پیامک‌هایی که می‌فرستید با خودتان است.' ),
					array( 'نام، لوگو و رنگ سایت قابل تغییر است؟', 'بله. نام کلینیک، لوگو، رنگ‌بندی، فونت عنوان‌ها و همه متن‌ها، خدمات، پزشکان و مقالات از داخل پنل تغییر می‌کنند و برای این کار به برنامه‌نویس نیاز نیست.' ),
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
				'lead'           => 'A bilingual clinic website, online booking with deposits, treatment previews on the patient\'s own photo, electronic patient records, SMS reminders and management reports: one system, installed on the clinic\'s own server and domain. Name, logo, colours, SMS key and payment gateway are all set from the panel, with no monthly fee.',
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
					array( 'Make it yours in the panel', 'Enter the name and logo, colours, services, doctors, SMS key and payment gateway in the panel.' ),
				),
				'plan_eye'       => 'Price',
				'plan_title'     => 'Buy once, run it on your own server',
				'plan_name'      => 'Aesthetic Clinic Suite',
				'plan_lead'      => 'One-time payment, no subscription',
				'plan_features'  => array( 'Full source code: website, admin panel and API', 'English and Persian versions', 'Step-by-step Docker installation guide', 'SMS, payment gateway and look set from the panel', 'Patient data stays on the clinic\'s own server' ),
				'buy_note'       => 'Buying from outside Iran? Contact us and we will send you a payment link. In Iran, you can pay online in toman with an Iranian bank card (Zarinpal) on the Persian page.',
				'buy_fa'         => 'Pay with an Iranian card',
				'contact'        => 'Contact us to buy',
				'faq_eye'        => 'FAQ',
				'faq_title'      => 'Good to know before you buy',
				'faq'            => array(
					array( 'What do I need to install it?', 'A Linux virtual server (Ubuntu 22.04 or 24.04) with at least 2 GB of RAM and 20 GB of disk, and a domain. It installs with Docker and gets its HTTPS certificate automatically.' ),
					array( 'Is there a monthly fee?', 'No. You buy it once and it runs on the clinic\'s own server; you only pay for the server, the domain and the text messages you send.' ),
					array( 'Can I change the name, logo and colours?', 'Yes. The clinic name, logo, colour theme, heading font and all texts, services, doctors and articles are edited in the panel, with no programmer needed.' ),
					array( 'Which SMS services and payment gateways are supported?', 'SMS: Kavenegar, SMS.ir, IPPanel (FarazSMS) and MeliPayamak. Payments: ZarinPal and Zibal, with test mode. You enter the keys in the panel and they are stored encrypted. These services are Iranian; others can be added on request.' ),
					array( 'How does the treatment preview work?', 'The patient\'s photo is processed only in their own browser and is never sent to or stored on the server. The result is an approximate preview to support the conversation with the patient, not a medical prediction.' ),
					array( 'Where is patient data kept?', 'Only on the clinic\'s own server. File documents are stored encrypted, and every opening of a patient\'s file is recorded in the activity log.' ),
					array( 'Can I try payments before connecting a real gateway?', 'Yes. A built-in demo gateway shows the full deposit and package payment flow without moving any money.' ),
				),
			),
		),
	);
	return $texts[ $app ][ 'en' === $lang ? 'en' : 'fa' ];
}
