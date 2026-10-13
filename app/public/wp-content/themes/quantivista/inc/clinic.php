<?php
/**
 * Clinic software sales pages (Persian and English): texts, plan cards and
 * assets. Orders are handled in mu-plugins/quantivista-core/clinic-orders.php.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

const QV_CLINIC_TEMPLATES = array( 'page-templates/clinic-software.php', 'page-templates/clinic-software-en.php' );

/**
 * WooCommerce products sold on the English page, by plan.
 */
const QV_CLINIC_SKUS = array(
	'basic'   => 'QV-CLINIC-BASIC',
	'pro'     => 'QV-CLINIC-PRO',
	'renewal' => 'QV-CLINIC-RENEW',
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_page_template( QV_CLINIC_TEMPLATES ) ) {
			return;
		}
		wp_enqueue_style( 'quantivista-clinic', QV_URI . '/assets/css/clinic-software.css', array( 'quantivista' ), qv_asset_version( 'assets/css/clinic-software.css' ) );
	}
);

add_action(
	'wp_head',
	function () {
		if ( is_page_template( 'page-templates/clinic-software.php' ) && ! is_rtl() ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( QV_URI . '/assets/fonts/vazirmatn-variable.woff2' ) );
		}
	},
	2
);

/**
 * Plan cards for the page: Persian prices from Clinic Orders settings,
 * English prices from the WooCommerce products.
 */
function qv_clinic_plan_cards( $lang ) {
	$t     = qv_clinic_strings( $lang );
	$cards = array();

	if ( 'fa' === $lang ) {
		$plans = function_exists( 'qvc_clinic_plans' ) ? qvc_clinic_plans() : array();
		foreach ( $plans as $key => $plan ) {
			$cards[ $key ] = array_merge(
				$plan,
				array(
					'price_html' => $plan['price'] > 0 ? esc_html( qvc_toman( $plan['price'] ) ) : esc_html( $t['price_on_request'] ),
					'href'       => '#order',
					'button'     => $t['choose'],
				)
			);
		}
		return $cards;
	}

	foreach ( $t['plans'] as $key => $plan ) {
		$product = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product( wc_get_product_id_by_sku( QV_CLINIC_SKUS[ $key ] ) ) : null;
		$buyable = $product && $product->is_purchasable() && '' !== $product->get_price();
		$contact = qv_page_by_path( 'contact' );
		$cards[ $key ] = array_merge(
			$plan,
			array(
				'price_html' => $buyable ? $product->get_price_html() : esc_html( $t['price_on_request'] ),
				'href'       => $buyable ? add_query_arg( 'add-to-cart', $product->get_id(), wc_get_cart_url() ) : ( $contact ? get_permalink( $contact ) : home_url( '/' ) ),
				'button'     => $buyable ? $t['choose'] : $t['ask_price'],
			)
		);
	}
	return $cards;
}

/**
 * Page texts.
 */
