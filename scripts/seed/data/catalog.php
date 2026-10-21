<?php
/**
 * Product categories: two groups (by platform, by field), each with its
 * categories. English is the store's name; Persian is shown on /fa/.
 * Edit here and run: wp eval-file scripts/seed.php catalog
 *
 * Category: slug => array( English name, Persian name, icon, English description, Persian description, product pages ).
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

return array(
	'platform' => array(
		'name'    => 'Software by platform',
		'name_fa' => 'بر اساس پلتفرم',
		'desc'    => 'Mobile apps, desktop programs, web applications, websites and WordPress add-ons.',
		'desc_fa' => 'اپلیکیشن موبایل، نرم‌افزار دسکتاپ، نرم‌افزار تحت وب، وب‌سایت و افزونه‌های وردپرس.',
		'icon'    => 'layers',
		'cats'    => array(
			'mobile-apps'      => array( 'Mobile apps (Android & iOS)', 'اپلیکیشن موبایل (اندروید و iOS)', 'smartphone', 'Apps for Android and iPhone, with an admin panel and store-ready builds.', 'اپلیکیشن برای اندروید و آیفون، همراه با پنل مدیریت و آماده انتشار در فروشگاه‌ها.', 'safejot' ),
			'desktop-software' => array( 'Windows & desktop software', 'نرم‌افزار ویندوز و دسکتاپ', 'monitor', 'Programs that run on Windows computers, online or offline, with installers and updates.', 'برنامه‌هایی که روی کامپیوترهای ویندوزی اجرا می‌شوند، با یا بدون اینترنت، همراه با نصب‌کننده و به‌روزرسانی.' ),
			'web-apps'         => array( 'Web applications', 'نرم‌افزار تحت وب', 'code', 'Software that runs in the browser: panels, booking, records and management systems.', 'نرم‌افزارهایی که در مرورگر اجرا می‌شوند: پنل‌ها، نوبت‌دهی، پرونده‌ها و سامانه‌های مدیریتی.', 'clinic-software' ),
			'websites'         => array( 'Websites', 'طراحی وب‌سایت', 'globe', 'Company, personal and shop websites that are fast, secure and easy to edit.', 'وب‌سایت شرکتی، شخصی و فروشگاهی؛ سریع، امن و با مدیریت آسان محتوا.', 'aesthetic-clinic-suite' ),
			'wordpress'        => array( 'WordPress plugins & themes', 'افزونه و قالب وردپرس', 'plug', 'Plugins and themes that add features to WordPress and WooCommerce sites.', 'افزونه‌ها و قالب‌هایی که امکانات تازه به سایت‌های وردپرس و ووکامرس اضافه می‌کنند.' ),
			'bots'             => array( 'Bots & chat assistants', 'ربات تلگرام، بله و دستیار گفتگو', 'bot', 'Telegram and Bale bots and chat assistants for orders, booking and support.', 'ربات‌های تلگرام و بله و دستیارهای گفتگو برای سفارش، نوبت‌دهی و پشتیبانی.' ),
		),
	),
	'industry' => array(
		'name'    => 'Software by field',
		'name_fa' => 'بر اساس حوزه کاری',
		'desc'    => 'Software made for the daily work of a profession or business.',
		'desc_fa' => 'نرم‌افزارهایی که برای کار روزانه یک حرفه یا کسب‌وکار ساخته شده‌اند.',
		'icon'    => 'briefcase',
		'cats'    => array(
			'medical-healthcare'      => array( 'Medical & healthcare', 'پزشکی و سلامت', 'stethoscope', 'Software for clinics, doctors and therapists: booking, patient records and reports.', 'نرم‌افزار برای کلینیک‌ها، پزشکان و درمانگران: نوبت‌دهی، پرونده بیمار و گزارش‌ها.', 'clinic-software,aesthetic-clinic-suite' ),
			'engineering'             => array( 'Engineering', 'مهندسی', 'ruler', 'Calculation, project control and reporting tools for engineers and contractors.', 'ابزارهای محاسبه، کنترل پروژه و گزارش‌گیری برای مهندسان و پیمانکاران.' ),
			'legal'                   => array( 'Legal & law firms', 'حقوقی و وکالت', 'scale', 'Case management, client files and deadlines for lawyers and legal offices.', 'مدیریت پرونده‌ها، اطلاعات موکلان و مهلت‌ها برای وکلا و دفاتر حقوقی.' ),
			'education'               => array( 'Education & e-learning', 'آموزش و آموزش آنلاین', 'graduation', 'Online courses, exams and student management for schools and teachers.', 'دوره‌های آنلاین، آزمون و مدیریت دانش‌آموزان برای آموزشگاه‌ها و مدرسان.' ),
			'business-accounting'     => array( 'Business & accounting', 'مدیریت کسب‌وکار و حسابداری', 'briefcase', 'Accounting, invoicing, CRM and inventory for small and medium businesses.', 'حسابداری، صدور فاکتور، مدیریت مشتریان و انبار برای کسب‌وکارهای کوچک و متوسط.' ),
			'ecommerce'               => array( 'E-commerce & retail', 'فروشگاه اینترنتی و خرده‌فروشی', 'bag', 'Online shops, payments and order management for sellers.', 'فروشگاه اینترنتی، درگاه پرداخت و مدیریت سفارش برای فروشندگان.' ),
			'ai-automation'           => array( 'AI & automation', 'هوش مصنوعی و اتوماسیون', 'sparkles', 'Smart assistants, data analysis and workflows that save repetitive work.', 'دستیارهای هوشمند، تحلیل داده و گردش‌کارهایی که کارهای تکراری را خودکار می‌کنند.' ),
			'restaurants-hospitality' => array( 'Restaurants & hospitality', 'رستوران، کافه و هتلداری', 'utensils', 'Menus, table booking, orders and guest management.', 'منوی دیجیتال، رزرو میز، ثبت سفارش و مدیریت مهمانان.' ),
			'real-estate'             => array( 'Real estate & construction', 'املاک و ساختمان', 'home', 'Property listings, building management and sales tools.', 'ثبت و نمایش فایل‌های ملکی، مدیریت ساختمان و ابزارهای فروش.' ),
		),
	),
);
