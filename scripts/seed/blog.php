<?php
/**
 * Blog: authors with bios/avatars, categories, tags, articles with cover
 * images, and a few approved comments.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------------
 * Authors (reuse team avatars when the pages section has run)
 * --------------------------------------------------------------------- */
$qv_authors = array(
	'elena'  => array( 'elena.marsh', 'Elena', 'Marsh', 'Co-founder & CEO', 0, 'Elena co-founded Quantivista in 2014 after a decade leading product teams at B2B SaaS companies. She writes about building durable software businesses, pricing and remote leadership.' ),
	'samuel' => array( 'samuel.ortiz', 'Samuel', 'Ortiz', 'Co-founder & CTO', 1, 'Samuel leads engineering at Quantivista. He has shipped distributed systems for logistics and fintech, and cares deeply about boring, reliable infrastructure.' ),
	'nadia'  => array( 'nadia.karimi', 'Nadia', 'Karimi', 'Head of Design', 2, 'Nadia runs the design studio, where she has led more than 150 website and product launches. She speaks regularly about accessible design systems and conversion-focused UX.' ),
	'yuki'   => array( 'yuki.sato', 'Yuki', 'Sato', 'Mobile Engineering Lead', 4, 'Yuki builds the PulseFit and Courier app kits. Before Quantivista he shipped consumer apps with more than 5 million downloads.' ),
);

$qv_author_ids = array();
foreach ( $qv_authors as $qv_key => $qv_a ) {
	$qv_user = get_user_by( 'login', $qv_a[0] );
	$qv_uid  = $qv_user ? $qv_user->ID : wp_insert_user(
		array(
			'user_login' => $qv_a[0],
			'user_email' => $qv_a[0] . '@quantivista.com',
			'user_pass'  => wp_generate_password( 24 ),
			'role'       => 'author',
		)
	);
	if ( is_wp_error( $qv_uid ) ) {
		WP_CLI::warning( 'Author ' . $qv_a[0] . ': ' . $qv_uid->get_error_message() );
		continue;
	}
	wp_update_user(
		array(
			'ID'           => $qv_uid,
			'first_name'   => $qv_a[1],
			'last_name'    => $qv_a[2],
			'display_name' => "{$qv_a[1]} {$qv_a[2]}",
			'nickname'     => $qv_a[1],
			'description'  => $qv_a[5],
			'user_url'     => 'https://quantivista.com',
		)
	);
	update_user_meta( $qv_uid, 'qv_job_title', $qv_a[3] );
	$qv_avatar = qvs_find_by_key( "team-{$qv_a[4]}", 'attachment', 'inherit' );
	if ( $qv_avatar ) {
		update_user_meta( $qv_uid, 'qv_avatar_id', $qv_avatar );
	}
	$qv_author_ids[ $qv_key ] = $qv_uid;
}

/* ------------------------------------------------------------------------
 * Categories
 * --------------------------------------------------------------------- */
$qv_cats = array(
	'engineering'     => array( 'Engineering', 'Architecture, performance and the practical details of shipping reliable software.' ),
	'product-updates' => array( 'Product Updates', 'Release notes and deep-dives into what\'s new across Quantivista products.' ),
	'design'          => array( 'Design', 'UX, UI, accessibility and conversion-focused web design.' ),
	'mobile'          => array( 'Mobile', 'Building, launching and scaling iOS and Android apps.' ),
	'business'        => array( 'Business Growth', 'Pricing, operations and growing a software-driven business.' ),
);
$qv_cat_ids = array();
foreach ( $qv_cats as $qv_slug => $qv_cat ) {
	$qv_cat_ids[ $qv_slug ] = qvs_term( $qv_cat[0], 'category', $qv_slug, array( 'description' => $qv_cat[1] ) );
}

/* ------------------------------------------------------------------------
 * Articles
 * --------------------------------------------------------------------- */
