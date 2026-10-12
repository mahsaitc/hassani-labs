<?php
/**
 * Pages, policies, hero slides, testimonials, team, milestones, contact
 * form, navigation menus and widgets.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

$qv_theme_uri = get_template_directory_uri();

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
				<p class="full"><label>What do you need? *[select* your-service include_blank "A software product" "Custom features or integrations" "A mobile app" "Website design" "Something else"]</label></p>
				<p class="full"><label>Project details *[textarea* your-message placeholder "Goals, timeline, links to references…"]</label></p>
				<p class="full">[acceptance your-consent] I agree that Quantivista may process my details to respond to this inquiry, as described in the Privacy Policy. [/acceptance]</p>
				<p class="full">[submit class:btn class:btn--accent class:btn--lg "Send message"]</p>
				</div>'
			),
			'mail'     => array(
				'active'             => true,
				'subject'            => '[Quantivista] New inquiry from [your-name]',
				'sender'             => 'Quantivista Website <wordpress@quantivista.com>',
				'recipient'          => 'hello@quantivista.com',
				'body'               => "Name: [your-name]\nEmail: [your-email]\nCompany: [your-company]\nBudget: [your-budget]\nService: [your-service]\n\n[your-message]\n\n--\nSent from the contact form on [_site_url]",
				'additional_headers' => 'Reply-To: [your-email]',
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'mail_2'   => array(
				'active'             => true,
				'subject'            => 'We received your message — Quantivista',
				'sender'             => 'Quantivista <hello@quantivista.com>',
				'recipient'          => '[your-email]',
				'body'               => "Hi [your-name],\n\nThanks for reaching out! A member of our team will reply within one business day.\n\nYour message:\n[your-message]\n\n— The Quantivista team\n[_site_url]",
				'additional_headers' => 'Reply-To: hello@quantivista.com',
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'messages' => array_merge(
				wpcf7_messages() ? array_map( fn( $m ) => $m['default'], wpcf7_messages() ) : array(),
				array( 'mail_sent_ok' => 'Thanks! Your message has been sent — we\'ll be in touch within one business day.' )
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

$qv_story_image = qvs_image( 'about-story', 'about-story.jpg', fn() => qvimg_mock_dashboard( 1400, 1000, 'Quantivista', '22D3EE', '5B6CFF', 'about-story' ), 'Quantivista product dashboard on a large display' );

$qv_about = qvs_page(
	'page-about',
	'About Us',
	'about',
	'<p>Quantivista started in 2014 as a two-person studio in San Francisco, building web platforms for startups that needed senior engineering without the overhead of a large agency. Client by client, we noticed the same problems appearing again and again — CRMs that didn\'t fit, brittle automations, analytics that nobody trusted.</p>
	<p>So we started turning our best internal tools into products. Today more than 10,000 teams run Quantivista software, and our studio still designs and builds websites and apps for companies from seed stage to the Fortune 500.</p>
	<p>We are a remote-first team of 42 engineers, designers and strategists across North America and Europe, united by one belief: great software should be fast, accessible and a pleasure to use.</p>',
	array(
		'template'     => 'page-templates/about.php',
		'post_excerpt' => 'We design, build and sell software that helps ambitious teams move faster — and we\'ve been doing it for more than a decade.',
		'menu_order'   => 1,
	),
	array(
		'qv_story_title' => 'From client projects to products used by 10,000+ teams',
		'qv_story_image' => $qv_story_image,
		'qv_mission'     => 'To give growing companies enterprise-grade software and websites without enterprise complexity — honest pricing, clean code and a team that genuinely cares about outcomes.',
		'qv_vision'      => 'A world where every team, regardless of size, can build on reliable, accessible and privacy-respecting technology that they truly own.',
		'qv_values'      => "Craft over shortcuts | We sweat the details users never notice — until they're missing.\nOwnership | Customers own their data and their code. So do we: no black boxes.\nClarity | Fixed scopes, transparent roadmaps and plain-language communication.\nInclusion | Accessible products, a diverse team and a respectful culture.",
	)
);

$qv_blog = qvs_page(
	'page-blog',
	'Blog',
	'blog',
	'',
	array(
		'post_excerpt' => 'Engineering deep-dives, product updates and practical guides on software, mobile and web design from the Quantivista team.',
		'menu_order'   => 3,
	)
);

$qv_contact = qvs_page(
	'page-contact',
	'Contact',
	'contact',
	'<p>Whether you have a question about a product, need a custom integration or want a quote for a new website, we\'d love to hear from you. Share a few details and the right specialist will get back to you.</p>',
	array(
		'template'     => 'page-templates/contact.php',
		'post_excerpt' => 'Tell us about your project or ask us anything. Real humans reply within one business day.',
		'menu_order'   => 5,
	),
	array(
		'qv_form_shortcode' => $qv_form_shortcode,
		'qv_faq'            => "How quickly will you reply? | Within one business day — usually within a few hours during Pacific business hours.\nCan you customize a product for us? | Yes. Most customers start with a product license and add custom features, integrations or branding through our studio team.\nDo you work with companies outside the US? | Absolutely. About half of our clients are in Europe, Canada and Australia, and we schedule calls across time zones.\nWhat does a website project cost? | Our packages start at \$1,490 for a landing page and \$3,900 for a full business website. Larger builds are scoped with a fixed-price proposal.\nIs there a money-back guarantee? | Software purchases include a 14-day money-back guarantee. Service packages are billed by milestone so you only pay for approved work.",
	)
);

$qv_policies = qvs_page(
	'page-policies',
	'Policies',
	'policies',
	'<p>Transparency matters to us. These documents explain how we handle your data, your purchases and the software you license from Quantivista. If anything is unclear, contact <a href="mailto:legal@quantivista.com">legal@quantivista.com</a>.</p>',
	array(
		'template'     => 'page-templates/policies.php',
		'post_excerpt' => 'Terms, privacy, refunds and licensing — in plain language.',
		'menu_order'   => 4,
	)
);

$qv_disclaimer = '<p class="callout"><strong>Note:</strong> This document is placeholder content for the Quantivista demo site. Have it reviewed by qualified legal counsel before publishing.</p>';

$qv_terms = qvs_page(
	'page-terms',
	'Terms & Conditions',
	'terms-and-conditions',
	$qv_disclaimer . '
	<p>These Terms &amp; Conditions ("Terms") govern your use of quantivista.com and any purchase of software products or services from Quantivista, Inc. ("Quantivista", "we", "us"). By creating an account or placing an order you agree to these Terms.</p>
	<h2>1. Accounts</h2>
	<p>You must create an account and verify your email address before completing a purchase. You are responsible for keeping your credentials confidential and for all activity under your account. You must be at least 18 years old or have the consent of a legal guardian.</p>
	<h2>2. Products and licenses</h2>
	<p>Software products are licensed, not sold. Your license tier (Standard, Professional or Unlimited) determines the number of sites or apps on which you may use the software, as described in the <a href="/policies/software-license-agreement/">Software License Agreement</a>.</p>
	<h2>3. Service packages</h2>
	<p>Website design and development packages are delivered according to the scope, timeline and revision rounds described on the product page. Kickoff is scheduled after payment is received. Additional work outside the agreed scope is quoted separately.</p>
	<h2>4. Pricing and payment</h2>
	<ul><li>Prices are shown in US dollars and exclude applicable taxes unless stated.</li><li>Orders paid by bank transfer or invoice are activated once payment clears.</li><li>We may change prices at any time; changes never affect orders already placed.</li></ul>
	<h2>5. Delivery</h2>
	<p>Download links and license keys are available in <strong>My Account</strong> immediately after payment confirmation and are also sent by email.</p>
	<h2>6. Refunds</h2>
	<p>Refunds are handled under our <a href="/policies/refund-return-policy/">Refund &amp; Return Policy</a>.</p>
	<h2>7. Acceptable use</h2>
	<p>You may not use our products or website to break the law, infringe intellectual property, distribute malware or attempt to gain unauthorized access to systems.</p>
	<h2>8. Limitation of liability</h2>
	<p>To the maximum extent permitted by law, Quantivista is not liable for indirect, incidental or consequential damages. Our total liability for any claim is limited to the amount you paid for the product or service giving rise to the claim.</p>
	<h2>9. Governing law</h2>
	<p>These Terms are governed by the laws of the State of California, USA, without regard to conflict-of-law principles.</p>
	<h2>10. Contact</h2>
	<p>Questions about these Terms? Email <a href="mailto:legal@quantivista.com">legal@quantivista.com</a> or write to Quantivista, Inc., 548 Market Street, Suite 300, San Francisco, CA 94104.</p>',
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
	<p>This Privacy Policy explains what personal data Quantivista, Inc. collects, why we collect it and the choices you have. We follow the principles of the GDPR and the CCPA/CPRA regardless of where you live.</p>
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
	<p>You can access, correct, export or delete your data, and object to or restrict processing. Use <strong>My Account → Account Settings</strong> or email <a href="mailto:privacy@quantivista.com">privacy@quantivista.com</a>. You may also lodge a complaint with your local supervisory authority.</p>
	<h2>Contact</h2>
	<p>Data controller: Quantivista, Inc., 548 Market Street, Suite 300, San Francisco, CA 94104 — <a href="mailto:privacy@quantivista.com">privacy@quantivista.com</a>.</p>',
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
	<p>Email <a href="mailto:billing@quantivista.com">billing@quantivista.com</a> from the address on your account with your order number, or reply to your order confirmation email.</p>
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
	<p>This Software License Agreement ("Agreement") is between you and Quantivista, Inc. and applies to all software products purchased from quantivista.com.</p>
	<h2>1. Grant of license</h2>
	<p>Subject to payment, Quantivista grants you a perpetual, non-exclusive, non-transferable license to install and use the software according to your license tier:</p>
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
		'post_excerpt' => 'How you may use Quantivista software under the Standard, Professional and Unlimited tiers.',
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
		'title' => 'Software that moves your business forward',
		'meta'  => array(
			'qv_slide_eyebrow'     => 'FlowPilot 3.0 is here',
			'qv_slide_subheadline' => 'Production-ready web software, mobile app kits and conversion-focused websites — engineered by one senior team.',
			'qv_slide_video'       => $qv_theme_uri . '/assets/video/hero-loop.mp4',
			'qv_slide_cta_label'   => 'Explore Products',
			'qv_slide_cta_url'     => '/shop/',
			'qv_slide_cta2_label'  => 'Get a Quote',
			'qv_slide_cta2_url'    => '/contact/',
		),
	),
	array(
		'title' => 'Launch your mobile app in weeks, not months',
		'image' => fn() => qvimg_mock_phones( 1920, 1080, 'Mobile app kits', 'A78BFA', '22D3EE', 'slide-2' ),
		'meta'  => array(
			'qv_slide_eyebrow'     => 'Mobile app kits',
			'qv_slide_subheadline' => 'Flutter, React Native and native iOS/Android kits with backend, admin panel and store-ready assets included.',
			'qv_slide_cta_label'   => 'Browse app kits',
			'qv_slide_cta_url'     => '/product-category/mobile-apps/',
			'qv_slide_cta2_label'  => 'Talk to an engineer',
			'qv_slide_cta2_url'    => '/contact/',
		),
	),
	array(
		'title' => 'Websites designed to convert — at a fixed price',
		'image' => fn() => qvimg_mock_landing( 1920, 1080, 'Design studio', '2DD4BF', '5B6CFF', 'slide-3' ),
		'meta'  => array(
			'qv_slide_eyebrow'     => 'Design studio',
			'qv_slide_subheadline' => 'Strategy, UX, UI and a lightning-fast build. Landing pages from $1,490 and business websites from $3,900.',
			'qv_slide_cta_label'   => 'Get a Quote',
			'qv_slide_cta_url'     => '/contact/',
			'qv_slide_cta2_label'  => 'See packages',
			'qv_slide_cta2_url'    => '/product-category/website-design/',
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
 * Testimonials
 * --------------------------------------------------------------------- */
