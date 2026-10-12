<?php
/**
 * Core, WooCommerce, payment, email and SEO settings.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

/* ---------- WordPress ---------- */
$qv_options = array(
	'blogname'               => 'Hassani Labs',
	'blogdescription'        => 'Software for clinics and growing businesses, by Mahsa Hassani',
	'timezone_string'        => 'Asia/Tehran',
	'date_format'            => 'F j, Y',
	'time_format'            => 'g:i a',
	'start_of_week'          => 1,
	'default_comment_status' => 'open',
	'comment_moderation'     => 1,
	'comment_previously_approved' => 1,
	'show_avatars'           => 1,
	'avatar_default'         => 'mystery',
	'users_can_register'     => 0,
	'thread_comments'        => 1,
	'posts_per_page'         => 9,
	'uploads_use_yearmonth_folders' => 1,
);
foreach ( $qv_options as $qv_key => $qv_value ) {
	update_option( $qv_key, $qv_value );
}

/* ---------- WooCommerce: store ---------- */
$qv_wc = array(
	// Store address: fill in WooCommerce > Settings once the business is registered.
	'woocommerce_store_address'                       => '',
	'woocommerce_store_address_2'                     => '',
	'woocommerce_store_city'                          => '',
	'woocommerce_store_postcode'                      => '',
	'woocommerce_default_country'                     => 'IR',
	'woocommerce_currency'                            => 'USD',
	'woocommerce_currency_pos'                        => 'left',
	'woocommerce_price_thousand_sep'                  => ',',
	'woocommerce_price_decimal_sep'                   => '.',
	'woocommerce_price_num_decimals'                  => 0,
	'woocommerce_allowed_countries'                   => 'all',
	'woocommerce_ship_to_countries'                   => 'disabled',
	'woocommerce_calc_taxes'                          => 'no',
	'woocommerce_enable_coupons'                      => 'yes',
	'woocommerce_manage_stock'                        => 'no',
	'woocommerce_enable_ajax_add_to_cart'             => 'yes',
	'woocommerce_cart_redirect_after_add'             => 'no',
	'woocommerce_shop_page_display'                   => '',
	'woocommerce_category_archive_display'            => '',
	'woocommerce_thumbnail_cropping'                  => 'custom',
	'woocommerce_thumbnail_cropping_custom_width'     => 16,
	'woocommerce_thumbnail_cropping_custom_height'    => 10,
	// Accounts & checkout: registration required, no guest checkout.
	'woocommerce_enable_guest_checkout'               => 'no',
	'woocommerce_enable_checkout_login_reminder'      => 'yes',
	'woocommerce_enable_signup_and_login_from_checkout' => 'no',
	'woocommerce_enable_myaccount_registration'       => 'yes',
	'woocommerce_registration_generate_username'      => 'yes',
	'woocommerce_registration_generate_password'      => 'no',
	// Reviews.
	'woocommerce_enable_reviews'                      => 'yes',
	'woocommerce_review_rating_verification_label'    => 'yes',
	'woocommerce_review_rating_verification_required' => 'no',
	'woocommerce_enable_review_rating'                => 'yes',
	'woocommerce_review_rating_required'              => 'yes',
	// Digital downloads.
	'woocommerce_file_download_method'                => 'force',
	'woocommerce_downloads_require_login'             => 'yes',
	'woocommerce_downloads_grant_access_after_payment' => 'yes',
	'woocommerce_downloads_redirect_fallback_allowed' => 'no',
	'woocommerce_downloads_add_hash_to_filename'      => 'yes',
	// Launch the store (WooCommerce starts new stores in "coming soon" mode).
	'woocommerce_coming_soon'                         => 'no',
	'woocommerce_store_pages_only'                    => 'no',
	// Admin noise.
	'woocommerce_onboarding_profile'                  => array( 'skipped' => true ),
	'woocommerce_task_list_hidden'                    => 'yes',
	'woocommerce_task_list_complete'                  => 'yes',
	'woocommerce_extended_task_list_hidden'           => 'yes',
	'woocommerce_show_marketplace_suggestions'        => 'no',
	'woocommerce_allow_tracking'                      => 'no',
	'woocommerce_analytics_enabled'                   => 'yes',
	// Emails.
	'woocommerce_email_from_name'                     => 'Hassani Labs',
	'woocommerce_email_from_address'                  => get_option( 'admin_email' ),
	'woocommerce_email_base_color'                    => '#3A54E8',
	'woocommerce_email_background_color'              => '#F5F7FC',
	'woocommerce_email_body_background_color'         => '#FFFFFF',
	'woocommerce_email_text_color'                    => '#0B1026',
	'woocommerce_email_footer_text'                   => '{site_title} — Software for clinics and growing businesses',
);
foreach ( $qv_wc as $qv_key => $qv_value ) {
	update_option( $qv_key, $qv_value );
}