$qv_posts = array(
	array(
		'slug'    => 'flowpilot-3-0-branching-approvals-new-engine',
		'title'   => 'FlowPilot 3.0: branching, human approvals and a 10× faster engine',
		'author'  => 'samuel',
		'cat'     => 'product-updates',
		'tags'    => array( 'FlowPilot', 'Automation', 'Release notes' ),
		'days'    => 6,
		'glyph'   => '3.0',
		'palette' => array( 'A78BFA', '22D3EE' ),
		'excerpt' => 'Our biggest release yet adds branching logic, human-in-the-loop approvals and a rewritten execution engine that handles millions of runs per day on a single node.',
		'content' => '<p>When we shipped FlowPilot 1.0, most customers automated simple, linear tasks: a form submission creates a CRM contact, which triggers a welcome email. Three years later, the workflows we see are far more ambitious — multi-department processes with conditions, loops and people in the loop. FlowPilot 3.0 is built for that reality.</p>
		<h2>Branching and parallel steps</h2>
		<p>Workflows can now split into branches based on any condition, run steps in parallel and merge results back together. A typical order-fulfilment flow can check stock, create an invoice and notify the warehouse at the same time, then continue only when all three succeed.</p>
		<ul><li><strong>If/else and switch</strong> nodes with a visual expression builder</li><li><strong>Parallel groups</strong> with configurable "wait for all" or "first to finish" behaviour</li><li><strong>Sub-flows</strong> so teams can reuse common sequences</li></ul>
		<h2>Human approvals</h2>
		<p>Some decisions shouldn\'t be automated. The new <em>Approval</em> step pauses a run and sends an actionable message by email or Slack. Approvers see the relevant data, click approve or reject, and the workflow resumes — with the decision recorded in the audit log.</p>
		<blockquote><p>"Approvals were the missing piece for our finance team. Refunds over $500 now route to a manager automatically, and we have a complete paper trail." — Marcus Feld, Cobalt Finance</p></blockquote>
		<h2>A new execution engine</h2>
		<p>We rewrote the engine around a durable, Redis-backed queue with idempotent step execution. In our benchmarks a single 4-vCPU node now processes <strong>2.3 million runs per day</strong>, up from 210,000 in version 2. Horizontal workers scale linearly when you need more.</p>
		<pre><code>docker compose pull
docker compose up -d --scale worker=4</code></pre>
		<h2>Upgrading</h2>
		<p>Version 3.0 is a free update for every license with an active update period. Existing workflows are migrated automatically on first start; we recommend taking a database backup first. Full upgrade notes are in the documentation, and our support team is on hand if you need help.</p>',
	),
	array(
		'slug'    => 'self-hosted-vs-saas-crm-total-cost',
		'title'   => 'Self-hosted vs. SaaS CRM: what it really costs over five years',
		'author'  => 'elena',
		'cat'     => 'business',
		'tags'    => array( 'CRM', 'Pricing', 'Nexus CRM' ),
		'days'    => 13,
		'glyph'   => '%',
		'palette' => array( 'FBBF24', '5B6CFF' ),
		'excerpt' => 'Per-seat pricing looks cheap at ten users and painful at a hundred. We modelled the five-year total cost of ownership for SaaS and self-hosted CRMs — here\'s what we found.',
		'content' => '<p>Choosing a CRM is usually framed as a feature comparison. But for growing teams, the bigger decision is the pricing model: pay a monthly fee per seat forever, or buy a license and run the software yourself. We modelled both for a company growing from 15 to 120 users over five years.</p>
		<h2>The assumptions</h2>
		<ul><li>Team grows from 15 users in year one to 120 in year five.</li><li>SaaS plan at $89 per user per month, with a typical 7% annual price increase.</li><li>Self-hosted license at $599 (unlimited tier) plus renewals, a $60/month cloud server and four hours of maintenance per month.</li></ul>
		<h2>The results</h2>
		<table><thead><tr><th>Year</th><th>SaaS CRM</th><th>Self-hosted</th></tr></thead><tbody><tr><td>1</td><td>$16,020</td><td>$4,079</td></tr><tr><td>3</td><td>$71,500</td><td>$4,520</td></tr><tr><td>5</td><td>$137,900</td><td>$4,960</td></tr><tr><td><strong>Total</strong></td><td><strong>$384,200</strong></td><td><strong>$22,600</strong></td></tr></tbody></table>
		<p>Even after adding generous internal maintenance time, the self-hosted option costs a fraction of the SaaS subscription once a team passes roughly 25 users.</p>
		<h2>When SaaS still wins</h2>
		<p>Self-hosting isn\'t for everyone. If you have no one comfortable running a Docker deployment, need a specific ecosystem of marketplace apps, or plan to stay under ten users, a SaaS CRM can be the better choice.</p>
		<h2>The hidden benefit: data ownership</h2>
		<p>The numbers are compelling, but customers tell us the deciding factor is often control. With a self-hosted CRM your customer data lives in your own database, which simplifies compliance reviews and removes vendor lock-in.</p>
		<p class="callout">Want to run the numbers for your team? <a href="/contact/">Contact us</a> and we\'ll send you our TCO spreadsheet.</p>',
	),
	array(
		'slug'    => 'accessible-design-systems-that-convert',
		'title'   => 'Accessible design systems that also convert better',
		'author'  => 'nadia',
		'cat'     => 'design',
		'tags'    => array( 'Accessibility', 'Design systems', 'Conversion' ),
		'days'    => 21,
		'glyph'   => 'Aa',
		'palette' => array( '2DD4BF', '5B6CFF' ),
		'excerpt' => 'Accessibility is often treated as a compliance checkbox. Across 40 recent launches, the accessible version of a page won our A/B tests more often than not. Here are the patterns that matter.',
		'content' => '<p>There\'s a persistent myth that accessible design means compromising on aesthetics or conversion. Our data says the opposite. Across 40 website launches in the past two years, pages that met WCAG 2.2 AA from day one outperformed their earlier versions on conversion in 31 cases.</p>
		<h2>1. Contrast is a conversion lever</h2>
		<p>Low-contrast grey text looks elegant in a design tool and disappears on a phone in sunlight. We set a floor of 4.5:1 for body text and 3:1 for UI components, and design primary buttons to be the highest-contrast element on the page.</p>
		<h2>2. Focus states are for everyone</h2>
		<p>Visible focus indicators help keyboard users, but they also help anyone filling in a long form. Our design system uses a 3px outline with a 2px offset that adapts its colour to light and dark sections.</p>
		<h2>3. Labels beat placeholders</h2>
		<p>Placeholder-only fields reduce clutter but increase errors, because the hint vanishes as soon as someone starts typing. Persistent labels, clear error messages next to the field and generous touch targets cut form abandonment by 18% on average.</p>
		<h2>4. Motion with restraint</h2>
		<p>Subtle scroll animations add polish, but they must respect <code>prefers-reduced-motion</code>. Every animation in our component library has a reduced-motion variant — usually no movement at all.</p>
		<h2>Build it into the system</h2>
		<ul><li>Tokens for colour pairs that are pre-validated for contrast</li><li>Components with keyboard behaviour documented and tested</li><li>Automated checks in CI plus manual screen-reader passes before launch</li></ul>
		<p>Accessibility isn\'t a final QA step; it\'s a set of defaults. Get the defaults right and every page your team builds starts from a better place.</p>',
	),
	array(
		'slug'    => 'shipping-flutter-app-to-both-stores-in-five-weeks',
		'title'   => 'How we ship a Flutter app to both app stores in five weeks',
		'author'  => 'yuki',
		'cat'     => 'mobile',
		'tags'    => array( 'Flutter', 'App Store', 'PulseFit' ),
		'days'    => 30,
		'glyph'   => 'iOS',
		'palette' => array( '34D399', '22D3EE' ),
		'excerpt' => 'A week-by-week look at the process we use with app kit customers — from rebranding and backend setup to TestFlight, Play Console reviews and launch day.',
		'content' => '<p>Starting from a production-ready kit removes months of foundational work, but launching still involves plenty of moving parts. This is the five-week plan we follow with customers who build on PulseFit or Courier.</p>
		<h2>Week 1 — Brand and configure</h2>
		<p>We swap colours, typography and icons through the kit\'s theme file, then configure environment variables for the API, analytics and crash reporting. Most customers see their branded app running on a device by day three.</p>
		<h2>Week 2 — Backend and data</h2>
		<p>The Node.js API and PostgreSQL database run with a single Docker Compose file. We connect production services — Stripe or RevenueCat for payments, a transactional email provider and push notification certificates.</p>
		<h2>Week 3 — Customise the core flow</h2>
		<p>Every business has one flow that makes it unique. We focus custom development there — for a fitness studio it might be class booking; for a delivery startup it is dispatch rules.</p>
		<h2>Week 4 — Test in the wild</h2>
		<ul><li>Internal testing via TestFlight and Play Console internal tracks</li><li>Accessibility pass with VoiceOver and TalkBack</li><li>Performance profiling on a low-end Android device</li></ul>
		<h2>Week 5 — Store review and launch</h2>
		<p>We prepare store listings, privacy nutrition labels and data-safety forms, then submit. Apple\'s review typically takes one to two days; Google\'s can take up to a week for new developer accounts, so we submit Android first.</p>
		<blockquote><p>The most common rejection reason we see? Missing account deletion. Make sure users can delete their account from inside the app.</p></blockquote>
		<p>Five weeks is ambitious but realistic when the foundations are solid. If you\'d like help with your launch, our mobile team offers fixed-price launch packages.</p>',
	),
	array(
		'slug'    => 'core-web-vitals-checklist-for-marketing-sites',
		'title'   => 'The Core Web Vitals checklist we use on every marketing site',
		'author'  => 'nadia',
		'cat'     => 'engineering',
		'tags'    => array( 'Performance', 'SEO', 'WordPress' ),
		'days'    => 38,
		'glyph'   => '98',
		'palette' => array( '22D3EE', '5B6CFF' ),
		'excerpt' => 'Fast sites rank better and convert better. This is the practical checklist our studio runs before any website goes live — no premium plugins required.',
		'content' => '<p>Our studio has a simple rule: no website launches with a mobile Lighthouse performance score below 90. Hitting that consistently doesn\'t require exotic tooling — just a disciplined checklist.</p>
		<h2>Largest Contentful Paint (LCP)</h2>
		<ul><li>Serve the hero image in modern formats with explicit width and height.</li><li>Add <code>fetchpriority="high"</code> to the LCP image and never lazy-load it.</li><li>Self-host and preload the one font file used above the fold.</li></ul>
		<h2>Interaction to Next Paint (INP)</h2>
		<ul><li>Defer all non-critical JavaScript and avoid heavy slider libraries.</li><li>Replace chat widgets with a lightweight facade that loads on click.</li><li>Break long tasks with <code>requestIdleCallback</code> or scheduler APIs.</li></ul>
		<h2>Cumulative Layout Shift (CLS)</h2>
		<ul><li>Reserve space for images, embeds and ads with aspect-ratio boxes.</li><li>Use <code>font-display: swap</code> with fallback metrics that match your web font.</li><li>Never inject banners above existing content after load.</li></ul>
		<h2>WordPress specifics</h2>
		<p>On WordPress we remove emoji scripts, load form plugin assets only on pages with forms, and use full-page caching at the server or CDN level. A lean custom theme typically ships less than 60 KB of CSS and JavaScript combined.</p>
		<pre><code>add_filter( \'wpcf7_load_js\', fn() => is_page( \'contact\' ) );</code></pre>
		<p>None of these items is difficult on its own. The trick is making them non-negotiable parts of the launch process.</p>',
	),
	array(
		'slug'    => 'designing-webhooks-customers-can-trust',
		'title'   => 'Designing webhooks your customers can actually trust',
		'author'  => 'samuel',
		'cat'     => 'engineering',
		'tags'    => array( 'API', 'Webhooks', 'Security' ),
		'days'    => 47,
		'glyph'   => '</>',
		'palette' => array( '5B6CFF', 'F472B6' ),
		'excerpt' => 'Signatures, retries, idempotency keys and replay — the webhook design principles behind Nexus CRM, FlowPilot and Quanta Analytics.',
		'content' => '<p>Webhooks look simple: send an HTTP POST when something happens. In production they are one of the most common sources of integration bugs. These are the principles we apply across every Quantivista product.</p>
		<h2>Sign every payload</h2>
		<p>Each request includes an HMAC-SHA256 signature of the raw body and a timestamp. Receivers verify both, rejecting requests older than five minutes to prevent replay attacks.</p>
		<pre><code>X-QV-Timestamp: 1767225600
X-QV-Signature: v1=5f2b9c…</code></pre>
		<h2>Retry with exponential backoff</h2>
		<p>Receivers go down. We retry failed deliveries up to 12 times over 48 hours with exponential backoff and jitter, then move the event to a dead-letter queue that customers can inspect and replay from the dashboard.</p>
		<h2>Make consumers idempotent</h2>
		<p>Because retries mean an event can arrive more than once, every payload carries a unique event ID. Our documentation shows how to store processed IDs so duplicate deliveries are harmless.</p>
		<h2>Keep payloads small and versioned</h2>
		<ul><li>Send identifiers and the changed fields, not entire objects.</li><li>Version event schemas explicitly and support the previous version for 12 months.</li><li>Publish a JSON Schema for every event type.</li></ul>
		<h2>Give developers visibility</h2>
		<p>The single biggest improvement to support volume came from a delivery log that shows request, response and latency for every attempt. When developers can see what happened, they rarely need to ask us.</p>',
	),
);

$qv_commenters = array(
	array( 'Priya Raman', 'Great write-up. The approval step is exactly what our ops team has been asking for — upgrading this week.' ),
	array( 'Jonas Weber', 'Do the migrated workflows keep their run history, or does that start fresh after the upgrade?' ),
	array( 'Chloe Martin', 'Really appreciate that you shared actual numbers. We did a similar analysis last year and reached the same conclusion.' ),
	array( 'Omar Haddad', 'The point about placeholders vs. labels is so underrated. Bookmarking this for our next design review.' ),
);

foreach ( $qv_posts as $qv_i => $qv_p ) {
	$qv_post_id = qvs_upsert_post(
		'post-' . $qv_p['slug'],
		array(
			'post_type'     => 'post',
			'post_title'    => $qv_p['title'],
			'post_name'     => $qv_p['slug'],
			'post_content'  => qvs_html( $qv_p['content'] ),
			'post_excerpt'  => $qv_p['excerpt'],
			'post_author'   => $qv_author_ids[ $qv_p['author'] ] ?? 1,
			'post_date'     => wp_date( 'Y-m-d H:i:s', strtotime( '-' . $qv_p['days'] . ' days 10:00' ) ),
			'post_category' => array( $qv_cat_ids[ $qv_p['cat'] ] ),
			'comment_status' => 'open',
		)
	);
	wp_set_post_tags( $qv_post_id, $qv_p['tags'], false );

	$qv_cover = qvs_image(
		'cover-' . $qv_p['slug'],
		'cover-' . $qv_p['slug'] . '.jpg',
		fn() => qvimg_cover( 1600, 900, $qv_p['glyph'], $qv_p['palette'][0], $qv_p['palette'][1], $qv_p['slug'] ),
		$qv_p['title']
	);
	set_post_thumbnail( $qv_post_id, $qv_cover );

	if ( $qv_i < 3 && ! get_comments( array( 'post_id' => $qv_post_id, 'count' => true ) ) ) {
		foreach ( array_slice( $qv_commenters, $qv_i, 2 ) as $qv_c => $qv_comment ) {
			wp_insert_comment(
				array(
					'comment_post_ID'      => $qv_post_id,
					'comment_author'       => $qv_comment[0],
					'comment_author_email' => sanitize_title( $qv_comment[0] ) . '@example.com',
					'comment_content'      => $qv_comment[1],
					'comment_approved'     => 1,
					'comment_date'         => wp_date( 'Y-m-d H:i:s', strtotime( '-' . max( 1, $qv_p['days'] - 1 - $qv_c ) . ' days' ) ),
				)
			);
		}
	}
	WP_CLI::log( "    post: {$qv_p['title']} (#{$qv_post_id})" );
}

// Remove the default "Uncategorized" label from view.
$qv_uncat = get_term_by( 'slug', 'uncategorized', 'category' );
if ( $qv_uncat ) {
	wp_update_term( $qv_uncat->term_id, 'category', array( 'name' => 'General' ) );
}
