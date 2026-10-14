<?php
/**
 * Persian content (run by languages.php in its own WP-CLI process): the
 * Persian version of every page, project, article, timeline entry and hero
 * slide, linked to its English original, plus the Persian menus.
 *
 * Persian texts: scripts/seed/data/portfolio.fa.json (portfolio and
 * articles) and this file (pages and slides).
 *
 * Run alone: wp eval-file scripts/seed/translations.php
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qvs_find_by_key' ) ) {
	require_once __DIR__ . '/helpers.php';
}
if ( ! function_exists( 'pll_save_post_translations' ) || count( pll_languages_list() ) < 2 ) {
	WP_CLI::error( 'Polylang with English and Persian is needed: run the languages seed first.' );
}

// Everything created so far without a language is English.
PLL()->model->set_language_in_mass( 'en' );

/* ------------------------------------------------------------------------
 * Helpers
 * --------------------------------------------------------------------- */

/**
 * Create or update the Persian translation of an English post and link them.
 * $postarr may override title, slug, content, excerpt; meta is added on top
 * of the English post's copied meta keys ($copy_meta).
 */
function qvs_fa( $en_id, array $postarr, array $meta = array(), array $copy_meta = array() ) {
	if ( ! $en_id ) {
		return 0;
	}
	$en    = get_post( $en_id );
	$fa_id = pll_get_post( $en_id, 'fa' );
	$data  = array_merge(
		array(
			'post_type'   => $en->post_type,
			'post_status' => $en->post_status,
			'post_name'   => $en->post_name . '-fa',
			'menu_order'  => $en->menu_order,
			'post_author' => $en->post_author,
			'post_date'   => $en->post_date,
		),
		$postarr
	);
	if ( $en->post_parent ) {
		$data['post_parent'] = (int) pll_get_post( $en->post_parent, 'fa' );
	}
	if ( $fa_id ) {
		$data['ID'] = $fa_id;
		wp_update_post( wp_slash( $data ) );
	} else {
		$fa_id = wp_insert_post( wp_slash( $data ), true );
		if ( is_wp_error( $fa_id ) ) {
			WP_CLI::warning( "Persian copy of #{$en_id}: " . $fa_id->get_error_message() );
			return 0;
		}
	}
	pll_set_post_language( $fa_id, 'fa' );
	pll_save_post_translations( array( 'en' => $en_id, 'fa' => $fa_id ) );
	$GLOBALS['qvs_fa_pairs'][ $en_id ] = (int) $fa_id;

	foreach ( $copy_meta as $key ) {
		update_post_meta( $fa_id, $key, get_post_meta( $en_id, $key, true ) );
	}
	foreach ( array( '_wp_page_template', '_thumbnail_id' ) as $key ) {
		$value = get_post_meta( $en_id, $key, true );
		if ( '' !== $value ) {
			update_post_meta( $fa_id, $key, $value );
		}
	}
	foreach ( $meta as $key => $value ) {
		update_post_meta( $fa_id, $key, $value );
	}
	return (int) $fa_id;
}

$qv_page = fn( $key ) => qvs_find_by_key( $key, 'page' );

/* ------------------------------------------------------------------------
 * Pages
 * --------------------------------------------------------------------- */
$qv_fa_data = json_decode( (string) file_get_contents( __DIR__ . '/data/portfolio.fa.json' ), true );

$qv_fa_home = qvs_fa( $qv_page( 'page-home' ), array( 'post_title' => 'خانه' ) );

