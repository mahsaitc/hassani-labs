<?php
/**
 * Store: license attribute, categories, products (tiers, downloads,
 * galleries) and coupons. Sample products are drafts; no reviews are invented.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

$qv_uploads = wp_upload_dir();

/* ------------------------------------------------------------------------
 * License attribute + tiers
 * --------------------------------------------------------------------- */
$qv_attr_id = wc_attribute_taxonomy_id_by_name( 'license' );
if ( ! $qv_attr_id ) {
	$qv_attr_id = wc_create_attribute(
		array(
			'name'         => 'License',
			'slug'         => 'license',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);
}
if ( ! taxonomy_exists( 'pa_license' ) ) {
	register_taxonomy( 'pa_license', array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => false, 'rewrite' => false ) );
}

$qv_tiers = array(
	'standard'     => array( 'Standard', '1 site or app · 12 months of updates & support', 0, false ),
	'professional' => array( 'Professional', 'Up to 5 sites or apps · priority support', 1, true ),
	'unlimited'    => array( 'Unlimited', 'Unlimited projects · white-label rights · 24/7 support', 2, false ),
);
$qv_tier_ids = array();
foreach ( $qv_tiers as $qv_slug => $qv_tier ) {
	$qv_tier_ids[ $qv_slug ] = qvs_term( $qv_tier[0], 'pa_license', $qv_slug, array( 'description' => $qv_tier[1] ), array( 'order' => $qv_tier[2] ) );
	if ( $qv_tier[3] ) {
		update_term_meta( $qv_tier_ids[ $qv_slug ], 'qv_popular', 1 );
	} else {
		delete_term_meta( $qv_tier_ids[ $qv_slug ], 'qv_popular' );
	}
}

/* ------------------------------------------------------------------------
 * Categories
 * --------------------------------------------------------------------- */
// Theme blues, navy and cyan only (v2 keys regenerate older warm-coloured images).
$qv_palettes = array(
	'cyan'   => array( '22D3EE', '5B6CFF' ),
	'violet' => array( '818CF8', '5B6CFF' ),
	'green'  => array( '2DD4BF', '22D3EE' ),
	'amber'  => array( '67E8F9', '3A54E8' ),
	'blue'   => array( '60A5FA', '818CF8' ),
	'teal'   => array( '2DD4BF', '3B82F6' ),
	'pink'   => array( '8B9BFF', '22D3EE' ),
);

$qv_categories = array(
	'web-software'   => array(
		'name'     => 'Web Software',
		'desc'     => 'Self-hosted and cloud-ready web applications — CRM, automation, analytics and commerce tools with source code, documentation and updates.',
		'tagline'  => 'CRM, automation & analytics platforms',
		'icon'     => 'code',
		'mock'     => 'qvimg_mock_dashboard',
		'palette'  => 'cyan',
		'order'    => 0,
		'children' => array(
			'crm-tools'        => array( 'CRM Tools', 'Pipelines, contact management and lead capture.', 'users', 'qvimg_mock_kanban', 'blue' ),
			'automation-tools' => array( 'Automation Tools', 'Workflows, schedulers and integrations that remove busywork.', 'zap', 'qvimg_mock_flow', 'violet' ),
			'analytics-bi'     => array( 'Analytics & BI', 'Dashboards, funnels and reporting for data-driven teams.', 'trending', 'qvimg_mock_dashboard', 'teal' ),
			'ecommerce-tools'  => array( 'E-commerce Tools', 'Conversion and retention boosters for online stores.', 'bag', 'qvimg_mock_flow', 'amber' ),
		),
	),
	'mobile-apps'    => array(
		'name'     => 'Mobile Apps',
		'desc'     => 'Production-ready iOS and Android app kits in Flutter, React Native and native Swift/Kotlin — with backend, admin panel and store-ready assets.',
		'tagline'  => 'iOS & Android app kits with full source',
		'icon'     => 'smartphone',
		'mock'     => 'qvimg_mock_phones',
		'palette'  => 'violet',
		'order'    => 1,
		'children' => array(
			'business-productivity' => array( 'Business & Productivity', 'Scheduling, invoicing and team apps.', 'layers', 'qvimg_mock_phones', 'blue' ),
			'health-fitness'        => array( 'Health & Fitness', 'Workout, nutrition and wellness app templates.', 'target', 'qvimg_mock_phones', 'green' ),
			'app-starter-kits'      => array( 'App Starter Kits', 'Multi-app templates for marketplaces and delivery.', 'rocket', 'qvimg_mock_phones', 'amber' ),
		),
	),
	'website-design' => array(
		'name'     => 'Website Design',
		'desc'     => 'Fixed-price website design and development packages — strategy, UX, UI and a fast, SEO-ready build delivered by our in-house team.',
		'tagline'  => 'Fixed-price design & build packages',
		'icon'     => 'layout',
		'mock'     => 'qvimg_mock_landing',
		'palette'  => 'teal',
		'order'    => 2,
		'children' => array(
			'landing-pages'      => array( 'Landing Pages', 'Campaign and product launch pages built to convert.', 'target', 'qvimg_mock_landing', 'pink' ),
			'corporate-websites' => array( 'Corporate Websites', 'Multi-page company sites on a CMS your team can edit.', 'globe', 'qvimg_mock_pages', 'cyan' ),
			'ecommerce-stores'   => array( 'E-commerce Stores', 'WooCommerce and Shopify stores, designed end to end.', 'bag', 'qvimg_mock_landing', 'amber' ),
		),
	),
);

