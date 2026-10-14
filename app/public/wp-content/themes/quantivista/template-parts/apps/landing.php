<?php
/**
 * App sales page, shared by the Persian and English templates.
 *
 * Persian: order form from App Orders (Zarinpal, toman).
 * English: the same page, with buying through the Persian page or contact.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_app   = sanitize_key( $args['app'] ?? 'safejot' );
$qv_lang  = ( $args['lang'] ?? 'fa' ) === 'en' ? 'en' : 'fa';
$qv_fa    = 'fa' === $qv_lang;
$qv_t     = qv_app_strings( $qv_app, $qv_lang );
$qv_shots = qv_app_shots( $qv_app, $qv_lang );
$qv_icon  = QV_URI . '/assets/img/apps/' . $qv_app . '-icon.webp';
$qv_num   = fn( $n ) => $qv_fa ? strtr( (string) $n, array_combine( range( 0, 9 ), mb_str_split( '۰۱۲۳۴۵۶۷۸۹' ) ) ) : (string) $n;

// English page: buy on the Persian page (Zarinpal) or ask by contact.
$qv_fa_url = '';
if ( ! $qv_fa && function_exists( 'pll_get_post' ) ) {
	$qv_fa_id  = pll_get_post( get_the_ID(), 'fa' );
	$qv_fa_url = $qv_fa_id ? get_permalink( $qv_fa_id ) . '#order' : '';
}
?>
<div class="clinic-landing clinic-landing--<?php echo esc_attr( $qv_lang ); ?> app-landing" dir="<?php echo $qv_fa ? 'rtl' : 'ltr'; ?>" lang="<?php echo esc_attr( $qv_lang ); ?>">

	<section class="clinic-hero app-hero">
		<div class="page-hero__glow" aria-hidden="true"></div>
		<div class="container clinic-hero__grid">
			<div class="clinic-hero__text">
				<p class="app-hero__brand">
					<img src="<?php echo esc_url( $qv_icon ); ?>" width="64" height="64" alt="<?php echo esc_attr( $qv_t['icon_alt'] ); ?>">
					<span><?php echo esc_html( $qv_t['name'] ); ?></span>
				</p>
				<p class="eyebrow"><?php echo esc_html( $qv_t['eyebrow'] ); ?></p>
				<h1 class="clinic-hero__title"><?php echo esc_html( $qv_t['title'] ); ?></h1>
				<p class="clinic-hero__lead"><?php echo esc_html( $qv_t['lead'] ); ?></p>
				<div class="btn-row">
					<a class="btn btn--accent btn--lg" href="#plan"><?php echo esc_html( $qv_t['buy'] ); ?> · <?php echo esc_html( qv_app_price_text( $qv_app, $qv_lang ) ); ?></a>
					<a class="btn btn--ghost btn--lg" href="#app-features"><?php echo esc_html( $qv_t['more'] ); ?></a>
				</div>
				<ul class="clinic-hero__points">
					<?php foreach ( $qv_t['points'] as $qv_point ) : ?>
						<li><?php qv_the_icon( 'check', array( 'size' => 16 ) ); ?> <?php echo esc_html( $qv_point ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="app-hero__visual">
				<?php if ( $qv_shots ) : ?>
					<figure class="app-phone">
						<img src="<?php echo esc_url( $qv_shots[0][0] ); ?>" width="540" height="1170" alt="<?php echo esc_attr( $qv_shots[0][1] ); ?>" fetchpriority="high">
					</figure>
				<?php else : ?>
					<div class="app-hero__icon">
						<img src="<?php echo esc_url( $qv_icon ); ?>" width="256" height="256" alt="" fetchpriority="high">
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="section" id="app-features" aria-labelledby="app-features-title">
		<div class="container">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['features_eye'], 'title' => $qv_t['features_title'], 'id' => 'app-features-title' ) ); ?>
			<div class="grid grid--4 clinic-features">
				<?php foreach ( $qv_t['features'] as $qv_f ) : ?>
					<div class="clinic-card" data-reveal>
						<span class="clinic-card__icon"><?php qv_the_icon( $qv_f[0], array( 'size' => 22 ) ); ?></span>
						<h3 class="clinic-card__title"><?php echo esc_html( $qv_f[1] ); ?></h3>
						<p class="clinic-card__text"><?php echo esc_html( $qv_f[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( $qv_shots ) : ?>
		<section class="section section--muted" aria-labelledby="app-shots">
			<div class="container">
				<?php qv_section_head( array( 'eyebrow' => $qv_t['shots_eye'], 'title' => $qv_t['shots_title'], 'id' => 'app-shots' ) ); ?>
				<div class="app-shots">
					<?php foreach ( $qv_shots as $qv_shot ) : ?>
						<figure class="app-shots__item" data-reveal>
							<span class="app-phone"><img src="<?php echo esc_url( $qv_shot[0] ); ?>" width="540" height="1170" alt="<?php echo esc_attr( $qv_shot[1] ); ?>" loading="lazy"></span>
							<figcaption><?php echo esc_html( $qv_shot[1] ); ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="section" aria-labelledby="app-steps">
		<div class="container">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['steps_eye'], 'title' => $qv_t['steps_title'], 'id' => 'app-steps' ) ); ?>
			<ol class="clinic-steps app-steps">
				<?php foreach ( $qv_t['steps'] as $qv_i => $qv_step ) : ?>
					<li data-reveal>
						<span class="clinic-steps__num"><?php echo esc_html( $qv_num( $qv_i + 1 ) ); ?></span>
						<h3><?php echo esc_html( $qv_step[0] ); ?></h3>
						<p><?php echo esc_html( $qv_step[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="section section--dark" id="plan" aria-labelledby="app-plan">
		<div class="container container--narrow">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['plan_eye'], 'title' => $qv_t['plan_title'], 'id' => 'app-plan' ) ); ?>
			<div class="clinic-plan clinic-plan--popular app-plan" data-reveal>
				<h3 class="clinic-plan__title"><?php echo esc_html( $qv_t['plan_name'] ); ?></h3>
				<p class="clinic-plan__lead"><?php echo esc_html( $qv_t['plan_lead'] ); ?></p>
				<p class="clinic-plan__price"><?php echo esc_html( qv_app_price_text( $qv_app, $qv_lang ) ); ?></p>
				<ul class="check-list">
					<?php foreach ( $qv_t['plan_features'] as $qv_line ) : ?>
						<li><?php qv_the_icon( 'check', array( 'size' => 16 ) ); ?> <span><?php echo esc_html( $qv_line ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $qv_fa ) : ?>
					<a class="btn btn--accent btn--block" href="#order"><?php echo esc_html( $qv_t['buy'] ); ?></a>
				<?php else : ?>
					<div class="btn-row">
						<?php if ( $qv_fa_url ) : ?>
							<a class="btn btn--accent" href="<?php echo esc_url( $qv_fa_url ); ?>"><?php echo esc_html( $qv_t['buy_fa'] ); ?></a>
						<?php endif; ?>
						<a class="btn btn--outline" href="<?php echo esc_url( qv_localize_url( '/contact/' ) ); ?>"><?php echo esc_html( $qv_t['contact'] ); ?></a>
					</div>
					<p class="app-plan__note"><?php echo esc_html( $qv_t['buy_note'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $qv_fa ) : ?>
		<section class="section" id="order" aria-labelledby="app-order">
			<div class="container container--narrow">
				<?php get_template_part( 'template-parts/apps/order-form', null, array( 'app' => $qv_app, 'name' => $qv_t['name'] ) ); ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="section section--muted" aria-labelledby="app-faq">
		<div class="container container--narrow">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['faq_eye'], 'title' => $qv_t['faq_title'], 'id' => 'app-faq' ) ); ?>
			<div class="faq">
				<?php foreach ( $qv_t['faq'] as $qv_q ) : ?>
					<details>
						<summary><?php echo esc_html( $qv_q[0] ); ?> <?php qv_the_icon( 'chevron-down', array( 'size' => 18 ) ); ?></summary>
						<div><p><?php echo esc_html( $qv_q[1] ); ?></p></div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
</div>