$qv_testimonials = array(
	array( 'Rachel Nguyen', 'VP of Sales', 'Brightline Logistics', 5, 'We moved 40 reps from spreadsheets to Nexus CRM in a single week. Forecast accuracy went from guesswork to within 5% — and our data stays on our own servers.' ),
	array( 'Marcus Feld', 'Head of Operations', 'Cobalt Finance', 5, 'FlowPilot replaced three different automation subscriptions. The audit logs alone made our compliance team fans for life.' ),
	array( 'Aisha Rahman', 'Founder', 'Northwind Labs', 5, 'Quantivista designed and built our new website in six weeks. Demo requests doubled in the first month and the site scores 98 on Lighthouse.' ),
	array( 'Tom Eriksen', 'CTO', 'Helix Health', 5, 'The PulseFit kit gave us a polished, accessible app foundation. We shipped to both stores in five weeks instead of six months.' ),
	array( 'Laura Chen', 'Product Manager', 'Lumen Retail', 4, 'Quanta Analytics finally gave us funnels we trust — without cookie banners. Support answered every question within hours.' ),
	array( 'Diego Alvarez', 'E-commerce Director', 'Orbital Outfitters', 5, 'Our store rebuild went live on time and on budget. CartPulse recovered $38k in abandoned carts in the first quarter.' ),
);
foreach ( $qv_testimonials as $qv_i => $qv_t ) {
	qvs_upsert_post(
		"testimonial-{$qv_i}",
		array( 'post_type' => 'qv_testimonial', 'post_title' => $qv_t[0], 'post_content' => $qv_t[4], 'menu_order' => $qv_i ),
		array( 'qv_role' => $qv_t[1], 'qv_company' => $qv_t[2], 'qv_rating' => $qv_t[3] )
	);
}