function qv_clinic_strings( $lang ) {
	if ( 'en' === $lang ) {
		return array(
			'eyebrow'          => 'Acupuncture clinic software',
			'title'            => 'Your clinic website and patient records, live in days',
			'lead'             => 'Online booking, patient records with an acupuncture and catgut embedding intake form, acupoint charts on 3D anatomy images, and a bilingual website in your clinic\'s brand. Installed on your own host and domain, so the data stays with your clinic.',
			'order'            => 'See plans',
			'demo'             => 'Try the demo',
			'hero_points'      => array( 'Built by a physician who practises acupuncture', 'Persian and English', 'Your data on your server' ),
			'features_eyebrow' => 'Features',
			'features_title'   => 'Everything a clinic needs, in one place',
			'features'         => qv_clinic_features( 'en' ),
			'shots_eyebrow'    => 'Screenshots',
			'shots_title'      => 'See it in action',
			'shots'            => array( array( 'demo-home', 'Clinic home page with online booking' ), array( 'demo-admin', 'Management panel: appointments and patients' ), array( 'demo-login', 'Sign-in with two-step verification for staff' ) ),
			'steps_eyebrow'    => 'How it works',
			'steps_title'      => 'From order to a working clinic site',
			'steps'            => array(
				array( 'Order', 'Choose a plan and pay online.' ),
				array( 'Host and domain', 'Buy a host and domain, or use the ones you have. We help you pick.' ),
				array( 'Installation', 'We install it, add SSL and nightly backups, and set your logo, name and colours.' ),
				array( 'Hand-over', 'We train your staff online and hand the site over.' ),
			),
			'hosting_note'     => 'Hosting: a Linux VPS (Ubuntu) with 2 GB RAM, or a Windows host with ASP.NET Core 10, plus your own domain.',
			'plans_eyebrow'    => 'Plans',
			'plans_title'      => 'Simple, one-time setup',
			'plans_lead'       => 'Each plan includes a year of updates and support. Renew yearly to keep receiving them.',
			'plans'            => array(
				'basic'   => array( 'title' => 'Basic setup', 'lead' => 'Installed on your host with a year of support', 'features' => array( 'Installed on your own host and domain', 'SSL and encrypted nightly backups', 'Your logo, name and colours', 'One year of updates and support' ) ),
				'pro'     => array( 'title' => 'Professional', 'lead' => 'Ready to work from day one', 'popular' => true, 'features' => array( 'Everything in Basic setup', 'Services, doctors and working hours entered', 'Payment gateway and clinic email connected', 'Online training for your staff' ) ),
				'renewal' => array( 'title' => 'Yearly renewal', 'lead' => 'For clinics that already use it', 'features' => array( 'Another year of updates', 'Support and bug fixes', 'Backup and security check' ) ),
			),
			'popular'          => 'Most popular',
			'price_on_request' => 'Price on request',
			'choose'           => 'Choose this plan',
			'ask_price'        => 'Ask for a quote',
			'faq_eyebrow'      => 'FAQ',
			'faq_title'        => 'Questions clinics ask',
			'faq'              => array(
				array( 'Who owns the patient data?', 'Your clinic. The software runs on your own server, and we only access it for support when you ask us to.' ),
				array( 'Do I need my own host?', 'Yes. Each clinic has its own host and domain. If you don\'t have one yet, we help you choose one.' ),
				array( 'How are updates installed?', 'During your support year we install new versions for you, with a backup taken before every update.' ),
				array( 'What do I tell you after paying?', 'In the project brief at checkout, write your clinic name, domain (if you have one) and what kind of host you have. We contact you within one business day.' ),
				array( 'Can I try it first?', 'Yes. The demo has made-up doctors, patients and appointments, and test logins for each role.' ),
			),
		);
	}

	return array(
		'eyebrow'          => 'نرم‌افزار مدیریت کلینیک طب سوزنی',
		'title'            => 'سایت و پرونده الکترونیک کلینیک شما، آماده در چند روز',
		'lead'             => 'نوبت‌دهی آنلاین، پرونده بیمار با فرم شرح حال طب سوزنی و امبدینگ، نقشه نقاط روی تصاویر سه‌بعدی آناتومی و سایت دوزبانه با برند کلینیک شما. روی هاست و دامنه خود کلینیک نصب می‌شود و داده‌ها در اختیار خود کلینیک می‌ماند.',
		'order'            => 'ثبت سفارش',
		'demo'             => 'دیدن نسخه نمایشی',
		'hero_points'      => array( 'طراحی‌شده توسط پزشک متخصص طب سوزنی', 'فارسی و انگلیسی', 'داده‌ها روی سرور خود کلینیک' ),
		'features_eyebrow' => 'امکانات',
		'features_title'   => 'هر چه یک کلینیک لازم دارد، یک‌جا',
		'features'         => qv_clinic_features( 'fa' ),
		'shots_eyebrow'    => 'تصاویر',
		'shots_title'      => 'نگاهی به نرم‌افزار',
		'shots'            => array( array( 'demo-home', 'صفحه اصلی کلینیک با نوبت‌دهی آنلاین' ), array( 'demo-admin', 'پنل مدیریت: نوبت‌ها و بیماران' ), array( 'demo-login', 'ورود کارکنان با تأیید دو مرحله‌ای' ) ),
		'steps_eyebrow'    => 'مراحل کار',
		'steps_title'      => 'از سفارش تا سایت آماده کلینیک',
		'steps'            => array(
			array( 'ثبت سفارش', 'پلن را انتخاب و فرم پایین صفحه را پر کنید.' ),
			array( 'هاست و دامنه', 'هاست و دامنه بخرید یا از همان که دارید استفاده کنید. در انتخاب کمکتان می‌کنیم.' ),
			array( 'نصب', 'نرم‌افزار را نصب می‌کنیم، SSL و بکاپ شبانه را فعال و لوگو، نام و رنگ‌های کلینیک را تنظیم می‌کنیم.' ),
			array( 'تحویل', 'کارکنان را آنلاین آموزش می‌دهیم و سایت را تحویل می‌دهیم.' ),
		),
		'hosting_note'     => 'هاست مناسب: سرور مجازی لینوکس (Ubuntu) با ۲ گیگ رم، یا هاست ویندوزی با پشتیبانی ASP.NET Core 10، به‌همراه دامنه اختصاصی.',
		'plans_eyebrow'    => 'پلن‌ها',
		'plans_title'      => 'هزینه یک‌باره راه‌اندازی',
		'plans_lead'       => 'هر پلن یک سال به‌روزرسانی و پشتیبانی دارد. برای ادامه آن، سالانه تمدید کنید.',
		'popular'          => 'پرطرفدار',
		'price_on_request' => 'استعلام قیمت',
		'choose'           => 'انتخاب این پلن',
		'ask_price'        => 'استعلام قیمت',
		'faq_eyebrow'      => 'پرسش‌های رایج',
		'faq_title'        => 'سؤال‌هایی که کلینیک‌ها می‌پرسند',
		'faq'              => array(
			array( 'اطلاعات بیماران مال کیست؟', 'مال کلینیک شما. نرم‌افزار روی سرور خود کلینیک اجرا می‌شود و ما فقط برای پشتیبانی و با اجازه شما به آن دسترسی داریم.' ),
			array( 'باید هاست جداگانه داشته باشم؟', 'بله. هر کلینیک هاست و دامنه خودش را دارد. اگر هنوز ندارید، در انتخاب و خرید کمکتان می‌کنیم.' ),
			array( 'به‌روزرسانی‌ها چطور نصب می‌شوند؟', 'در سال پشتیبانی، نسخه‌های جدید را خودمان نصب می‌کنیم و قبل از هر به‌روزرسانی از اطلاعات بکاپ می‌گیریم.' ),
			array( 'پرداخت امن است؟', 'پرداخت از درگاه زرین‌پال انجام می‌شود و اطلاعات کارت شما به سایت ما نمی‌رسد. بعد از پرداخت، کد پیگیری به شما نمایش داده می‌شود.' ),
			array( 'می‌توانم اول امتحانش کنم؟', 'بله. نسخه نمایشی پزشک، بیمار و نوبت ساختگی دارد و برای هر نقش یک حساب آزمایشی دارد.' ),
		),
	);
}

