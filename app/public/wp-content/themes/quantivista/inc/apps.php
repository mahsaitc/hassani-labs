<?php
/**
 * App sales pages (Persian and English), e.g. SafeJot: texts, screenshots
 * and assets. Orders are handled in mu-plugins/quantivista-core/app-orders.php.
 *
 * Screenshots: assets/img/apps/<app>-<shot>.webp (phone screens, 540x1170),
 * English ones with an -en suffix; a missing file is skipped. SafeJot's are
 * rendered by its repo (test_shots/shots_test.dart).
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
	);
	return $texts[ $app ][ 'en' === $lang ? 'en' : 'fa' ];
}