/* ------------------------------------------------------------------------
 * Team
 * --------------------------------------------------------------------- */
$qv_team = array(
	array( 'Elena Marsh', 'Co-founder & CEO', '22D3EE', '5B6CFF' ),
	array( 'Samuel Ortiz', 'Co-founder & CTO', 'A78BFA', 'F472B6' ),
	array( 'Nadia Karimi', 'Head of Design', '2DD4BF', '3B82F6' ),
	array( 'James Whitaker', 'Lead Backend Engineer', 'FBBF24', 'F97316' ),
	array( 'Yuki Sato', 'Mobile Engineering Lead', '34D399', '22D3EE' ),
	array( 'Olivia Bennett', 'Product Manager', 'F472B6', 'A78BFA' ),
	array( 'Kwame Mensah', 'Customer Success Lead', '60A5FA', '818CF8' ),
	array( 'Isabella Conti', 'Senior UX Researcher', '5B6CFF', '22D3EE' ),
);
foreach ( $qv_team as $qv_i => $qv_m ) {
	$qv_initials = implode( '', array_map( fn( $w ) => $w[0], explode( ' ', $qv_m[0] ) ) );
	$qv_mid      = qvs_upsert_post(
		"team-{$qv_i}",
		array( 'post_type' => 'qv_team', 'post_title' => $qv_m[0], 'menu_order' => $qv_i ),
		array(
			'qv_role'     => $qv_m[1],
			'qv_linkedin' => 'https://www.linkedin.com/in/' . sanitize_title( $qv_m[0] ),
		)
	);
	set_post_thumbnail( $qv_mid, qvs_image( "team-{$qv_i}", 'team-' . sanitize_title( $qv_m[0] ) . '.jpg', fn() => qvimg_avatar( 800, $qv_initials, $qv_m[2], $qv_m[3] ), "Portrait of {$qv_m[0]}" ) );
}

