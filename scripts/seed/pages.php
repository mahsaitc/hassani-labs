<?php
/**
 * Pages, policies, hero slides, contact form, navigation menus and
 * widgets.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

$qv_theme_uri = get_template_directory_uri();
$qv_email     = function_exists( 'qv_mod' ) && qv_mod( 'contact_email' ) ? qv_mod( 'contact_email' ) : get_option( 'admin_email' );
$qv_host      = wp_parse_url( home_url(), PHP_URL_HOST );

/* ------------------------------------------------------------------------
 * Contact Form 7
 * --------------------------------------------------------------------- */
$qv_form_shortcode = '';
if ( class_exists( 'WPCF7_ContactForm' ) ) {
	$qv_form_id = qvs_find_by_key( 'cf7-project-inquiry', 'wpcf7_contact_form' );
	$qv_form    = $qv_form_id ? wpcf7_contact_form( $qv_form_id ) : WPCF7_ContactForm::get_template( array( 'title' => 'Project inquiry' ) );

	$qv_form->set_properties(
		array(
			'form'     => qvs_html(
				'<div class="form-grid">
				<p><label>Full name *[text* your-name autocomplete:name placeholder "Jane Cooper"]</label></p>
				<p><label>Work email *[email* your-email autocomplete:email placeholder "jane@company.com"]</label></p>
				<p><label>Company[text your-company autocomplete:organization placeholder "Company Inc."]</label></p>
				<p><label>Budget[select your-budget include_blank "Under $5k" "$5k – $15k" "$15k – $50k" "$50k+"]</label></p>
				<p class="full"><label>What do you need? *[select* your-service include_blank "Clinic software" "A custom web application" "A website" "Changes to a product I bought" "Something else"]</label></p>
				<p class="full"><label>Project details *[textarea* your-message placeholder "Goals, timeline, links to references…"]</label></p>
				<p class="full">[acceptance your-consent] I agree that Hassani Labs may process my details to respond to this inquiry, as described in the Privacy Policy. [/acceptance]</p>
				<p class="full">[submit class:btn class:btn--accent class:btn--lg "Send message"]</p>
				</div>'
			),
			'mail'     => array(
				'active'             => true,
				'subject'            => '[Hassani Labs] New inquiry from [your-name]',
				'sender'             => "Hassani Labs Website <wordpress@{$qv_host}>",
				'recipient'          => $qv_email,
				'body'               => "Name: [your-name]\nEmail: [your-email]\nCompany: [your-company]\nBudget: [your-budget]\nService: [your-service]\n\n[your-message]\n\n--\nSent from the contact form on [_site_url]",
				'additional_headers' => 'Reply-To: [your-email]',
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'mail_2'   => array(
				'active'             => true,
				'subject'            => 'I received your message — Hassani Labs',
				'sender'             => "Hassani Labs <wordpress@{$qv_host}>",
				'recipient'          => '[your-email]',
				'body'               => "Hi [your-name],\n\nThanks for reaching out. I read every message myself and will reply within two business days.\n\nYour message:\n[your-message]\n\nMahsa Hassani\nHassani Labs\n[_site_url]",
				'additional_headers' => "Reply-To: {$qv_email}",
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'messages' => array_merge(
				wpcf7_messages() ? array_map( fn( $m ) => $m['default'], wpcf7_messages() ) : array(),
				array( 'mail_sent_ok' => 'Thanks! Your message has been sent. I will reply within two business days.' )
			),
		)
	);
	$qv_form->save();
	update_post_meta( $qv_form->id(), QVS_KEY, 'cf7-project-inquiry' );
	// Numeric IDs remain supported by CF7 and avoid relying on the hash being loaded.
	$qv_form_shortcode = sprintf( '[contact-form-7 id="%d" title="Project inquiry"]', $qv_form->id() );
}

/* ------------------------------------------------------------------------
 * Pages
 * --------------------------------------------------------------------- */

/**
 * Upsert a page, adopting an existing page by path on first run.
 */
function qvs_page( $key, $title, $slug, $content, array $args = array(), array $meta = array(), $adopt_path = '' ) {
	if ( ! qvs_find_by_key( $key, 'page' ) && $adopt_path ) {
		$existing = get_page_by_path( $adopt_path );
		if ( $existing ) {
			update_post_meta( $existing->ID, QVS_KEY, $key );
		}
	}
	$template = $args['template'] ?? '';
	unset( $args['template'] );
	$id = qvs_upsert_post(
		$key,
		array_merge(
			array(
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => qvs_html( $content ),
			),
			$args
		),
		$meta
	);
	update_post_meta( $id, '_wp_page_template', $template ? $template : 'default' );
	return $id;
}

$qv_home = qvs_page( 'page-home', 'Home', 'home', '' );

$qv_about = qvs_page(
	'page-about',
	'About',
	'about',
	'<p>I am Mahsa Hassani. I trained and worked as a physician in General Medicine for more than twenty years, and I am also a computer engineer specialising in software design (Shiraz University). Hassani Labs is where those two halves meet: software I design, build and support myself.</p>
	<p>Medicine trains you to reason from symptoms to cause under incomplete information, to distrust the first plausible explanation, and to write notes precise enough that a stranger can act on them safely. I bring the same habits to software: clear boundaries, tests that prove the important rules, and documentation a new developer can follow.</p>
	<p>Day to day that means ASP.NET Core and C# on the back end, React and TypeScript on the front end, SQLite or PostgreSQL for data, and containers for anything that has to run the same way twice. My first commercial product is software for acupuncture clinics, built from my own clinic\'s needs.</p>',
	array(
		'template'     => 'page-templates/about.php',
		'post_excerpt' => 'Physician and computer engineer. I design, build and support software for clinics and growing businesses.',
		'menu_order'   => 1,
	),
	array(
		'qv_story_title' => 'Two disciplines, one habit of mind',
		'qv_story_image' => '',
		'qv_mission'     => 'To build software that clinics and small businesses can trust with their daily work: clear, secure, documented, and supported by the person who wrote it.',
		'qv_vision'      => 'Tools that respect the people who use them and the data they hold, in Persian and in English.',
		'qv_values'      => "Honesty | No invented numbers, no fake reviews. What the software does is what the page says.\nCare with data | Patient and customer data stays on the client's own server, encrypted where it matters and backed up.\nClarity | Fixed scopes, plain-language communication and written handover notes.\nOwnership | You own your data and your domain. I stay available for updates and support.",
	)
);

$qv_blog = qvs_page(
	'page-blog',
	'Blog',
	'blog',
	'',
	array(
		'post_excerpt' => 'Notes on software design, testing and building software for clinics, by Mahsa Hassani.',
		'menu_order'   => 3,
	)
);

$qv_contact = qvs_page(
	'page-contact',
	'Contact',
	'contact',
	'<p>Whether you have a question about the clinic software, need a custom web application or want a website, write to me with a few details. I read and answer every message myself.</p>',
	array(
		'template'     => 'page-templates/contact.php',
		'post_excerpt' => 'Tell me about your project or ask me anything. I reply within two business days.',
		'menu_order'   => 5,
	),
	array(
		'qv_form_shortcode' => $qv_form_shortcode,
		'qv_faq'            => "How quickly will you reply? | Within two business days, usually sooner.\nDo you work with clients outside Iran? | Yes. I work remotely in English and Persian and schedule calls across time zones.\nCan the clinic software be changed for my clinic? | Yes. Name, logo and colours are set from the admin panel; larger changes are quoted separately.\nHow is a custom project priced? | After a short call I send a written scope with a fixed price and milestones, so you only pay for approved work.",
	)
);

$qv_policies = qvs_page(
	'page-policies',
	'Policies',
	'policies',
	'<p>Transparency matters to us. These documents explain how we handle your data, your purchases and the software you license from Hassani Labs. If anything is unclear, contact <a href="mailto:' . esc_attr( $qv_email ) . '">' . esc_html( $qv_email ) . '</a>.</p>',
	array(
		'template'     => 'page-templates/policies.php',
		'post_excerpt' => 'Terms, privacy, refunds and licensing — in plain language.',
		'menu_order'   => 4,
	)
);

$qv_disclaimer = '<p class="callout"><strong>Note:</strong> This document is a draft template. Have it reviewed by qualified legal counsel, and complete the business details, before publishing.</p>';

$qv_terms = qvs_page(
	'page-terms',
	'Terms & Conditions',
	'terms-and-conditions',
	$qv_disclaimer . '
	<p>These Terms &amp; Conditions ("Terms") govern your use of this website and any purchase of software products or services from Hassani Labs ("Hassani Labs", "we", "us"). By creating an account or placing an order you agree to these Terms.</p>
	<h2>1. Accounts</h2>
	<p>You must create an account and verify your email address before completing a purchase. You are responsible for keeping your credentials confidential and for all activity under your account. You must be at least 18 years old or have the consent of a legal guardian.</p>
	<h2>2. Products and licenses</h2>
	<p>Software products are licensed, not sold. Your license tier (Standard, Professional or Unlimited) determines the number of sites or apps on which you may use the software, as described in the <a href="/policies/software-license-agreement/">Software License Agreement</a>.</p>
	<h2>3. Service packages</h2>
	<p>Website design and development packages are delivered according to the scope, timeline and revision rounds described on the product page. Kickoff is scheduled after payment is received. Additional work outside the agreed scope is quoted separately.</p>
	<h2>4. Pricing and payment</h2>
	<ul><li>Prices are shown in the currency of the page (US dollars on English pages, toman on Persian pages) and exclude applicable taxes unless stated.</li><li>Orders paid by bank transfer or invoice are activated once payment clears.</li><li>We may change prices at any time; changes never affect orders already placed.</li></ul>
	<h2>5. Delivery</h2>
	<p>Download links and license keys are available in <strong>My Account</strong> immediately after payment confirmation and are also sent by email.</p>
	<h2>6. Refunds</h2>
	<p>Refunds are handled under our <a href="/policies/refund-return-policy/">Refund &amp; Return Policy</a>.</p>
	<h2>7. Acceptable use</h2>
	<p>You may not use our products or website to break the law, infringe intellectual property, distribute malware or attempt to gain unauthorized access to systems.</p>
	<h2>8. Limitation of liability</h2>
	<p>To the maximum extent permitted by law, Hassani Labs is not liable for indirect, incidental or consequential damages. Our total liability for any claim is limited to the amount you paid for the product or service giving rise to the claim.</p>
	<h2>9. Governing law</h2>
	<p>These Terms are governed by the laws of [country of registration, to be completed].</p>
	<h2>10. Contact</h2>
	<p>Questions about these Terms? Email <a href="mailto:' . esc_attr( $qv_email ) . '">' . esc_html( $qv_email ) . '</a>.</p>',
	array(
		'post_parent'  => $qv_policies,
		'template'     => 'page-templates/policy.php',
		'post_excerpt' => 'The rules for using our website, buying software licenses and ordering service packages.',
		'menu_order'   => 1,
	)
);

$qv_privacy = qvs_page(
	'page-privacy',
	'Privacy Policy',
	'privacy-policy',
	$qv_disclaimer . '
	<p>This Privacy Policy explains what personal data Hassani Labs collects, why we collect it and the choices you have. We follow the principles of the GDPR and the CCPA/CPRA regardless of where you live.</p>
	<h2>Data we collect</h2>
	<ul><li><strong>Account data:</strong> name, email address, password (hashed) and billing details.</li><li><strong>Order data:</strong> products purchased, license keys, invoices and payment status. We do not store full card numbers.</li><li><strong>Communications:</strong> messages you send via our contact form, email or support portal.</li><li><strong>Usage data:</strong> anonymized, cookieless analytics about page views and performance.</li></ul>
	<h2>How we use data</h2>
	<p>We use personal data to provide and deliver purchases, verify accounts, provide support, send transactional emails, prevent fraud and — only with your consent — send our newsletter.</p>
	<h2>Legal bases</h2>
	<p>Performance of a contract (orders and accounts), legitimate interests (security and product improvement), consent (newsletter) and legal obligations (tax records).</p>
	<h2>Sharing</h2>
	<p>We share data only with processors that help us run the business — payment providers, email delivery and hosting — under data processing agreements. We never sell personal data.</p>
	<h2>Cookies</h2>
	<p>We use strictly necessary cookies for login, cart and security, plus a preference cookie for your wishlist. We do not use advertising cookies.</p>
	<h2>Retention</h2>
	<p>Account data is kept while your account is active. Order records are retained for seven years for tax purposes. Contact form messages are deleted after 24 months.</p>
	<h2>Your rights</h2>
	<p>You can access, correct, export or delete your data, and object to or restrict processing. Use <strong>My Account → Account Settings</strong> or email <a href="mailto:' . esc_attr( $qv_email ) . '">' . esc_html( $qv_email ) . '</a>. You may also lodge a complaint with your local supervisory authority.</p>
	<h2>Contact</h2>
	<p>Data controller: Hassani Labs (Mahsa Hassani), <a href="mailto:' . esc_attr( $qv_email ) . '">' . esc_html( $qv_email ) . '</a>.</p>',
	array(
		'post_parent'  => $qv_policies,
		'template'     => 'page-templates/policy.php',
		'post_excerpt' => 'What data we collect, why, how long we keep it and how to exercise your rights.',
		'menu_order'   => 2,
	),
	array(),
	'privacy-policy'
);

$qv_refund = qvs_page(
	'page-refund',
	'Refund & Return Policy',
	'refund-return-policy',
	$qv_disclaimer . '
	<p>We want you to be happy with every purchase. This policy explains when and how you can request a refund.</p>
	<h2>Software products — 14-day money-back guarantee</h2>
	<p>If a software product doesn\'t work as described, or simply isn\'t the right fit, request a refund within <strong>14 days</strong> of purchase. We may ask a few questions to help us improve, and occasionally offer to fix the issue first — but the choice is yours.</p>
	<ul><li>Refunds are issued to the original payment method within 5–10 business days.</li><li>The associated license key is deactivated when the refund is processed.</li><li>Renewals of updates &amp; support can be refunded within 14 days if no updates were downloaded.</li></ul>
	<h2>Service packages</h2>
	<p>Design and development packages are delivered in milestones:</p>
	<ol><li><strong>Before kickoff:</strong> full refund.</li><li><strong>After kickoff, before design approval:</strong> refund minus the discovery fee (20% of the package price).</li><li><strong>After design approval:</strong> approved milestones are non-refundable; remaining milestones are refunded.</li></ol>
	<h2>Upgrades</h2>
	<p>You can upgrade a license tier at any time by paying the difference. Downgrades are not refundable.</p>
	<h2>How to request a refund</h2>
	<p>Email <a href="mailto:' . esc_attr( $qv_email ) . '">' . esc_html( $qv_email ) . '</a> from the address on your account with your order number, or reply to your order confirmation email.</p>
	<h2>Returns</h2>
	<p>As all our products are digital or services, there are no physical returns.</p>',
	array(
		'post_parent'  => $qv_policies,
		'template'     => 'page-templates/policy.php',
		'post_excerpt' => '14-day money-back guarantee on software and milestone-based refunds for service packages.',
		'menu_order'   => 3,
	),
	array(),
	'refund_returns'
);

$qv_license = qvs_page(
	'page-license',
	'Software License Agreement',
	'software-license-agreement',
	$qv_disclaimer . '
	<p>This Software License Agreement ("Agreement") is between you and Hassani Labs and applies to all software products purchased from this website.</p>
	<h2>1. Grant of license</h2>
	<p>Subject to payment, Hassani Labs grants you a perpetual, non-exclusive, non-transferable license to install and use the software according to your license tier:</p>
	<table><thead><tr><th>Tier</th><th>Production installs</th><th>Updates &amp; support</th><th>White-label</th></tr></thead><tbody><tr><td>Standard</td><td>1 site or app</td><td>12 months</td><td>No</td></tr><tr><td>Professional</td><td>Up to 5</td><td>12 months, priority</td><td>No</td></tr><tr><td>Unlimited</td><td>Unlimited</td><td>12 months, 24/7</td><td>Yes</td></tr></tbody></table>
	<p>Development, staging and testing environments do not count toward your limit.</p>
	<h2>2. Source code</h2>
	<p>Where source code is provided you may modify it for your own use. You may not resell, sublicense or redistribute the software or modified versions as a competing product.</p>
	<h2>3. License keys</h2>
	<p>Each purchase includes license keys available under <strong>My Account → Licenses</strong>. Keys enable automatic updates and support access. The software continues to work if your update period expires.</p>
	<h2>4. Client work</h2>
	<p>You may use the software to build projects for clients, provided each production install is covered by a license and the client does not receive the right to redistribute the software.</p>
	<h2>5. Third-party components</h2>
	<p>The software may include open-source components licensed under their own terms, listed in the <code>THIRD_PARTY_NOTICES</code> file.</p>
	<h2>6. Warranty disclaimer</h2>
	<p>The software is provided "as is" without warranties of any kind, except as required by law and our 14-day money-back guarantee.</p>
	<h2>7. Termination</h2>
	<p>This license terminates automatically if you breach this Agreement or receive a refund. Upon termination you must stop using and delete the software.</p>',
	array(
		'post_parent'  => $qv_policies,
		'template'     => 'page-templates/policy.php',
		'post_excerpt' => 'How you may use Hassani Labs software under the Standard, Professional and Unlimited tiers.',
		'menu_order'   => 4,
	)
);

$qv_wishlist = qvs_page(
	'page-wishlist',
	'Wishlist',
	'wishlist',
	'<!-- wp:shortcode -->[qv_wishlist]<!-- /wp:shortcode -->',
	array( 'post_excerpt' => 'Products you saved for later.' )
);

$qv_clinic_fa = qvs_page(
	'page-clinic-software-fa',
	'نرم‌افزار کلینیک طب سوزنی',
	'clinic-software-fa',
	'',
	array(
		'template'     => 'page-templates/clinic-software.php',
		'post_excerpt' => 'سایت، نوبت‌دهی آنلاین و پرونده الکترونیک بیمار برای کلینیک‌های طب سوزنی، نصب‌شده روی هاست خود کلینیک.',
	)
);

$qv_clinic_en = qvs_page(
	'page-clinic-software',
	'Acupuncture Clinic Software',
	'clinic-software',
	'',
	array(
		'template'     => 'page-templates/clinic-software-en.php',
		'post_excerpt' => 'Website, online booking and electronic patient records for acupuncture clinics, installed on the clinic\'s own host.',
	)
);

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $qv_home );
update_option( 'page_for_posts', $qv_blog );
update_option( 'wp_page_for_privacy_policy', $qv_privacy );
update_option( 'woocommerce_terms_page_id', $qv_terms );
update_option( 'qv_wishlist_page_id', $qv_wishlist );

/* ------------------------------------------------------------------------
 * Hero slides
 * --------------------------------------------------------------------- */
$qv_slides = array(
	array(
		'title' => 'From a real problem to a smart software solution',
		'meta'  => array(
			'qv_slide_eyebrow'     => 'Dr. Mahsa Hassani | Physician & Software Developer',
			'qv_slide_subheadline' => 'Websites, custom software and digital tools, built with an analytical, structured approach that fits your real needs.',
			'qv_slide_video'       => $qv_theme_uri . '/assets/video/hero-loop.mp4',
			'qv_slide_cta_label'   => 'See my work',
			'qv_slide_cta_url'     => '/work/',
			'qv_slide_cta2_label'  => 'Get in touch',
			'qv_slide_cta2_url'    => '/contact/',
		),
	),
	array(
		'title' => 'Software for acupuncture clinics',
		'image' => fn() => qvimg_mock_dashboard( 1920, 1080, 'Clinic software', '22D3EE', '5B6CFF', 'slide-1' ),
		'meta'  => array(
			'qv_slide_eyebrow'     => 'Product',
			'qv_slide_subheadline' => 'Website, online booking and electronic patient records, installed on your clinic\'s own host.',
			'qv_slide_cta_label'   => 'Learn more',
			'qv_slide_cta_url'     => '/clinic-software/',
			'qv_slide_cta2_label'  => 'Ask a question',
			'qv_slide_cta2_url'    => '/contact/',
		),
	),
	array(
		'title' => 'A custom web application, from scope to support',
		'image' => fn() => qvimg_mock_landing( 1920, 1080, 'Custom software', '2DD4BF', '5B6CFF', 'slide-2' ),
		'meta'  => array(
			'qv_slide_eyebrow'     => 'Services',
			'qv_slide_subheadline' => 'ASP.NET Core and React, with tests, documentation and a fixed-price scope.',
			'qv_slide_cta_label'   => 'Get a quote',
			'qv_slide_cta_url'     => '/contact/',
			'qv_slide_cta2_label'  => 'See my work',
			'qv_slide_cta2_url'    => '/work/',
		),
	),
);
foreach ( $qv_slides as $qv_i => $qv_slide ) {
	$qv_sid = qvs_upsert_post( "slide-{$qv_i}", array( 'post_type' => 'qv_slide', 'post_title' => $qv_slide['title'], 'menu_order' => $qv_i ), $qv_slide['meta'] );
	if ( isset( $qv_slide['image'] ) ) {
		set_post_thumbnail( $qv_sid, qvs_image( "slide-{$qv_i}", "hero-slide-{$qv_i}.jpg", $qv_slide['image'], '' ) );
	}
}

/* ------------------------------------------------------------------------
 * Remove demo testimonials and team from earlier seeds (milestones come
 * from the portfolio section). Add real testimonials in the admin.
 * --------------------------------------------------------------------- */
foreach ( array( 'qv_testimonial' => 'testimonial-', 'qv_team' => 'team-' ) as $qv_type => $qv_prefix ) {
	for ( $qv_i = 0; $qv_i < 12; $qv_i++ ) {
		$qv_old = qvs_find_by_key( $qv_prefix . $qv_i, $qv_type );
		if ( $qv_old ) {
			wp_delete_post( $qv_old, true );
		}
	}
}

/* ------------------------------------------------------------------------
 * Menus
 * --------------------------------------------------------------------- */
$qv_page_item = fn( $id, $title = '' ) => array(
	'menu-item-type'      => 'post_type',
	'menu-item-object'    => 'page',
	'menu-item-object-id' => $id,
	'menu-item-title'     => $title ? $title : get_the_title( $id ),
);

// Primary.
$qv_primary = qvs_menu( 'Primary' );
qvs_menu_item( $qv_primary, $qv_page_item( $qv_home ) );
qvs_menu_item( $qv_primary, $qv_page_item( $qv_about ) );
qvs_menu_item(
	$qv_primary,
	array(
		'menu-item-type'   => 'post_type_archive',
		'menu-item-object' => 'qv_project',
		'menu-item-title'  => 'Work',
	)
);
$qv_products_item = qvs_menu_item(
	$qv_primary,
	array(
		'menu-item-type'  => 'custom',
		'menu-item-title' => 'Products',
		'menu-item-url'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
	)
);
qvs_menu_item( $qv_primary, $qv_page_item( $qv_clinic_en, 'Acupuncture Clinic Software' ), $qv_products_item );
qvs_menu_item( $qv_primary, $qv_page_item( $qv_blog ) );
$qv_policies_item = qvs_menu_item( $qv_primary, $qv_page_item( $qv_policies ) );
foreach ( array( $qv_terms, $qv_privacy, $qv_refund, $qv_license ) as $qv_pid ) {
	qvs_menu_item( $qv_primary, $qv_page_item( $qv_pid ), $qv_policies_item );
}
qvs_menu_item( $qv_primary, $qv_page_item( $qv_contact ) );

// Footer.
$qv_footer_quick = qvs_menu( 'Footer — Company' );
foreach ( array( $qv_about, $qv_blog, $qv_contact ) as $qv_pid ) {
	qvs_menu_item( $qv_footer_quick, $qv_page_item( $qv_pid ) );
}
if ( function_exists( 'wc_get_page_id' ) ) {
	qvs_menu_item( $qv_footer_quick, $qv_page_item( wc_get_page_id( 'myaccount' ), 'My Account' ) );
}
qvs_menu_item( $qv_footer_quick, $qv_page_item( $qv_wishlist ) );

$qv_footer_products = qvs_menu( 'Footer — Products' );
qvs_menu_item( $qv_footer_products, $qv_page_item( $qv_clinic_en, 'Acupuncture Clinic Software' ) );
qvs_menu_item(
	$qv_footer_products,
	array(
		'menu-item-type'  => 'custom',
		'menu-item-title' => 'All products',
		'menu-item-url'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
	)
);

$qv_footer_policies = qvs_menu( 'Footer — Policies' );
foreach ( array( $qv_terms, $qv_privacy, $qv_refund, $qv_license ) as $qv_pid ) {
	qvs_menu_item( $qv_footer_policies, $qv_page_item( $qv_pid ) );
}

set_theme_mod(
	'nav_menu_locations',
	array(
		'primary'         => $qv_primary,
		'footer_quick'    => $qv_footer_quick,
		'footer_products' => $qv_footer_products,
		'footer_policies' => $qv_footer_policies,
	)
);

/* ------------------------------------------------------------------------
 * Widgets (block widgets)
 * --------------------------------------------------------------------- */
$qv_blocks = array(
	2 => array( 'content' => '<!-- wp:search {"label":"Search articles","buttonText":"Search","placeholder":"Search articles…"} /-->' ),
	3 => array( 'content' => "<!-- wp:heading {\"level\":2,\"className\":\"widget__title\"} -->\n<h2 class=\"wp-block-heading widget__title\">Categories</h2>\n<!-- /wp:heading -->\n<!-- wp:categories {\"showPostCounts\":true} /-->" ),
	4 => array( 'content' => "<!-- wp:heading {\"level\":2,\"className\":\"widget__title\"} -->\n<h2 class=\"wp-block-heading widget__title\">Popular tags</h2>\n<!-- /wp:heading -->\n<!-- wp:tag-cloud {\"smallestFontSize\":\"0.8rem\",\"largestFontSize\":\"0.8rem\"} /-->" ),
	5 => array( 'content' => "<!-- wp:html -->\n<div class=\"sidebar-cta\"><p class=\"eyebrow\">Custom build</p><h2 class=\"widget__title\">Need something tailored?</h2><p>I extend any product with custom features, integrations and branding.</p><a class=\"btn btn--primary btn--sm\" href=\"/contact/\">Get in touch</a></div>\n<!-- /wp:html -->" ),
	'_multiwidget' => 1,
);
update_option( 'widget_block', $qv_blocks );
update_option(
	'sidebars_widgets',
	array(
		'wp_inactive_widgets' => array(),
		'blog-sidebar'        => array( 'block-2', 'block-3', 'block-4' ),
		'shop-sidebar'        => array( 'block-5' ),
		'footer-extra'        => array(),
		'array_version'       => 3,
	)
);

WP_CLI::log( "    pages, slides, menus, widgets seeded (form: {$qv_form_shortcode})" );