$qv_fa_about = qvs_fa(
	$qv_page( 'page-about' ),
	array(
		'post_title'   => 'درباره من',
		'post_excerpt' => 'بنیان‌گذار Hassani Labs؛ توسعه‌دهنده نرم‌افزار با نگاه تحلیلی و پیشینه میان‌رشته‌ای پزشکی و مهندسی.',
		'post_content' => qvs_html(
			'<p>من مهسا حسنی هستم؛ پزشک با بیش از بیست سال تجربه حرفه‌ای در حوزه پزشکی و دارای پیشینه تحصیلی در مهندسی نرم‌افزار. مسیر حرفه‌ای من در نقطه تلاقی دو دنیای متفاوت شکل گرفته است: شناخت انسان و مسائل واقعی از یک سو، و طراحی راهکارهای منطقی و ساختاریافته از سوی دیگر.</p>
			<p>پزشکی به من آموخته است که پیش از ارائه هر راهکاری، باید مسئله را به‌درستی شناخت، اطلاعات را با دقت بررسی کرد و به جزئیاتی توجه داشت که ممکن است در نگاه اول دیده نشوند. در توسعه نرم‌افزار نیز همین رویکرد را دنبال می‌کنم: تحلیل نیاز، طراحی ساختار مناسب، پیاده‌سازی دقیق و بهبود مستمر.</p>
			<h3>از شناخت مسئله تا ساخت راهکار</h3>
			<p>در Hassani Labs، تمرکز من بر طراحی و توسعه وب‌سایت‌ها، برنامه‌های تحت وب و نرم‌افزارهای اختصاصی است. همچنین به طراحی ابزارهای کاربردی، خودکارسازی فرایندها و استفاده از فناوری برای ساده‌تر کردن کارهای پیچیده علاقه‌مندم.</p>
			<p>در مسیر توسعه، با فناوری‌هایی مانند C#، ASP.NET Core، Python، JavaScript، TypeScript، React و Next.js کار می‌کنم و بسته به نیاز هر پروژه، ابزار و معماری مناسب را انتخاب می‌کنم. برای من، انتخاب فناوری باید در خدمت هدف پروژه باشد؛ نه اینکه صرفاً به استفاده از ابزارهای جدید محدود شود.</p>
			<h3>کیفیت، فراتر از ظاهر</h3>
			<p>یک محصول دیجیتال موفق فقط ظاهر زیبا یا عملکرد اولیه مناسب ندارد. ساختار قابل نگهداری، امنیت، تجربه کاربری، قابلیت توسعه و تناسب با نیازهای واقعی کاربران نیز اهمیت دارند.</p>
			<p>به همین دلیل، در پروژه‌ها تلاش می‌کنم از ابتدا به مسیر رشد محصول، نحوه نگهداری و امکان افزودن قابلیت‌های جدید فکر کنم؛ رویکردی که به‌ویژه در نرم‌افزارهای اختصاصی و پروژه‌های بلندمدت اهمیت دارد.</p>
			<h3>چرا Hassani Labs؟</h3>
			<p>Hassani Labs فضای کاری من برای تبدیل ایده‌ها و نیازهای واقعی به راهکارهای نرم‌افزاری است؛ فضایی برای تحلیل، طراحی، توسعه و بهبود محصولاتی که قرار است در عمل مفید باشند.</p>
			<p>چه به یک وب‌سایت حرفه‌ای نیاز داشته باشید، چه بخواهید فرایندی را دیجیتالی کنید یا ایده‌ای برای ساخت یک نرم‌افزار اختصاصی داشته باشید، نقطه شروع همکاری ما شناخت دقیق نیاز شما و تعریف راهکاری متناسب با آن خواهد بود.</p>
			<p><strong>هدف من صرفاً ساختن نرم‌افزار نیست؛ هدف، ساختن راهکاری است که دلیل مشخصی برای وجود داشتن داشته باشد.</strong></p>'
		),
	),
	array(
		'qv_story_title' => 'دو رشته، یک شیوه فکر کردن',
		'qv_mission'     => 'ساختن نرم‌افزاری که کلینیک‌ها و کسب‌وکارهای کوچک بتوانند کار روزانه‌شان را با اطمینان به آن بسپارند: روشن، امن، مستند و پشتیبانی‌شده توسط کسی که آن را نوشته است.',
		'qv_vision'      => 'ابزارهایی که به کاربرانشان و داده‌هایی که نگه می‌دارند احترام می‌گذارند، به فارسی و انگلیسی.',
		'qv_values'      => "صداقت | بدون عدد ساختگی و نظر جعلی. آنچه نرم‌افزار انجام می‌دهد همان است که صفحه می‌گوید.\nمراقبت از داده | داده بیماران و مشتریان روی سرور خود مجموعه می‌ماند، جاهای حساس رمزگذاری می‌شود و پشتیبان گرفته می‌شود.\nشفافیت | محدوده کار ثابت، گفت‌وگوی ساده و یادداشت تحویل مکتوب.\nمالکیت | داده و دامنه متعلق به شماست. من برای به‌روزرسانی و پشتیبانی در دسترس می‌مانم.",
		'qv_skills'      => wp_json_encode(
			array_map(
				fn( $g ) => array(
					'title'  => $g[0],
					'blurb'  => $g[1],
					'skills' => array(),
				),
				$qv_fa_data['skills']
			),
			JSON_UNESCAPED_UNICODE
		),
	),
	array( 'qv_story_image' )
);

// Skill names stay in English (they are product names); the notes are Persian.
$qv_en_skills = json_decode( (string) get_post_meta( $qv_page( 'page-about' ), 'qv_skills', true ), true );
if ( $qv_fa_about && $qv_en_skills ) {
	foreach ( $qv_en_skills as $qv_g => &$qv_group ) {
		$qv_group['title'] = $qv_fa_data['skills'][ $qv_g ][0];
		$qv_group['blurb'] = $qv_fa_data['skills'][ $qv_g ][1];
		foreach ( $qv_group['skills'] as $qv_s => &$qv_skill ) {
			$qv_skill['note'] = $qv_fa_data['skills'][ $qv_g ][2][ $qv_s ];
		}
		unset( $qv_skill );
	}
	unset( $qv_group );
	update_post_meta( $qv_fa_about, 'qv_skills', wp_json_encode( $qv_en_skills, JSON_UNESCAPED_UNICODE ) );
}