/* ---------- Payment gateways (offline; add Stripe/PayPal keys later) ---------- */
update_option(
	'woocommerce_bacs_settings',
	array(
		'enabled'         => 'yes',
		'title'           => 'Direct bank transfer',
		'description'     => 'Pay directly into our bank account using your order number as the reference. Downloads and license keys are released as soon as the transfer clears (usually 1–2 business days).',
		'instructions'    => 'Please use your order number as the payment reference. Your downloads, license keys or project kick-off will be released once the funds have cleared.',
		'account_details' => '',
	)
);
update_option(
	'woocommerce_bacs_accounts',
	array(
		array(
			// Placeholder: enter the real account in WooCommerce > Settings > Payments.
			'account_name'   => 'Hassani Labs',
			'account_number' => '',
			'bank_name'      => '',
			'sort_code'      => '',
			'iban'           => '',
			'bic'            => '',
		),
	)
);
update_option(
	'woocommerce_cheque_settings',
	array(
		'enabled'      => 'yes',
		'title'        => 'Invoice — Net 15 (business customers)',
		'description'  => 'We email a PDF invoice payable within 15 days. Access is activated when payment is received.',
		'instructions' => 'Your invoice will arrive by email within one business day.',
	)
);
update_option( 'woocommerce_cod_settings', array( 'enabled' => 'no' ) );
update_option( 'woocommerce_gateway_order', array( 'bacs' => 0, 'cheque' => 1 ) );

/* ---------- WooCommerce pages: classic shortcodes (theme-styled) ---------- */
foreach ( array( 'cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]', 'myaccount' => '[woocommerce_my_account]' ) as $qv_page => $qv_shortcode ) {
	$qv_page_id = wc_get_page_id( $qv_page );
	if ( $qv_page_id > 0 ) {
		wp_update_post(
			array(
				'ID'           => $qv_page_id,
				'post_content' => "<!-- wp:shortcode -->\n{$qv_shortcode}\n<!-- /wp:shortcode -->",
			)
		);
	}
}
$qv_account_page = wc_get_page_id( 'myaccount' );
if ( $qv_account_page > 0 ) {
	wp_update_post( array( 'ID' => $qv_account_page, 'post_title' => 'My Account' ) );
}

/* ---------- Approved download directory for seeded files ---------- */
$qv_uploads = wp_upload_dir();
try {
	$qv_dirs = wc_get_container()->get( \Automattic\WooCommerce\Internal\ProductDownloads\ApprovedDirectories\Register::class );
	$qv_dirs->add_approved_directory( trailingslashit( $qv_uploads['baseurl'] ) . 'woocommerce_uploads/' );
} catch ( \Throwable $e ) {
	WP_CLI::debug( 'Approved directory: ' . $e->getMessage() );
}

/* ---------- Yoast SEO ---------- */
if ( defined( 'WPSEO_VERSION' ) ) {
	qvs_update_option_merge(
		'wpseo_titles',
		array(
			'company_or_person'  => 'company',
			'company_name'       => 'Hassani Labs',
			'website_name'       => 'Hassani Labs',
			'breadcrumbs-enable' => true,
			'breadcrumbs-sep'    => '/',
			'breadcrumbs-home'   => 'Home',
			'separator'          => 'sc-pipe',
			'title-home-wpseo'   => '%%sitename%% %%sep%% Clinic Software, Web Applications & Websites',
			'metadesc-home-wpseo' => 'Hassani Labs is the studio of Mahsa Hassani, physician and software engineer: acupuncture clinic software, custom ASP.NET Core and React applications, and websites.',
		)
	);
	qvs_update_option_merge( 'wpseo', array( 'enable_xml_sitemap' => true, 'show_onboarding_notice' => false ) );
	qvs_update_option_merge(
		'wpseo_social',
		array(
			'twitter_site'  => '',
			'other_social_urls' => array( 'https://www.linkedin.com/in/mahsahassani', 'https://github.com/mahsaitc' ),
		)
	);
}

/* ---------- Tidy defaults ---------- */
foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $qv_slug => $qv_type ) {
	$qv_default = get_page_by_path( $qv_slug, OBJECT, $qv_type );
	if ( $qv_default && ! get_post_meta( $qv_default->ID, QVS_KEY, true ) ) {
		wp_delete_post( $qv_default->ID, true );
	}
}

WP_CLI::log( '    settings applied' );