/* ------------------------------------------------------------------------
 * Milestones
 * --------------------------------------------------------------------- */
$qv_milestones = array(
	array( '2014', 'Quantivista is founded', 'Elena and Samuel start a two-person web studio in San Francisco, building platforms for early-stage startups.' ),
	array( '2016', 'First enterprise client', 'A Fortune 500 logistics company trusts us with its customer portal — our team grows to 12.' ),
	array( '2018', 'Our first product: TaskForge', 'We package our internal job scheduler as a product. 1,000 customers in the first year.' ),
	array( '2020', 'Remote-first and 100+ studio clients', 'We go fully remote, hire across two continents and launch Nexus CRM.' ),
	array( '2023', 'Mobile app kits', 'PulseFit and Courier bring our product approach to iOS and Android.' ),
	array( '2026', '10,000 teams and FlowPilot 3.0', 'Our products now power more than 10,000 teams in 70 countries.' ),
);
foreach ( $qv_milestones as $qv_i => $qv_ms ) {
	qvs_upsert_post(
		"milestone-{$qv_i}",
		array( 'post_type' => 'qv_milestone', 'post_title' => $qv_ms[1], 'post_content' => $qv_ms[2], 'menu_order' => $qv_i ),
		array( 'qv_year' => $qv_ms[0] )
	);
}