$qv_fa_blog = qvs_fa(
	$qv_page( 'page-blog' ),
	array(
		'post_title'   => 'بلاگ',
		'post_excerpt' => 'یادداشت‌هایی درباره طراحی نرم‌افزار، تست و ساخت نرم‌افزار برای کلینیک‌ها، نوشته مهسا حسنی.',
	)
);

/* Persian contact form (Contact Form 7), used on the Persian contact page. */
$qv_fa_form_shortcode = '';
if ( class_exists( 'WPCF7_ContactForm' ) ) {
	$qv_fa_mailto  = function_exists( 'qv_mod' ) && qv_mod( 'contact_email' ) ? qv_mod( 'contact_email' ) : get_option( 'admin_email' );
	$qv_fa_host    = wp_parse_url( home_url(), PHP_URL_HOST );
	$qv_fa_form_id = qvs_find_by_key( 'cf7-project-inquiry-fa', 'wpcf7_contact_form' );
	$qv_fa_form    = $qv_fa_form_id ? wpcf7_contact_form( $qv_fa_form_id ) : WPCF7_ContactForm::get_template( array( 'title' => 'فرم تماس (فارسی)' ) );
	$qv_fa_form->set_properties(
		array(
			'form'     => qvs_html(
				'<div class="form-grid">
				<p><label>نام و نام خانوادگی *[text* your-name autocomplete:name placeholder "مثلاً سارا احمدی"]</label></p>
				<p><label>ایمیل *[email* your-email autocomplete:email placeholder "you@example.com"]</label></p>
				<p><label>شماره تماس[tel your-phone autocomplete:tel placeholder "09121234567"]</label></p>
				<p><label>نام کلینیک یا شرکت[text your-company autocomplete:organization]</label></p>
				<p class="full"><label>چه چیزی لازم دارید؟ *[select* your-service first_as_label "یکی را انتخاب کنید" "نرم‌افزار کلینیک طب سوزنی" "نرم‌افزار تحت وب سفارشی" "وب‌سایت" "تغییر در محصولی که خریده‌ام" "موضوع دیگر"]</label></p>
				<p class="full"><label>توضیحات *[textarea* your-message placeholder "هدف، زمان‌بندی، نمونه‌هایی که دوست دارید…"]</label></p>
				<p class="full">[acceptance your-consent] موافقم که Hassani Labs برای پاسخ به این پیام، طبق سیاست حریم خصوصی از اطلاعات من استفاده کند. [/acceptance]</p>
				<p class="full">[submit class:btn class:btn--accent class:btn--lg "ارسال پیام"]</p>
				</div>'
			),
			'mail'     => array(
				'active'             => true,
				'subject'            => '[Hassani Labs] پیام جدید از [your-name]',
				'sender'             => "Hassani Labs Website <wordpress@{$qv_fa_host}>",
				'recipient'          => $qv_fa_mailto,
				'body'               => "نام: [your-name]\nایمیل: [your-email]\nتلفن: [your-phone]\nکلینیک یا شرکت: [your-company]\nموضوع: [your-service]\n\n[your-message]\n\n--\nارسال‌شده از فرم تماس فارسی [_site_url]",
				'additional_headers' => 'Reply-To: [your-email]',
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'mail_2'   => array(
				'active'             => true,
				'subject'            => 'پیام شما رسید — Hassani Labs',
				'sender'             => "Hassani Labs <wordpress@{$qv_fa_host}>",
				'recipient'          => '[your-email]',
				'body'               => "[your-name] عزیز، سلام\n\nممنون که پیام دادید. همه پیام‌ها را خودم می‌خوانم و ظرف دو روز کاری پاسخ می‌دهم.\n\nپیام شما:\n[your-message]\n\nمهسا حسنی\nHassani Labs\n[_site_url]",
				'additional_headers' => "Reply-To: {$qv_fa_mailto}",
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'messages' => array(
				'mail_sent_ok'             => 'ممنون! پیام شما ارسال شد. ظرف دو روز کاری پاسخ می‌دهم.',
				'mail_sent_ng'             => 'ارسال پیام با خطا روبه‌رو شد. لطفاً دوباره تلاش کنید یا مستقیم ایمیل بزنید.',
				'validation_error'         => 'یک یا چند فیلد خطا دارد. لطفاً بررسی کنید و دوباره بفرستید.',
				'spam'                     => 'ارسال پیام با خطا روبه‌رو شد. لطفاً دوباره تلاش کنید.',
				'accept_terms'             => 'برای ارسال، باید با شرایط موافقت کنید.',
				'invalid_required'         => 'لطفاً این فیلد را پر کنید.',
				'invalid_too_long'         => 'این فیلد بیش از حد طولانی است.',
				'invalid_too_short'        => 'این فیلد بیش از حد کوتاه است.',
				'upload_failed'            => 'بارگذاری فایل با خطا روبه‌رو شد.',
				'invalid_email'            => 'لطفاً یک ایمیل درست وارد کنید.',
				'invalid_tel'              => 'لطفاً یک شماره تلفن درست وارد کنید.',
				'invalid_url'              => 'لطفاً یک نشانی اینترنتی درست وارد کنید.',
				'invalid_number'           => 'لطفاً یک عدد وارد کنید.',
				'invalid_date'             => 'لطفاً یک تاریخ درست وارد کنید.',
			),
			'locale'   => 'fa_IR',
		)
	);
	$qv_fa_form->save();
	update_post_meta( $qv_fa_form->id(), QVS_KEY, 'cf7-project-inquiry-fa' );
	$qv_fa_form_shortcode = sprintf( '[contact-form-7 id="%d" title="فرم تماس (فارسی)"]', $qv_fa_form->id() );
}

$qv_fa_contact = qvs_fa(
	$qv_page( 'page-contact' ),
	array(
		'post_title'   => 'تماس',
		'post_excerpt' => 'درباره پروژه‌تان بنویسید یا هر سؤالی دارید بپرسید. ظرف دو روز کاری پاسخ می‌دهم.',
		'post_content' => '<p>چه درباره نرم‌افزار کلینیک سؤال داشته باشید، چه یک نرم‌افزار تحت وب سفارشی یا وب‌سایت بخواهید، با چند خط توضیح برایم بنویسید. همه پیام‌ها را خودم می‌خوانم و جواب می‌دهم.</p>',
	),
	array(
		'qv_faq' => "چقدر طول می‌کشد جواب بدهید؟ | ظرف دو روز کاری، معمولاً زودتر.\nبا مشتری‌های خارج از ایران هم کار می‌کنید؟ | بله. از راه دور و به فارسی و انگلیسی کار می‌کنم و تماس‌ها را با اختلاف ساعت هماهنگ می‌کنم.\nنرم‌افزار کلینیک برای کلینیک من قابل تغییر است؟ | بله. نام، لوگو و رنگ‌ها از پنل مدیریت تنظیم می‌شوند و تغییرات بزرگ‌تر جداگانه قیمت‌گذاری می‌شوند.\nقیمت پروژه سفارشی چطور تعیین می‌شود؟ | پس از یک تماس کوتاه، محدوده کار را با قیمت ثابت و مرحله‌بندی مکتوب می‌فرستم تا فقط برای کار تأییدشده پرداخت کنید.",
		'qv_form_shortcode' => $qv_fa_form_shortcode ? $qv_fa_form_shortcode : get_post_meta( $qv_page( 'page-contact' ), 'qv_form_shortcode', true ),
	)
);

$qv_fa_note = '<p class="callout"><strong>توجه:</strong> این متن یک پیش‌نویس است. پیش از انتشار، مشخصات کسب‌وکار را کامل کنید و آن را به تأیید یک مشاور حقوقی برسانید.</p>';
$qv_email   = function_exists( 'qv_mod' ) && qv_mod( 'contact_email' ) ? qv_mod( 'contact_email' ) : get_option( 'admin_email' );
$qv_mail    = '<a href="mailto:' . esc_attr( $qv_email ) . '">' . esc_html( $qv_email ) . '</a>';

$qv_fa_policies = qvs_fa(
	$qv_page( 'page-policies' ),
	array(
		'post_title'   => 'قوانین',
		'post_excerpt' => 'شرایط استفاده، حریم خصوصی، بازگشت وجه و مجوز نرم‌افزار، به زبان ساده.',
		'post_content' => '<p>این صفحه‌ها توضیح می‌دهند با داده‌های شما، خریدهایتان و نرم‌افزاری که از Hassani Labs می‌خرید چه می‌کنیم. اگر چیزی روشن نیست به ' . $qv_mail . ' ایمیل بزنید.</p>',
	)
);

$qv_fa_privacy = qvs_fa(
	$qv_page( 'page-privacy' ),
	array(
		'post_title'   => 'حریم خصوصی',
		'post_excerpt' => 'چه داده‌ای جمع می‌کنیم، چرا، تا کی نگه می‌داریم و چه حقوقی دارید.',
		'post_content' => qvs_html(
			$qv_fa_note . '
			<p>این صفحه توضیح می‌دهد Hassani Labs چه داده‌های شخصی‌ای جمع می‌کند، چرا و چه اختیاری دارید.</p>
			<h2>داده‌هایی که جمع می‌کنیم</h2>
			<ul><li><strong>حساب:</strong> نام، ایمیل، رمز عبور (به‌صورت هش‌شده) و اطلاعات صورت‌حساب.</li><li><strong>سفارش:</strong> محصول خریداری‌شده، کلید مجوز، فاکتور و وضعیت پرداخت. شماره کامل کارت ذخیره نمی‌شود.</li><li><strong>پیام‌ها:</strong> آنچه از طریق فرم تماس یا ایمیل می‌فرستید.</li><li><strong>آمار بازدید:</strong> آمار بدون کوکی و بدون اطلاعات شخصی.</li></ul>
			<h2>کاربرد داده‌ها</h2>
			<p>برای تحویل خرید، تأیید حساب، پشتیبانی، ایمیل‌های مربوط به سفارش، جلوگیری از تقلب و فقط با رضایت شما، ارسال خبرنامه.</p>
			<h2>اشتراک‌گذاری</h2>
			<p>داده فقط با سرویس‌هایی که برای اداره کار لازم‌اند (درگاه پرداخت، ارسال ایمیل و میزبانی) به اشتراک گذاشته می‌شود. داده شخصی هرگز فروخته نمی‌شود.</p>
			<h2>مدت نگهداری</h2>
			<p>داده حساب تا زمانی که حساب فعال است نگه داشته می‌شود. سوابق سفارش برای امور مالیاتی تا مدتی که قانون می‌خواهد و پیام‌های فرم تماس تا ۲۴ ماه نگه داشته می‌شوند.</p>
			<h2>حقوق شما</h2>
			<p>می‌توانید داده‌هایتان را ببینید، اصلاح کنید، خروجی بگیرید یا حذف کنید. از بخش حساب من یا با ایمیل به ' . $qv_mail . ' اقدام کنید.</p>'
		),
	)
);

$qv_fa_refund = qvs_fa(
	$qv_page( 'page-refund' ),
	array(
		'post_title'   => 'سیاست بازگشت وجه',
		'post_excerpt' => 'بازگشت وجه نرم‌افزار و خدمات مرحله‌ای.',
		'post_content' => qvs_html(
			$qv_fa_note . '
			<h2>نرم‌افزار</h2>
			<p>اگر نرم‌افزار آن‌طور که توضیح داده شده کار نکند، تا <strong>۱۴ روز</strong> پس از خرید می‌توانید درخواست بازگشت وجه بدهید. ممکن است پیش از آن پیشنهاد رفع مشکل بدهم، اما انتخاب با شماست.</p>
			<h2>نصب و خدمات</h2>
			<ol><li><strong>پیش از شروع کار:</strong> بازگشت کامل وجه.</li><li><strong>پس از شروع کار:</strong> مرحله‌های انجام‌شده و تأییدشده قابل بازگشت نیستند و مبلغ مرحله‌های باقی‌مانده بازگردانده می‌شود.</li></ol>
			<h2>روش درخواست</h2>
			<p>با شماره سفارش به ' . $qv_mail . ' ایمیل بزنید.</p>'
		),
	)
);

$qv_fa_license = qvs_fa(
	$qv_page( 'page-license' ),
	array(
		'post_title'   => 'قرارداد مجوز نرم‌افزار',
		'post_excerpt' => 'چطور می‌توانید از نرم‌افزار Hassani Labs استفاده کنید.',
		'post_content' => qvs_html(
			$qv_fa_note . '
			<h2>۱. اعطای مجوز</h2>
			<p>پس از پرداخت، مجوز غیرانحصاری و غیرقابل‌انتقال برای نصب و استفاده از نرم‌افزار، به تعداد نصبی که در سفارش آمده، به شما داده می‌شود. نصب آزمایشی و محیط تست جزو این تعداد حساب نمی‌شود.</p>
			<h2>۲. کد منبع</h2>
			<p>فروش دوباره، اجاره یا توزیع نرم‌افزار یا نسخه تغییر‌یافته آن به‌عنوان محصول رقیب مجاز نیست.</p>
			<h2>۳. به‌روزرسانی و پشتیبانی</h2>
			<p>به‌روزرسانی و پشتیبانی برای مدتی که در سفارش آمده ارائه می‌شود. پس از پایان این مدت نرم‌افزار همچنان کار می‌کند.</p>
			<h2>۴. داده‌ها</h2>
			<p>داده‌هایی که در نرم‌افزار ثبت می‌کنید، از جمله داده بیماران، متعلق به شماست و روی سرور شما می‌ماند.</p>
			<h2>۵. اجزای شخص ثالث</h2>
			<p>نرم‌افزار ممکن است اجزای متن‌باز با مجوز خودشان داشته باشد که در فایل THIRD-PARTY-NOTICES آمده‌اند.</p>
			<h2>۶. پایان مجوز</h2>
			<p>در صورت نقض این قرارداد یا بازگشت وجه، مجوز پایان می‌یابد.</p>'
		),
	)
);

$qv_fa_terms = qvs_fa(
	$qv_page( 'page-terms' ),
	array(
		'post_title'   => 'شرایط و قوانین',
		'post_excerpt' => 'قوانین استفاده از سایت، خرید مجوز نرم‌افزار و سفارش خدمات.',
		'post_content' => qvs_html(
			$qv_fa_note . '
			<p>این شرایط، استفاده از این سایت و خرید محصولات نرم‌افزاری یا خدمات از Hassani Labs را تنظیم می‌کند. با ساختن حساب یا ثبت سفارش، این شرایط را می‌پذیرید.</p>
			<h2>۱. حساب کاربری</h2>
			<p>برای خرید باید حساب بسازید و ایمیل خود را تأیید کنید. نگهداری از رمز عبور و مسئولیت فعالیت‌های حساب با شماست.</p>
			<h2>۲. محصولات و مجوزها</h2>
			<p>نرم‌افزار به‌صورت مجوز استفاده واگذار می‌شود، نه فروش مالکیت کد. دامنه مجوز در <a href="' . esc_url( get_permalink( $qv_fa_license ) ) . '">قرارداد مجوز نرم‌افزار</a> آمده است.</p>
			<h2>۳. خدمات</h2>
			<p>نصب، راه‌اندازی و پروژه‌های سفارشی طبق محدوده، زمان‌بندی و مرحله‌هایی انجام می‌شوند که پیش از شروع به‌صورت مکتوب توافق می‌شود. کار خارج از محدوده جداگانه قیمت‌گذاری می‌شود.</p>
			<h2>۴. قیمت و پرداخت</h2>
			<ul><li>قیمت‌ها در صفحه‌های فارسی به تومان و در صفحه‌های انگلیسی به دلار است.</li><li>پرداخت فارسی از طریق درگاه زرین‌پال انجام می‌شود.</li><li>تغییر قیمت روی سفارش‌های ثبت‌شده اثری ندارد.</li></ul>
			<h2>۵. بازگشت وجه</h2>
			<p>طبق <a href="' . esc_url( get_permalink( $qv_fa_refund ) ) . '">سیاست بازگشت وجه</a> انجام می‌شود.</p>
			<h2>۶. محدودیت مسئولیت</h2>
			<p>تا جایی که قانون اجازه می‌دهد، مسئولیت Hassani Labs در هر ادعا حداکثر به اندازه مبلغی است که برای همان محصول یا خدمت پرداخت کرده‌اید.</p>
			<h2>۷. قانون حاکم</h2>
			<p>این شرایط تابع قوانین [کشور محل ثبت کسب‌وکار، تکمیل شود] است.</p>
			<h2>۸. تماس</h2>
			<p>سؤال درباره این شرایط: ' . $qv_mail . '</p>'
		),
	)
);

// The Products pages exist in both languages too.
list( $qv_products_en, $qv_products_fa ) = qvs_catalog_pages();
list( $qv_app_en, $qv_app_fa ) = qvs_app_pages(); // SafeJot sales pages.
if ( $qv_app_en && $qv_app_fa ) {
	$GLOBALS['qvs_fa_pairs'][ $qv_app_en ] = (int) $qv_app_fa;
}
if ( $qv_products_en && $qv_products_fa ) {
	$GLOBALS['qvs_fa_pairs'][ $qv_products_en ] = (int) $qv_products_fa;
}

// The clinic software pages already exist in both languages: link them.
$qv_clinic_en = $qv_page( 'page-clinic-software' );
$qv_clinic_fa = $qv_page( 'page-clinic-software-fa' );
if ( $qv_clinic_en && $qv_clinic_fa ) {
	pll_set_post_language( $qv_clinic_fa, 'fa' );
	pll_save_post_translations( array( 'en' => $qv_clinic_en, 'fa' => $qv_clinic_fa ) );
}

/* ------------------------------------------------------------------------
 * Projects, timeline and articles
 * --------------------------------------------------------------------- */
foreach ( $qv_fa_data['projects'] as $qv_slug => $qv_p ) {
	$qv_content = '<h2>معماری</h2><p>' . esc_html( $qv_p['overview'] ) . '</p>';
	if ( $qv_p['layers'] ) {
		$qv_content .= '<ul>' . implode( '', array_map( fn( $l ) => '<li><strong>' . esc_html( $l[0] ) . '</strong>: ' . esc_html( $l[1] ) . '</li>', $qv_p['layers'] ) ) . '</ul>';
	}
	if ( ! empty( $qv_p['outcome'] ) ) {
		$qv_content .= '<h2>' . esc_html( $qv_p['outcome'][0] ) . '</h2><p>' . esc_html( $qv_p['outcome'][1] ) . '</p>';
	}
	$qv_en_id = qvs_find_by_key( 'project-' . $qv_slug, 'qv_project' );
	$qv_fa_id = qvs_fa(
		$qv_en_id,
		array(
			'post_title'   => get_the_title( $qv_en_id ),
			'post_excerpt' => $qv_p['summary'],
			'post_content' => $qv_content,
		),
		array(
			'qv_tagline'    => $qv_p['tagline'],
			'qv_poster_alt' => $qv_p['alt'],
			'qv_metrics'    => implode( "\n", array_map( fn( $m ) => $m[0] . ' | ' . $m[1], $qv_p['metrics'] ?? array() ) ),
		),
		array( 'qv_year', 'qv_repo', 'qv_demo', 'qv_poster', 'qv_stack', 'qv_featured' )
	);
	if ( $qv_fa_id ) {
		wp_set_object_terms( $qv_fa_id, wp_get_object_terms( $qv_en_id, 'qv_tech', array( 'fields' => 'ids' ) ), 'qv_tech', false );
	}
}

foreach ( $qv_fa_data['timeline'] as $qv_i => $qv_title ) {
	qvs_fa( qvs_find_by_key( "milestone-{$qv_i}", 'qv_milestone' ), array( 'post_title' => $qv_title, 'post_content' => '' ), array(), array( 'qv_year' ) );
}

$qv_en_cat = get_term_by( 'slug', 'engineering', 'category' );
$qv_fa_cat = 0;
if ( $qv_en_cat ) {
	$qv_fa_cat = (int) pll_get_term( $qv_en_cat->term_id, 'fa' );
	if ( ! $qv_fa_cat ) {
		$qv_new = wp_insert_term( 'مهندسی نرم‌افزار', 'category', array( 'slug' => 'engineering-fa', 'description' => 'معماری، تست و جزئیات عملی ساخت نرم‌افزار قابل‌اعتماد.' ) );
		$qv_fa_cat = is_wp_error( $qv_new ) ? 0 : (int) $qv_new['term_id'];
	}
	if ( $qv_fa_cat ) {
		pll_set_term_language( $qv_fa_cat, 'fa' );
		pll_save_term_translations( array( 'en' => $qv_en_cat->term_id, 'fa' => $qv_fa_cat ) );
	}
}

foreach ( $qv_fa_data['posts'] as $qv_slug => $qv_p ) {
	$qv_en_id = qvs_find_by_key( 'article-' . $qv_slug, 'post' );
	$qv_fa_id = qvs_fa(
		$qv_en_id,
		array(
			'post_title'    => $qv_p['title'],
			'post_excerpt'  => $qv_p['excerpt'],
			'post_content'  => qvs_blocks_html( $qv_p['body'] ),
			'post_category' => $qv_fa_cat ? array( $qv_fa_cat ) : array(),
		)
	);
	if ( $qv_fa_id ) {
		// Tags are technology names: one Persian-language tag per English tag.
		$qv_tag_ids = array();
		foreach ( wp_get_post_tags( $qv_en_id ) as $qv_tag ) {
			$qv_fa_tag = (int) pll_get_term( $qv_tag->term_id, 'fa' );
			if ( ! $qv_fa_tag ) {
				$qv_new    = wp_insert_term( $qv_tag->name, 'post_tag', array( 'slug' => $qv_tag->slug . '-fa' ) );
				$qv_fa_tag = is_wp_error( $qv_new ) ? (int) ( $qv_new->get_error_data( 'term_exists' ) ?? 0 ) : (int) $qv_new['term_id'];
				if ( $qv_fa_tag ) {
					pll_set_term_language( $qv_fa_tag, 'fa' );
					pll_save_term_translations( array( 'en' => $qv_tag->term_id, 'fa' => $qv_fa_tag ) );
				}
			}
			if ( $qv_fa_tag ) {
				$qv_tag_ids[] = $qv_fa_tag;
			}
		}
		wp_set_object_terms( $qv_fa_id, $qv_tag_ids, 'post_tag', false );
	}
}

/* ------------------------------------------------------------------------
 * Hero slides
 * --------------------------------------------------------------------- */
$qv_fa_slides = array(
	array( 'از یک مسئله واقعی، تا یک راهکار نرم‌افزاری هوشمند', 'دکتر مهسا حسنی | پزشک و توسعه‌دهنده نرم‌افزار', 'طراحی و توسعه وب‌سایت، نرم‌افزارهای اختصاصی و ابزارهای دیجیتال؛ با رویکردی تحلیلی، ساختاریافته و متناسب با نیاز واقعی شما.', 'مشاهده نمونه‌کارها', '/work/', 'شروع همکاری', '/contact/' ),
	array( 'نرم‌افزار کلینیک طب سوزنی', 'محصول', 'وب‌سایت، نوبت‌دهی آنلاین و پرونده الکترونیک بیمار، نصب‌شده روی هاست خود کلینیک.', 'اطلاعات بیشتر و سفارش', '/clinic-software-fa/', 'سؤال دارید؟', '/contact/' ),
	array( 'نرم‌افزار تحت وب سفارشی، از تعریف تا پشتیبانی', 'خدمات', 'ASP.NET Core و React، همراه با تست، مستندات و قیمت ثابت.', 'درخواست قیمت', '/contact/', 'نمونه‌کارها', '/work/' ),
);
foreach ( $qv_fa_slides as $qv_i => $qv_s ) {
	qvs_fa(
		qvs_find_by_key( "slide-{$qv_i}", 'qv_slide' ),
		array( 'post_title' => $qv_s[0] ),
		array(
			'qv_slide_eyebrow'     => $qv_s[1],
			'qv_slide_subheadline' => $qv_s[2],
			'qv_slide_cta_label'   => $qv_s[3],
			'qv_slide_cta_url'     => $qv_s[4],
			'qv_slide_cta2_label'  => $qv_s[5],
			'qv_slide_cta2_url'    => $qv_s[6],
		),
		array( 'qv_slide_video' )
	);
}

/* ------------------------------------------------------------------------
 * Persian menus
 * --------------------------------------------------------------------- */
$qv_fa_item = fn( $id, $title = '' ) => array(
	'menu-item-type'      => 'post_type',
	'menu-item-object'    => 'page',
	'menu-item-object-id' => $id,
	'menu-item-title'     => $title ? $title : get_the_title( $id ),
);
$qv_work_item = array(
	'menu-item-type'   => 'post_type_archive',
	'menu-item-object' => 'qv_project',
	'menu-item-title'  => 'نمونه‌کارها',
);

$qv_fa_primary = qvs_menu( 'Primary (فارسی)' );
qvs_menu_item( $qv_fa_primary, $qv_fa_item( $qv_fa_home ) );
qvs_menu_item( $qv_fa_primary, $qv_fa_item( $qv_fa_about ) );
qvs_menu_item( $qv_fa_primary, $qv_work_item );
qvs_catalog_menu( $qv_fa_primary, 'fa', $qv_products_fa );
qvs_menu_item( $qv_fa_primary, $qv_fa_item( $qv_fa_blog ) );
$qv_fa_policies_item = qvs_menu_item( $qv_fa_primary, $qv_fa_item( $qv_fa_policies ) );
foreach ( array( $qv_fa_terms, $qv_fa_privacy, $qv_fa_refund, $qv_fa_license ) as $qv_pid ) {
	qvs_menu_item( $qv_fa_primary, $qv_fa_item( $qv_pid ), $qv_fa_policies_item );
}
qvs_menu_item( $qv_fa_primary, $qv_fa_item( $qv_fa_contact ) );

$qv_fa_quick = qvs_menu( 'Footer — Company (فارسی)' );
foreach ( array( $qv_fa_about, $qv_fa_blog, $qv_fa_contact ) as $qv_pid ) {
	qvs_menu_item( $qv_fa_quick, $qv_fa_item( $qv_pid ) );
}
qvs_menu_item( $qv_fa_quick, $qv_work_item );

$qv_fa_products_menu = qvs_menu( 'Footer — Products (فارسی)' );
if ( $qv_clinic_fa ) {
	qvs_menu_item( $qv_fa_products_menu, $qv_fa_item( $qv_clinic_fa, 'نرم‌افزار کلینیک طب سوزنی' ) );
}

$qv_fa_policies_menu = qvs_menu( 'Footer — Policies (فارسی)' );
foreach ( array( $qv_fa_terms, $qv_fa_privacy, $qv_fa_refund, $qv_fa_license ) as $qv_pid ) {
	qvs_menu_item( $qv_fa_policies_menu, $qv_fa_item( $qv_pid ) );
}

$qv_en_locations = get_theme_mod( 'nav_menu_locations', array() );
$qv_fa_locations = array(
	'primary'         => $qv_fa_primary,
	'footer_quick'    => $qv_fa_quick,
	'footer_products' => $qv_fa_products_menu,
	'footer_policies' => $qv_fa_policies_menu,
);
$qv_nav_menus = PLL()->options->get( 'nav_menus' );
$qv_theme     = get_option( 'stylesheet' );
foreach ( $qv_fa_locations as $qv_location => $qv_menu_id ) {
	$qv_nav_menus[ $qv_theme ][ $qv_location ] = array(
		'en' => (int) ( $qv_en_locations[ $qv_location ] ?? 0 ),
		'fa' => (int) $qv_menu_id,
	);
}
PLL()->options->set( 'nav_menus', $qv_nav_menus );
PLL()->options->save();

// finish.php links every pair once more in a new process: links saved for
// the front page and the blog page are dropped by Polylang during this run.
update_option( 'qvs_fa_pairs', $GLOBALS['qvs_fa_pairs'] ?? array(), false );

WP_CLI::log( '    languages: English (root) and Persian (/fa/) with translated pages, projects, articles, slides and menus' );