function qv_clinic_features( $lang ) {
	$fa = array(
		array( 'calendar', 'نوبت‌دهی آنلاین', 'انتخاب پزشک و ساعت، پیش‌پرداخت با زرین‌پال و لغو خودکار نوبت‌های پرداخت‌نشده.' ),
		array( 'file-text', 'پرونده بیمار', 'فرم شرح حال ۱۰ بخشی طب سوزنی و امبدینگ، جلسات درمان، فایل‌ها و چاپ A4.' ),
		array( 'target', 'نقشه نقاط', '۳۹۰ نقطه روی تصاویر سه‌بعدی آناتومی بدن، سر، دست، پا و گوش، با پروتکل‌های آماده.' ),
		array( 'users', 'چند پزشک و منشی', 'هر پزشک فقط بیماران و نوبت‌های خودش را می‌بیند؛ منشی، مدیر و بیمار نقش جدا دارند.' ),
		array( 'globe', 'سایت دوزبانه', 'صفحه‌های خدمات، پزشکان، مقاله‌ها و تماس، با سئو و تاریخ شمسی.' ),
		array( 'trending', 'گزارش‌ها', 'گزارش ویزیت‌ها، پرداخت‌ها و آمار بازدید سایت به تفکیک شبکه‌های اجتماعی.' ),
		array( 'shield', 'امنیت', 'ورود دو مرحله‌ای کارکنان، رمزنگاری اطلاعات حساس و بکاپ شبانه رمزدار.' ),
		array( 'layout', 'برند کلینیک', 'لوگو، نام، رنگ‌ها و متن‌های صفحه اصلی را خود کلینیک تغییر می‌دهد.' ),
	);
	$en = array(
		array( 'calendar', 'Online booking', 'Patients pick a doctor and time, with optional prepayment and automatic release of unpaid slots.' ),
		array( 'file-text', 'Patient records', 'A 10-section acupuncture and catgut embedding intake form, treatment sessions, files and A4 printouts.' ),
		array( 'target', 'Acupoint charts', '390 points on 3D anatomy images of the body, head, arm, leg and ear, with ready-made protocols.' ),
		array( 'users', 'Doctors and reception', 'Each doctor sees only their own patients and appointments; reception, admin and patients have their own roles.' ),
		array( 'globe', 'Bilingual website', 'Services, doctors, articles and contact pages, with SEO built in.' ),
		array( 'trending', 'Reports', 'Visits, payments and website traffic by social network.' ),
		array( 'shield', 'Security', 'Two-step sign-in for staff, encrypted sensitive fields and encrypted nightly backups.' ),
		array( 'layout', 'Your brand', 'The clinic changes its own logo, name, colours and home page texts.' ),
	);
	return 'en' === $lang ? $en : $fa;
}