$qv_cat_ids = array();
foreach ( $qv_categories as $qv_slug => $qv_cat ) {
	$qv_pal   = $qv_palettes[ $qv_cat['palette'] ];
	$qv_thumb = qvs_image(
		"cat-{$qv_slug}-v2",
		"category-{$qv_slug}-v2.jpg",
		fn() => call_user_func( $qv_cat['mock'], 1440, 900, $qv_cat['name'], $qv_pal[0], $qv_pal[1], $qv_slug ),
		"{$qv_cat['name']} by Hassani Labs"
	);
	$qv_cat_ids[ $qv_slug ] = qvs_term(
		$qv_cat['name'],
		'product_cat',
		$qv_slug,
		array( 'description' => $qv_cat['desc'], 'parent' => 0 ),
		array(
			'thumbnail_id'   => $qv_thumb,
			'order'          => $qv_cat['order'],
			'qv_cat_icon'    => $qv_cat['icon'],
			'qv_cat_tagline' => $qv_cat['tagline'],
		)
	);

	$qv_i = 0;
	foreach ( $qv_cat['children'] as $qv_child_slug => $qv_child ) {
		$qv_cpal   = $qv_palettes[ $qv_child[4] ];
		$qv_cthumb = qvs_image(
			"cat-{$qv_child_slug}-v2",
			"category-{$qv_child_slug}-v2.jpg",
			fn() => call_user_func( $qv_child[3], 1440, 900, $qv_child[0], $qv_cpal[0], $qv_cpal[1], $qv_child_slug ),
			"{$qv_child[0]} — {$qv_cat['name']}"
		);
		$qv_cat_ids[ $qv_child_slug ] = qvs_term(
			$qv_child[0],
			'product_cat',
			$qv_child_slug,
			array( 'description' => $qv_child[1], 'parent' => $qv_cat_ids[ $qv_slug ] ),
			array(
				'thumbnail_id' => $qv_cthumb,
				'order'        => $qv_i++,
				'qv_cat_icon'  => $qv_child[2],
			)
		);
	}
}

// Keep "Uncategorized" out of the way.
$qv_uncat = get_term_by( 'slug', 'uncategorized', 'product_cat' );
if ( $qv_uncat ) {
	wp_update_term( $qv_uncat->term_id, 'product_cat', array( 'name' => 'Other' ) );
}

/* ------------------------------------------------------------------------
 * Downloadable placeholder packages
 * --------------------------------------------------------------------- */