/* ------------------------------------------------------------------------
 * Menus
 * --------------------------------------------------------------------- */
function qvs_menu( $name ) {
	$menu = wp_get_nav_menu_object( $name );
	$id   = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	foreach ( (array) wp_get_nav_menu_items( $id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	return $id;
}

function qvs_menu_item( $menu_id, array $args, $parent = 0 ) {
	return wp_update_nav_menu_item(
		$menu_id,
		0,
		array_merge(
			array(
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			),
			$args
		)
	);
}

$qv_page_item = fn( $id, $title = '' ) => array(
	'menu-item-type'      => 'post_type',
	'menu-item-object'    => 'page',
	'menu-item-object-id' => $id,
	'menu-item-title'     => $title ? $title : get_the_title( $id ),
);
$qv_cat_item  = function ( $slug, $desc = '' ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	return array(
		'menu-item-type'        => 'taxonomy',
		'menu-item-object'      => 'product_cat',
		'menu-item-object-id'   => $term ? $term->term_id : 0,
		'menu-item-title'       => $term ? $term->name : $slug,
		'menu-item-description' => $desc,
	);
};

// Primary.
$qv_primary = qvs_menu( 'Primary' );
qvs_menu_item( $qv_primary, $qv_page_item( $qv_home ) );
qvs_menu_item( $qv_primary, $qv_page_item( $qv_about ) );
$qv_products_item = qvs_menu_item(
	$qv_primary,
	array(
		'menu-item-type'    => 'custom',
		'menu-item-title'   => 'Products',
		'menu-item-url'     => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
		'menu-item-classes' => 'mega-menu',
	)
);
$qv_mega = array(
	'web-software'   => array( 'CRM, automation & analytics platforms', array( 'crm-tools', 'automation-tools', 'analytics-bi', 'ecommerce-tools' ) ),
	'mobile-apps'    => array( 'iOS & Android app kits with full source', array( 'business-productivity', 'health-fitness', 'app-starter-kits' ) ),
	'website-design' => array( 'Fixed-price design & build packages', array( 'landing-pages', 'corporate-websites', 'ecommerce-stores' ) ),
);
foreach ( $qv_mega as $qv_slug => $qv_col ) {
	$qv_col_id = qvs_menu_item( $qv_primary, $qv_cat_item( $qv_slug, $qv_col[0] ), $qv_products_item );
	foreach ( $qv_col[1] as $qv_child ) {
		qvs_menu_item( $qv_primary, $qv_cat_item( $qv_child ), $qv_col_id );
	}
}
qvs_menu_item( $qv_primary, $qv_page_item( $qv_clinic_en, 'Clinic Software' ) );
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
foreach ( array( 'web-software', 'mobile-apps', 'website-design' ) as $qv_slug ) {
	qvs_menu_item( $qv_footer_products, $qv_cat_item( $qv_slug ) );
}
qvs_menu_item( $qv_footer_products, $qv_page_item( $qv_clinic_en, 'Acupuncture Clinic Software' ) );
qvs_menu_item( $qv_footer_products, $qv_page_item( $qv_clinic_fa, 'نرم‌افزار کلینیک (فارسی)' ) );
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
	5 => array( 'content' => "<!-- wp:html -->\n<div class=\"sidebar-cta\"><p class=\"eyebrow\">Custom build</p><h2 class=\"widget__title\">Need something tailored?</h2><p>We extend any product with custom features, integrations and branding.</p><a class=\"btn btn--primary btn--sm\" href=\"/contact/\">Talk to our team</a></div>\n<!-- /wp:html -->" ),
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

WP_CLI::log( "    pages, slides, testimonials, team, milestones, menus, widgets seeded (form: {$qv_form_shortcode})" );
