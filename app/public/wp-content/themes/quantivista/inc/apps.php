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
				'eyebrow'       => 'اپلیکیشن اندروید · امنیت و حریم خصوصی',
				'title'         => 'SafeJot؛ قفلی امن برای متن‌های شخصی شما',
				'lead'          => 'یادداشت، رمز، آدرس یا هر پیام خصوصی را بنویسید، با رمز عبور خودتان قفل کنید و متن رمزی را هر جا خواستید نگه دارید یا بفرستید. فقط کسی که رمز عبور را دارد می‌تواند آن را باز کند. رمزنگاری استاندارد AES-256، کاملاً آفلاین، بدون حساب کاربری و بدون ذخیره هیچ متن یا رمزی.',
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
				'title'         => 'SafeJot: a secure lock for your private texts',
				'lead'          => 'Write a note, a password, an address or any private message, lock it with your own password, and keep or send the coded text anywhere. Only someone with the password can open it. Standard AES-256 encryption, fully offline, no account, and no text or password ever stored.',
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
				'buy_note'      => 'Online payment is in toman with an Iranian bank card (Zarinpal). Buying from outside Iran? Contact us.',
				'buy_fa'        => 'Pay with an Iranian card',
				'contact'       => 'Contact us',
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