function qvs_download_package( $slug, $name, $version ) {
	$uploads = wp_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . 'woocommerce_uploads/qv-files';
	wp_mkdir_p( $dir );
	$file = "{$dir}/{$slug}-{$version}.zip";
	if ( ! file_exists( $file ) && class_exists( 'ZipArchive' ) ) {
		$zip = new ZipArchive();
		$zip->open( $file, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		$zip->addFromString( "{$slug}/README.md", "# {$name} {$version}\n\nThank you for purchasing {$name} from Hassani Labs.\n\n1. Read INSTALL.md for server requirements.\n2. Activate using the license key from My Account → Licenses.\n3. Documentation: see the docs folder.\n\n_This is a placeholder package generated for the demo store._\n" );
		$zip->addFromString( "{$slug}/INSTALL.md", "## Installation\n\n```bash\ncp .env.example .env\ncomposer install --no-dev\nphp artisan migrate --seed\n```\n" );
		$zip->addFromString( "{$slug}/LICENSE.txt", "Hassani Labs Software License Agreement — see /policies/software-license-agreement/ on the website.\n" );
		$zip->addFromString( "{$slug}/CHANGELOG.md", "## {$version}\n- Performance improvements\n- Security hardening\n- Updated dependencies\n" );
		$zip->close();
	}
	return trailingslashit( $uploads['baseurl'] ) . "woocommerce_uploads/qv-files/{$slug}-{$version}.zip";
}

// Deny direct web access on Apache; nginx rule documented in README.
$qv_protect = trailingslashit( $qv_uploads['basedir'] ) . 'woocommerce_uploads/.htaccess';
if ( ! file_exists( $qv_protect ) ) {
	wp_mkdir_p( dirname( $qv_protect ) );
	file_put_contents( $qv_protect, "deny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/* ------------------------------------------------------------------------
 * Products
 * --------------------------------------------------------------------- */
$qv_software_reqs = "Server: PHP 8.1+ or Docker 24+\nDatabase: MySQL 8, MariaDB 10.6+ or PostgreSQL 14+\nWeb server: Nginx or Apache with HTTPS\nBrowsers: Latest Chrome, Firefox, Safari and Edge\nLicense: Per production domain (staging included)\nUpdates: 12 months included, renewals 40% off\nSupport: Ticket portal & email (Professional+: priority)\nDocumentation: Online docs, API reference, video walkthroughs";
$qv_mobile_reqs   = "Platforms: iOS 15+ and Android 8.0+\nFramework: See product overview\nBackend: Node.js 20 API + PostgreSQL (Docker compose included)\nAdmin panel: React dashboard\nTooling: Xcode 16 / Android Studio Ladybug or newer\nLicense: Per published app (Unlimited: any number)\nUpdates: 12 months included\nSupport: Setup help + ticket portal";

$qv_products = array(
	array(
		'sku'       => 'QV-NEXUS',
		'name'      => 'Nexus CRM',
		'slug'      => 'nexus-crm',
		'cats'      => array( 'web-software', 'crm-tools' ),
		'type'      => 'variable',
		'tiers'     => array( 'standard' => 149, 'professional' => 299, 'unlimited' => 599 ),
		'featured'  => true,
		'palette'   => 'cyan',
		'mocks'     => array( 'qvimg_mock_kanban', 'qvimg_mock_dashboard', 'qvimg_mock_flow' ),
		'version'   => '4.2.0',
		'updated'   => '20260822',
		'short'     => 'Self-hosted CRM with visual pipelines, two-way email sync and revenue forecasting — built for B2B teams that have outgrown spreadsheets.',
		'intro'     => 'Nexus CRM gives sales teams a single, fast workspace for every lead, deal and conversation. Drag deals through customizable pipelines, log calls and emails automatically, and see exactly which opportunities will close this quarter.',
		'body'      => 'Because Nexus runs on your own infrastructure, customer data never leaves your servers — a hard requirement for many finance, healthcare and public-sector teams. It ships with a clean REST API, webhooks and native integrations for Slack, Stripe and QuickBooks.',
		'highlights' => "Visual deal pipelines with drag & drop\nTwo-way Gmail and Outlook sync\nRevenue forecasting & win-rate reports\nREST API, webhooks and SSO (SAML/OIDC)",
		'features'  => "Custom pipelines | Unlimited pipelines, stages and deal fields per team.\nEmail & calendar sync | Two-way sync with Google Workspace and Microsoft 365.\nForecasting | Weighted forecasts by rep, team and quarter.\nAutomation rules | Assign leads, create tasks and send follow-ups automatically.\nRole-based access | Granular permissions, audit log and SSO.\nImport in minutes | CSV and HubSpot/Pipedrive importers with field mapping.\nMobile-ready | Responsive UI plus PWA for reps on the road.\nDeveloper API | REST API, webhooks and a typed JavaScript SDK.",
		'reqs'      => $qv_software_reqs,
		'demo'      => 'https://demo.quantivista.com/nexus',
	),
	array(
		'sku'       => 'QV-LEADLOOP',
		'name'      => 'LeadLoop',
		'slug'      => 'leadloop',
		'cats'      => array( 'web-software', 'crm-tools' ),
		'type'      => 'simple',
		'price'     => 79,
		'palette'   => 'pink',
		'mocks'     => array( 'qvimg_mock_landing', 'qvimg_mock_dashboard' ),
		'version'   => '2.3.1',
		'updated'   => '20260805',
		'short'     => 'Smart lead-capture forms with progressive profiling and AI-free lead scoring. Drop one script on any website.',
		'intro'     => 'LeadLoop turns anonymous visitors into qualified leads. Build multi-step forms visually, ask for more details on return visits and score every lead on engagement and fit — then push hot leads to your CRM instantly.',
		'body'      => 'It works with any stack: a WordPress plugin, a Webflow/Framer embed or a single JavaScript snippet. GDPR consent logging and double opt-in are built in.',
		'highlights' => "Visual multi-step form builder\nProgressive profiling on return visits\nRule-based lead scoring\nNative Nexus CRM, HubSpot & Zapier sync",
		'features'  => "Form builder | Multi-step forms with conditional logic and 20+ field types.\nProgressive profiling | Never ask the same question twice.\nLead scoring | Weight page views, email opens and firmographics.\nConsent logging | GDPR/CCPA consent records with export.\nA/B testing | Test headlines, fields and CTAs with auto-winner.\nIntegrations | Nexus CRM, HubSpot, Salesforce, Mailchimp, Zapier.",
		'reqs'      => "Platforms: Any website (JS snippet) or WordPress 6.5+\nServer (self-hosted API): PHP 8.1+, MySQL 8\nLicense: Per website\nUpdates: 12 months included\nSupport: Ticket portal & email",
	),
	array(
		'sku'       => 'QV-FLOWPILOT',
		'name'      => 'FlowPilot',
		'slug'      => 'flowpilot',
		'cats'      => array( 'web-software', 'automation-tools' ),
		'type'      => 'variable',
		'tiers'     => array( 'standard' => 199, 'professional' => 399, 'unlimited' => 799 ),
		'featured'  => true,
		'palette'   => 'violet',
		'mocks'     => array( 'qvimg_mock_flow', 'qvimg_mock_dashboard', 'qvimg_mock_kanban' ),
		'version'   => '3.0.0',
		'updated'   => '20260901',
		'short'     => 'Visual workflow automation with 120+ integrations, automatic retries and full audit logs — the self-hosted alternative to per-task pricing.',
		'intro'     => 'FlowPilot lets operations and engineering teams automate processes across their tools without writing glue code. Connect triggers, conditions and actions on a visual canvas, test with live data and deploy in one click.',
		'body'      => 'Version 3.0 introduces branching, parallel steps, human-approval tasks and a new execution engine that handles millions of runs per day on a single node — with no per-task fees.',
		'highlights' => "Drag-and-drop workflow canvas\n120+ integrations and custom HTTP steps\nRetries, dead-letter queue & audit logs\nNo per-task pricing — run unlimited flows",
		'features'  => "Visual canvas | Branches, loops, parallel steps and sub-flows.\n120+ connectors | Slack, Google Workspace, Stripe, HubSpot, Postgres and more.\nCode steps | JavaScript and Python steps with npm/pip packages.\nApprovals | Human-in-the-loop approvals via email or Slack.\nObservability | Step-level logs, replay and alerting.\nScale | Horizontal workers with Redis-backed queues.",
		'reqs'      => $qv_software_reqs . "\nQueue: Redis 7+ (bundled in Docker compose)",
		'demo'      => 'https://demo.quantivista.com/flowpilot',
	),
	array(
		'sku'       => 'QV-TASKFORGE',
		'name'      => 'TaskForge',
		'slug'      => 'taskforge',
		'cats'      => array( 'web-software', 'automation-tools' ),
		'type'      => 'simple',
		'price'     => 59,
		'sale'      => 49,
		'palette'   => 'amber',
		'mocks'     => array( 'qvimg_mock_dashboard', 'qvimg_mock_flow' ),
		'version'   => '1.8.4',
		'updated'   => '20260716',
		'short'     => 'Cron and background-job scheduler with a real-time dashboard, retries and Slack alerts. Replace fragile crontabs in an afternoon.',
		'intro'     => 'TaskForge centralizes scheduled and background jobs across servers. Define jobs in YAML or the UI, watch runs in real time and get alerted the moment something fails.',
		'body'      => 'It supports distributed locks, timezone-aware schedules, concurrency limits and automatic retries with exponential backoff.',
		'highlights' => "Real-time job dashboard\nRetries with exponential backoff\nDistributed locks & concurrency limits\nSlack, email and PagerDuty alerts",
		'features'  => "Scheduling | Cron expressions, intervals and timezone-aware calendars.\nReliability | Retries, timeouts and distributed locks.\nVisibility | Live logs, run history and duration trends.\nAlerts | Slack, email, PagerDuty and webhooks.",
		'reqs'      => $qv_software_reqs,
	),
	array(
		'sku'       => 'QV-QUANTA',
		'name'      => 'Quanta Analytics',
		'slug'      => 'quanta-analytics',
		'cats'      => array( 'web-software', 'analytics-bi' ),
		'type'      => 'variable',
		'tiers'     => array( 'standard' => 129, 'professional' => 259, 'unlimited' => 519 ),
		'featured'  => true,
		'palette'   => 'teal',
		'mocks'     => array( 'qvimg_mock_dashboard', 'qvimg_mock_kanban', 'qvimg_mock_flow' ),
		'version'   => '5.1.2',
		'updated'   => '20260828',
		'short'     => 'Privacy-first product analytics — funnels, retention cohorts and shareable dashboards without cookies or consent banners.',
		'intro'     => 'Quanta Analytics shows how people actually use your product. Track events with a 2 KB script, build funnels and retention cohorts in seconds and share live dashboards with your whole team.',
		'body'      => 'Everything is cookieless and processed on your own servers, so you stay compliant with GDPR, CCPA and PECR without annoying consent pop-ups.',
		'highlights' => "Cookieless, GDPR-compliant tracking\nFunnels, cohorts and path analysis\nShareable live dashboards\nClickHouse engine for billions of events",
		'features'  => "Event tracking | 2 KB script, server SDKs and auto-capture.\nFunnels | Multi-step conversion funnels with breakdowns.\nRetention | Cohort tables and stickiness charts.\nDashboards | Drag-and-drop widgets, public share links.\nAlerts | Anomaly alerts on key metrics.\nData export | SQL access, CSV export and warehouse sync.",
		'reqs'      => $qv_software_reqs . "\nAnalytics store: ClickHouse 24+ (bundled)",
		'demo'      => 'https://demo.quantivista.com/quanta',
	),
	array(
		'sku'       => 'QV-CARTPULSE',
		'name'      => 'CartPulse',
		'slug'      => 'cartpulse',
		'cats'      => array( 'web-software', 'ecommerce-tools' ),
		'type'      => 'simple',
		'price'     => 89,
		'palette'   => 'green',
		'mocks'     => array( 'qvimg_mock_flow', 'qvimg_mock_dashboard' ),
		'version'   => '2.0.3',
		'updated'   => '20260810',
		'short'     => 'Abandoned-cart recovery and one-click upsells for WooCommerce and Shopify. Merchants recover 12–18% of lost revenue on average.',
		'intro'     => 'CartPulse follows up with shoppers who leave without buying — by email, SMS or web push — and suggests the right upsell at the right moment.',
		'body'      => 'Pre-built sequences, dynamic coupons and revenue attribution mean you can launch in under an hour and see exactly what each campaign earns.',
		'highlights' => "Email, SMS & web-push recovery flows\nDynamic, single-use coupons\nPost-purchase one-click upsells\nRevenue attribution dashboard",
		'features'  => "Recovery flows | Multi-channel sequences with smart timing.\nUpsells | Cart and post-purchase offers with A/B tests.\nCoupons | Unique, expiring codes generated per shopper.\nAttribution | Recovered revenue by campaign and channel.",
		'reqs'      => "Platforms: WooCommerce 9+ or Shopify\nServer: PHP 8.1+ (WooCommerce) — Shopify app is hosted\nLicense: Per store\nUpdates: 12 months included\nSupport: Ticket portal & email",
	),
	array(
		'sku'       => 'QV-PULSEFIT',
		'name'      => 'PulseFit — Fitness App Kit',
		'slug'      => 'pulsefit-fitness-app-kit',
		'cats'      => array( 'mobile-apps', 'health-fitness' ),
		'type'      => 'variable',
		'tiers'     => array( 'standard' => 249, 'professional' => 499, 'unlimited' => 999 ),
		'featured'  => true,
		'palette'   => 'green',
		'mocks'     => array( 'qvimg_mock_phones', 'qvimg_mock_dashboard' ),
		'version'   => '2.4.0',
		'updated'   => '20260818',
		'short'     => 'Complete Flutter fitness app for iOS and Android — workout plans, HealthKit/Google Fit sync, in-app subscriptions and a coach admin panel.',
		'intro'     => 'PulseFit is a launch-ready fitness product: a polished Flutter app for members, a React admin panel for coaches and a Node.js API that ties it all together.',
		'body'      => 'Members follow guided programs, log workouts and track progress with Apple Health and Google Fit sync. Coaches publish programs, message members and manage subscriptions through RevenueCat.',
		'highlights' => "Flutter app for iOS & Android\nHealthKit & Google Fit integration\nSubscriptions via RevenueCat\nCoach admin panel + Node.js API",
		'features'  => "Guided programs | Video workouts, rest timers and progressive overload.\nHealth sync | Steps, heart rate and workouts from HealthKit/Google Fit.\nSubscriptions | Monthly/annual plans and free trials via RevenueCat.\nCoach tools | Program builder, member chat and analytics.\nLocalization | 12 languages, RTL support.\nStore assets | App icons, screenshots and listing copy included.",
		'reqs'      => str_replace( 'See product overview', 'Flutter 3.24+', $qv_mobile_reqs ),
		'demo'      => 'https://demo.quantivista.com/pulsefit',
	),
	array(
		'sku'       => 'QV-SHIFTMATE',
		'name'      => 'ShiftMate — Team Scheduling App',
		'slug'      => 'shiftmate-team-scheduling-app',
		'cats'      => array( 'mobile-apps', 'business-productivity' ),
		'type'      => 'simple',
		'price'     => 179,
		'palette'   => 'blue',
		'mocks'     => array( 'qvimg_mock_phones', 'qvimg_mock_kanban' ),
		'version'   => '1.6.0',
		'updated'   => '20260726',
		'short'     => 'React Native shift-scheduling app with swaps, time-off requests and clock-in geofencing — plus a manager web dashboard.',
		'intro'     => 'ShiftMate helps restaurants, clinics and retail teams publish schedules, approve swaps and track hours from any phone.',
		'body'      => 'Managers build rotas on the web dashboard; staff get push notifications, request time off and clock in only when they are on site thanks to geofencing.',
		'highlights' => "React Native app for iOS & Android\nShift swaps & time-off approvals\nGeofenced clock-in/out\nPayroll-ready timesheet export",
		'features'  => "Scheduling | Drag-and-drop rota builder with templates.\nSwaps | Staff-initiated swaps with manager approval.\nTime tracking | Geofenced clock-in and break tracking.\nExports | Timesheets for Gusto, ADP and CSV.",
		'reqs'      => str_replace( 'See product overview', 'React Native 0.76+ (Expo)', $qv_mobile_reqs ),
	),
	array(
		'sku'       => 'QV-COURIER',
		'name'      => 'Courier — Delivery App Starter Kit',
		'slug'      => 'courier-delivery-app-starter-kit',
		'cats'      => array( 'mobile-apps', 'app-starter-kits' ),
		'type'      => 'variable',
		'tiers'     => array( 'standard' => 299, 'professional' => 599, 'unlimited' => 1199 ),
		'palette'   => 'amber',
		'mocks'     => array( 'qvimg_mock_phones', 'qvimg_mock_dashboard', 'qvimg_mock_flow' ),
		'version'   => '3.2.0',
		'updated'   => '20260812',
		'short'     => 'Three apps in one kit — customer, driver and dispatcher — with live tracking, route optimization and Stripe payouts.',
		'intro'     => 'Courier is everything you need to launch an on-demand delivery business: a customer ordering app, a driver app with turn-by-turn navigation and a dispatcher dashboard.',
		'body'      => 'Real-time tracking runs on WebSockets, routes are optimized with Google Maps or Mapbox and drivers are paid automatically through Stripe Connect.',
		'highlights' => "Customer, driver & dispatcher apps\nLive map tracking over WebSockets\nRoute optimization (Google Maps/Mapbox)\nStripe Connect driver payouts",
		'features'  => "Customer app | Ordering, scheduling, live tracking and ratings.\nDriver app | Job queue, navigation, proof-of-delivery photos.\nDispatcher | Live map, manual assignment and SLA alerts.\nPayments | Stripe checkout and automated driver payouts.\nNotifications | Push, SMS and email status updates.",
		'reqs'      => str_replace( 'See product overview', 'Flutter 3.24+', $qv_mobile_reqs ),
		'demo'      => 'https://demo.quantivista.com/courier',
	),
	array(
		'sku'       => 'QV-POCKETINV',
		'name'      => 'Pocket Invoice',
		'slug'      => 'pocket-invoice',
		'cats'      => array( 'mobile-apps', 'business-productivity' ),
		'type'      => 'simple',
		'price'     => 69,
		'sale'      => 59,
		'palette'   => 'cyan',
		'mocks'     => array( 'qvimg_mock_phones' ),
		'version'   => '1.3.2',
		'updated'   => '20260701',
		'short'     => 'Native Swift and Kotlin invoicing app for freelancers — estimates, invoices, receipts scanning and Stripe payment links.',
		'intro'     => 'Pocket Invoice is a clean, native invoicing app you can rebrand and publish. Create estimates, convert them to invoices and get paid by card with Stripe payment links.',
		'body'      => 'Receipts are scanned with on-device OCR and every document syncs securely through the included Firebase backend.',
		'highlights' => "Native Swift (iOS) & Kotlin (Android)\nEstimates, invoices & reminders\nOn-device receipt OCR\nStripe payment links",
		'features'  => "Documents | Estimates, invoices, credit notes and PDF export.\nPayments | Stripe payment links and payment status sync.\nExpenses | Receipt scanning with on-device OCR.\nReports | Profit & loss and tax summaries.",
		'reqs'      => str_replace( array( 'See product overview', 'Node.js 20 API + PostgreSQL (Docker compose included)' ), array( 'Swift 6 / Kotlin 2', 'Firebase (Auth, Firestore, Functions)' ), $qv_mobile_reqs ),
	),
	array(
		'sku'       => 'QV-SVC-LANDING',
		'name'      => 'Launch Landing Page',
		'slug'      => 'launch-landing-page',
		'cats'      => array( 'website-design', 'landing-pages' ),
		'type'      => 'service',
		'price'     => 1490,
		'palette'   => 'pink',
		'mocks'     => array( 'qvimg_mock_landing', 'qvimg_mock_pages' ),
		'short'     => 'A high-converting landing page — strategy, copy guidance, custom design and a lightning-fast build — delivered in 10 business days.',
		'intro'     => 'Perfect for product launches, paid campaigns and waitlists. We start with a 45-minute strategy call, map your offer and objections, then design and build a page engineered to convert.',
		'body'      => 'Every page scores 95+ on Lighthouse, includes analytics and A/B testing setup, and is handed over on the platform of your choice (WordPress, Webflow or static hosting).',
		'highlights' => "Delivered in 10 business days\nStrategy call + wireframe + 2 revision rounds\n95+ Lighthouse performance score\nAnalytics & A/B testing setup",
		'features'  => "Conversion strategy | Offer, audience and objection mapping before any pixels.\nCustom design | Unique design aligned to your brand — no templates.\nResponsive build | Pixel-perfect on mobile, tablet and desktop.\nSEO & speed | Semantic markup, meta tags and image optimization.\nTracking | GA4, Meta Pixel and conversion events configured.",
		'reqs'      => "Timeline: 10 business days after kickoff\nPages: 1 long-form landing page (up to 8 sections)\nRevisions: 2 rounds on design, 1 on build\nPlatform: WordPress, Webflow or static (your choice)\nIncludes: Wireframe, UI design, development, QA, launch\nAfter launch: 30 days of free fixes",
	),
	array(
		'sku'       => 'QV-SVC-BUSINESS',
		'name'      => 'Business Website Package',
		'slug'      => 'business-website-package',
		'cats'      => array( 'website-design', 'corporate-websites' ),
		'type'      => 'service',
		'price'     => 3900,
		'featured'  => true,
		'palette'   => 'cyan',
		'mocks'     => array( 'qvimg_mock_pages', 'qvimg_mock_landing' ),
		'short'     => 'A complete 8–12 page company website on WordPress — UX, custom design, CMS training and SEO foundations included.',
		'intro'     => 'Our most popular package for growing companies. We handle discovery, information architecture, custom UI design and development on a WordPress setup your team can easily update.',
		'body'      => 'You get a dedicated project manager, weekly progress demos and a launch checklist covering redirects, analytics, performance and accessibility (WCAG 2.2 AA).',
		'highlights' => "8–12 custom-designed pages\nEditable WordPress CMS + training\nWCAG 2.2 AA accessibility\nDedicated project manager",
		'features'  => "Discovery | Stakeholder interviews, competitor review and sitemap.\nUX & UI | Wireframes and high-fidelity designs for every template.\nCMS build | Flexible blocks your team can rearrange without code.\nSEO foundations | Redirect map, schema markup and technical SEO.\nAccessibility | Audited against WCAG 2.2 AA.\nTraining | Recorded CMS training session and documentation.",
		'reqs'      => "Timeline: 5–7 weeks\nPages: 8–12 unique templates\nRevisions: 3 design rounds, 2 build rounds\nPlatform: WordPress (hosting setup available)\nIncludes: Discovery, UX, UI, build, QA, launch, training\nAfter launch: 60 days of support",
	),
	array(
		'sku'       => 'QV-SVC-STORE',
		'name'      => 'E-commerce Store Build',
		'slug'      => 'ecommerce-store-build',
		'cats'      => array( 'website-design', 'ecommerce-stores' ),
		'type'      => 'service',
		'price'     => 7500,
		'palette'   => 'amber',
		'mocks'     => array( 'qvimg_mock_landing', 'qvimg_mock_pages', 'qvimg_mock_dashboard' ),
		'short'     => 'A conversion-optimized WooCommerce or Shopify store — custom design, product migration, payments, shipping and analytics.',
		'intro'     => 'From a first store to a replatforming project, we design and build online shops that load fast and sell more. We migrate products and customers, configure payments and shipping, and set up analytics end to end.',
		'body'      => 'The package includes a custom product page and checkout optimization pass based on our experience launching 60+ stores.',
		'highlights' => "WooCommerce or Shopify\nProduct & customer migration (up to 2,000 SKUs)\nPayments, shipping & tax configuration\nCheckout conversion optimization",
		'features'  => "Store design | Custom home, collection, product and cart templates.\nMigration | Products, customers and order history.\nPayments | Stripe, PayPal, Apple Pay and Google Pay.\nOperations | Shipping zones, taxes, transactional emails.\nAnalytics | GA4 e-commerce, Meta and TikTok pixels.",
		'reqs'      => "Timeline: 8–10 weeks\nCatalog: Up to 2,000 SKUs migrated\nPlatform: WooCommerce or Shopify\nRevisions: 3 design rounds, 2 build rounds\nIncludes: UX, UI, build, migration, integrations, QA, launch\nAfter launch: 90 days of support",
	),
	array(
		'sku'       => 'QV-SVC-CARE',
		'name'      => 'Website Care Plan — 6 Months',
		'slug'      => 'website-care-plan-6-months',
		'cats'      => array( 'website-design', 'corporate-websites' ),
		'type'      => 'service',
		'price'     => 1190,
		'palette'   => 'green',
		'mocks'     => array( 'qvimg_mock_dashboard' ),
		'short'     => 'Six months of updates, backups, uptime monitoring, security hardening and 4 hours of monthly design/dev time.',
		'intro'     => 'Keep your website fast, secure and fresh without hiring in-house. We handle updates, backups and monitoring, and you get monthly hours for small improvements.',
		'body'      => 'Every month you receive a report covering uptime, performance, security scans and completed tasks.',
		'highlights' => "Weekly updates & daily off-site backups\n24/7 uptime & security monitoring\n4 hours/month of design & dev time\nMonthly performance report",
		'features'  => "Updates | Core, theme and plugin updates tested on staging.\nBackups | Daily off-site backups with 30-day retention.\nMonitoring | Uptime, SSL and malware scanning.\nImprovements | 4 hours per month for content and design changes.",
		'reqs'      => "Duration: 6 months\nIncluded time: 4 hours/month (rollover 1 month)\nResponse time: Next business day (critical: 4 hours)\nPlatforms: WordPress, WooCommerce, Webflow\nReporting: Monthly PDF report",
	),
);


$qv_product_ids = array();
foreach ( $qv_products as $qv_index => $qv_p ) {
	$qv_pal     = $qv_palettes[ $qv_p['palette'] ];
	$qv_is_svc  = 'service' === $qv_p['type'];
	$qv_images  = array();
	foreach ( $qv_p['mocks'] as $qv_m => $qv_mock ) {
		$qv_images[] = qvs_image(
			"product-{$qv_p['slug']}-{$qv_m}-v2",
			"product-{$qv_p['slug']}-{$qv_m}-v2.jpg",
			fn() => call_user_func( $qv_mock, 1440, 900, explode( ' — ', $qv_p['name'] )[0], $qv_m % 2 ? $qv_pal[1] : $qv_pal[0], $qv_m % 2 ? $qv_pal[0] : $qv_pal[1], $qv_p['slug'] . $qv_m ),
			$qv_p['name'] . ( 0 === $qv_m ? ' — product preview' : " — screenshot {$qv_m}" )
		);
	}

	$qv_existing = wc_get_product_id_by_sku( $qv_p['sku'] );
	if ( 'variable' === $qv_p['type'] ) {
		$qv_product = $qv_existing ? new WC_Product_Variable( $qv_existing ) : new WC_Product_Variable();
	} else {
		$qv_product = $qv_existing ? new WC_Product_Simple( $qv_existing ) : new WC_Product_Simple();
	}

	$qv_list = '<ul>' . implode( '', array_map( fn( $l ) => '<li>' . esc_html( $l ) . '</li>', qv_seed_lines( $qv_p['highlights'] ) ) ) . '</ul>';
	$qv_desc = $qv_is_svc
		? "<p>{$qv_p['intro']}</p>\n<h3>What you get</h3>\n{$qv_list}\n<p>{$qv_p['body']}</p>\n<h3>How it works</h3>\n<ol><li><strong>Kickoff call</strong> — goals, audience, scope and timeline.</li><li><strong>Design</strong> — wireframes, then high-fidelity designs with revision rounds.</li><li><strong>Build &amp; QA</strong> — development, performance and accessibility testing.</li><li><strong>Launch &amp; support</strong> — go-live, training and post-launch fixes.</li></ol>\n<p>After checkout you will receive an onboarding questionnaire and a calendar link to book your kickoff call within 1 business day.</p>"
		: "<p>{$qv_p['intro']}</p>\n<h3>Why teams choose {$qv_p['name']}</h3>\n{$qv_list}\n<p>{$qv_p['body']}</p>\n<h3>What's in the package</h3>\n<p>Full source code, installation guide, API documentation and a license key for activation. Updates and support are included for 12 months, and your license never expires — the software keeps working even if you don't renew.</p>";

	$qv_product->set_props(
		array(
			'name'               => $qv_p['name'],
			'slug'               => $qv_p['slug'],
			// Sample catalog: kept as drafts to edit and publish, never shown as real products.
			'status'             => 'draft',
			'catalog_visibility' => 'visible',
			'featured'           => ! empty( $qv_p['featured'] ),
			'description'        => $qv_desc,
			'short_description'  => $qv_p['short'],
			'sku'                => $qv_p['sku'],
			'category_ids'       => array_map( fn( $s ) => $qv_cat_ids[ $s ], $qv_p['cats'] ),
			'image_id'           => $qv_images[0],
			'gallery_image_ids'  => array_slice( $qv_images, 1 ),
			'reviews_allowed'    => true,
			'virtual'            => true,
			'menu_order'         => $qv_index,
			'sold_individually'  => $qv_is_svc,
		)
	);

	if ( 'variable' !== $qv_p['type'] ) {
		$qv_product->set_regular_price( (string) $qv_p['price'] );
		$qv_product->set_sale_price( isset( $qv_p['sale'] ) ? (string) $qv_p['sale'] : '' );
		$qv_product->set_downloadable( ! $qv_is_svc );
		if ( ! $qv_is_svc ) {
			$qv_dl = new WC_Product_Download();
			$qv_dl->set_id( md5( $qv_p['sku'] . $qv_p['version'] ) );
			$qv_dl->set_name( "{$qv_p['name']} {$qv_p['version']} (.zip)" );
			$qv_dl->set_file( qvs_download_package( $qv_p['slug'], $qv_p['name'], $qv_p['version'] ) );
			$qv_product->set_downloads( array( $qv_dl ) );
			$qv_product->set_download_limit( -1 );
			$qv_product->set_download_expiry( -1 );
		}
	} else {
		$qv_attribute = new WC_Product_Attribute();
		$qv_attribute->set_id( $qv_attr_id );
		$qv_attribute->set_name( 'pa_license' );
		$qv_attribute->set_options( array_values( array_map( fn( $slug ) => $qv_tier_ids[ $slug ], array_keys( $qv_p['tiers'] ) ) ) );
		$qv_attribute->set_visible( false );
		$qv_attribute->set_variation( true );
		$qv_product->set_attributes( array( $qv_attribute ) );
		$qv_product->set_default_attributes( array( 'pa_license' => 'professional' ) );
	}

	$qv_product_id = $qv_product->save();
	update_post_meta( $qv_product_id, QVS_KEY, 'product-' . $qv_p['slug'] );

	if ( 'variable' === $qv_p['type'] ) {
		$qv_file = qvs_download_package( $qv_p['slug'], $qv_p['name'], $qv_p['version'] );
		foreach ( $qv_p['tiers'] as $qv_tier_slug => $qv_price ) {
			$qv_var_sku = $qv_p['sku'] . '-' . strtoupper( substr( $qv_tier_slug, 0, 3 ) );
			$qv_var_id  = wc_get_product_id_by_sku( $qv_var_sku );
			$qv_var     = $qv_var_id ? new WC_Product_Variation( $qv_var_id ) : new WC_Product_Variation();
			$qv_dl      = new WC_Product_Download();
			$qv_dl->set_id( md5( $qv_var_sku . $qv_p['version'] ) );
			$qv_dl->set_name( "{$qv_p['name']} {$qv_p['version']} (.zip)" );
			$qv_dl->set_file( $qv_file );
			$qv_var->set_props(
				array(
					'parent_id'       => $qv_product_id,
					'sku'             => $qv_var_sku,
					'regular_price'   => (string) $qv_price,
					'virtual'         => true,
					'downloadable'    => true,
					'downloads'       => array( $qv_dl ),
					'download_limit'  => -1,
					'download_expiry' => -1,
					'status'          => 'publish',
					'description'     => $qv_tiers[ $qv_tier_slug ][1],
					'menu_order'      => $qv_tiers[ $qv_tier_slug ][2],
				)
			);
			$qv_var->set_attributes( array( 'pa_license' => $qv_tier_slug ) );
			$qv_var->save();
		}
		WC_Product_Variable::sync( $qv_product_id );
	}

	$qv_meta = array(
		'qv_delivery'     => $qv_is_svc ? 'service' : 'download',
		'qv_highlights'   => $qv_p['highlights'],
		'qv_features'     => $qv_p['features'],
		'qv_requirements' => $qv_p['reqs'],
	);
	if ( ! $qv_is_svc ) {
		$qv_meta['qv_version']      = $qv_p['version'];
		$qv_meta['qv_last_updated'] = $qv_p['updated'];
	}
	if ( ! empty( $qv_p['demo'] ) ) {
		$qv_meta['qv_demo_url'] = $qv_p['demo'];
	}
	foreach ( $qv_meta as $qv_key => $qv_value ) {
		update_post_meta( $qv_product_id, $qv_key, $qv_value );
		update_post_meta( $qv_product_id, '_' . $qv_key, 'field_' . $qv_key );
	}

	// No invented reviews: remove the ones earlier demo seeds created.
	foreach ( get_comments( array( 'post_id' => $qv_product_id, 'meta_key' => QVS_KEY, 'fields' => 'ids' ) ) as $qv_cid ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		wp_delete_comment( $qv_cid, true );
	}
	WC_Comments::clear_transients( $qv_product_id );

	$qv_product_ids[ $qv_p['slug'] ] = $qv_product_id;
	WP_CLI::log( "    product: {$qv_p['name']} (#{$qv_product_id})" );
}

/**
 * Split multi-line strings (seed-local helper; theme has qv_lines()).
 */
function qv_seed_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', explode( "\n", $text ) ) ) );
}

/* ------------------------------------------------------------------------
 * Coupons
 * --------------------------------------------------------------------- */
$qv_coupons = array(
	'WELCOME10' => array( 'percent', 10, 0, 'Welcome offer: 10% off your first order.' ),
	'LAUNCH50'  => array( 'fixed_cart', 50, 300, '$50 off orders over $300.' ),
);
foreach ( $qv_coupons as $qv_code => $qv_c ) {
	$qv_coupon_id = wc_get_coupon_id_by_code( $qv_code );
	$qv_coupon    = new WC_Coupon( $qv_coupon_id ? $qv_coupon_id : 0 );
	$qv_coupon->set_props(
		array(
			'code'           => $qv_code,
			'discount_type'  => $qv_c[0],
			'amount'         => $qv_c[1],
			'minimum_amount' => $qv_c[2] ? (string) $qv_c[2] : '',
			'description'    => $qv_c[3],
			'individual_use' => true,
			'usage_limit_per_user' => 'WELCOME10' === $qv_code ? 1 : 0,
		)
	);
	$qv_coupon->save();
}

/* ------------------------------------------------------------------------
 * Clinic software plans for the English "Clinic Software" page. Service
 * products, hidden from the catalogue; prices are left empty for the owner
 * to set in Products (until then the page shows "Price on request").
 * --------------------------------------------------------------------- */
$qv_clinic_plans = array(
	'QV-CLINIC-BASIC' => array( 'Acupuncture Clinic Software — Basic setup', 'clinic-software-basic', 'Installed on your own host and domain, with SSL, nightly backups, your branding and one year of updates and support.' ),
	'QV-CLINIC-PRO'   => array( 'Acupuncture Clinic Software — Professional', 'clinic-software-professional', 'Everything in Basic setup, plus your services, doctors and working hours entered, payment gateway and email connected, and online staff training.' ),
	'QV-CLINIC-RENEW' => array( 'Acupuncture Clinic Software — Yearly renewal', 'clinic-software-renewal', 'Another year of updates, support and backup and security checks.' ),
);
foreach ( $qv_clinic_plans as $qv_sku => $qv_plan ) {
	$qv_existing = wc_get_product_id_by_sku( $qv_sku );
	$qv_product  = new WC_Product_Simple( $qv_existing ? $qv_existing : 0 );
	$qv_product->set_props(
		array(
			'name'               => $qv_plan[0],
			'slug'               => $qv_plan[1],
			'status'             => 'publish',
			'catalog_visibility' => 'hidden',
			'short_description'  => $qv_plan[2],
			'description'        => '<p>' . $qv_plan[2] . '</p><p>After checkout, we contact you within one business day to plan the installation. Please write your clinic name, domain and type of host in the project brief.</p>',
			'sku'                => $qv_sku,
			'virtual'            => true,
			'downloadable'       => false,
			'sold_individually'  => true,
			'reviews_allowed'    => false,
		)
	);
	$qv_id = $qv_product->save();
	update_post_meta( $qv_id, 'qv_delivery', 'service' );
	update_post_meta( $qv_id, QVS_KEY, 'product-' . $qv_plan[1] );
}
WP_CLI::log( '    clinic software plans: ' . implode( ', ', array_keys( $qv_clinic_plans ) ) );

update_option( 'qv_seed_product_ids', $qv_product_ids );
WP_CLI::log( '    coupons: ' . implode( ', ', array_keys( $qv_coupons ) ) );
