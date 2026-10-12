<?php
/**
 * Clinic software sales page, shared by the Persian and English templates.
 *
 * Persian: plans and order form from Clinic Orders (Zarinpal, toman).
 * English: plans are WooCommerce service products (SKU QV-CLINIC-*), bought
 * through the normal store checkout in the store currency.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_lang = ( $args['lang'] ?? 'fa' ) === 'en' ? 'en' : 'fa';
$qv_fa   = 'fa' === $qv_lang;
$qv_t    = qv_clinic_strings( $qv_lang );
$qv_img  = QV_URI . '/assets/img/clinic/';
$qv_demo = function_exists( 'qvc_clinic_settings' ) ? qvc_clinic_settings()['demo_url'] : '';
$qv_cta  = $qv_fa ? '#order' : '#plans';
?>
<div class="clinic-landing clinic-landing--<?php echo esc_attr( $qv_lang ); ?>" dir="<?php echo $qv_fa ? 'rtl' : 'ltr'; ?>" lang="<?php echo esc_attr( $qv_lang ); ?>">

	<section class="clinic-hero">
		<div class="page-hero__glow" aria-hidden="true"></div>
		<div class="container clinic-hero__grid">
			<div class="clinic-hero__text">
				<p class="eyebrow"><?php echo esc_html( $qv_t['eyebrow'] ); ?></p>
				<h1 class="clinic-hero__title"><?php echo esc_html( $qv_t['title'] ); ?></h1>
				<p class="clinic-hero__lead"><?php echo esc_html( $qv_t['lead'] ); ?></p>
				<div class="btn-row">
					<a class="btn btn--accent btn--lg" href="<?php echo esc_attr( $qv_cta ); ?>"><?php echo esc_html( $qv_t['order'] ); ?></a>
					<?php if ( $qv_demo ) : ?>
						<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( $qv_demo ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $qv_t['demo'] ); ?></a>
					<?php endif; ?>
				</div>
				<ul class="clinic-hero__points">
					<?php foreach ( $qv_t['hero_points'] as $qv_point ) : ?>
						<li><?php qv_the_icon( 'check', array( 'size' => 16 ) ); ?> <?php echo esc_html( $qv_point ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<figure class="clinic-hero__shot">
				<img src="<?php echo esc_url( $qv_img . 'demo-home.webp' ); ?>" width="1200" height="791" alt="<?php echo esc_attr( $qv_t['shots'][0][1] ); ?>" fetchpriority="high">
			</figure>
		</div>
	</section>

	<section class="section" aria-labelledby="clinic-features">
		<div class="container">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['features_eyebrow'], 'title' => $qv_t['features_title'], 'id' => 'clinic-features' ) ); ?>
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

	<section class="section section--muted" aria-labelledby="clinic-shots">
		<div class="container">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['shots_eyebrow'], 'title' => $qv_t['shots_title'], 'id' => 'clinic-shots' ) ); ?>
			<div class="grid grid--3 clinic-shots">
				<?php foreach ( $qv_t['shots'] as $qv_shot ) : ?>
					<figure class="clinic-shot" data-reveal>
						<img src="<?php echo esc_url( $qv_img . $qv_shot[0] . '.webp' ); ?>" width="1200" height="791" alt="<?php echo esc_attr( $qv_shot[1] ); ?>" loading="lazy">
						<figcaption><?php echo esc_html( $qv_shot[1] ); ?></figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section" aria-labelledby="clinic-steps">
		<div class="container">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['steps_eyebrow'], 'title' => $qv_t['steps_title'], 'id' => 'clinic-steps' ) ); ?>
			<ol class="clinic-steps">
				<?php foreach ( $qv_t['steps'] as $qv_i => $qv_step ) : ?>
					<li data-reveal>
						<span class="clinic-steps__num"><?php echo esc_html( $qv_fa ? strtr( (string) ( $qv_i + 1 ), array_combine( range( 0, 9 ), mb_str_split( '۰۱۲۳۴۵۶۷۸۹' ) ) ) : $qv_i + 1 ); ?></span>
						<h3><?php echo esc_html( $qv_step[0] ); ?></h3>
						<p><?php echo esc_html( $qv_step[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="clinic-note"><?php qv_the_icon( 'globe', array( 'size' => 18 ) ); ?> <?php echo esc_html( $qv_t['hosting_note'] ); ?></p>
		</div>
	</section>

	<section class="section section--dark" id="plans" aria-labelledby="clinic-plans">
		<div class="container">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['plans_eyebrow'], 'title' => $qv_t['plans_title'], 'lead' => $qv_t['plans_lead'], 'id' => 'clinic-plans' ) ); ?>
			<div class="grid grid--3 clinic-plans">
				<?php foreach ( qv_clinic_plan_cards( $qv_lang ) as $qv_key => $qv_plan ) : ?>
					<div class="clinic-plan<?php echo ! empty( $qv_plan['popular'] ) ? ' clinic-plan--popular' : ''; ?>" data-reveal>
						<?php if ( ! empty( $qv_plan['popular'] ) ) : ?>
							<span class="clinic-plan__badge"><?php echo esc_html( $qv_t['popular'] ); ?></span>
						<?php endif; ?>
						<h3 class="clinic-plan__title"><?php echo esc_html( $qv_plan['title'] ); ?></h3>
						<p class="clinic-plan__lead"><?php echo esc_html( $qv_plan['lead'] ); ?></p>
						<p class="clinic-plan__price"><?php echo wp_kses_post( $qv_plan['price_html'] ); ?></p>
						<ul class="check-list">
							<?php foreach ( $qv_plan['features'] as $qv_line ) : ?>
								<li><?php qv_the_icon( 'check', array( 'size' => 16 ) ); ?> <span><?php echo esc_html( $qv_line ); ?></span></li>
							<?php endforeach; ?>
						</ul>
						<a class="btn <?php echo ! empty( $qv_plan['popular'] ) ? 'btn--accent' : 'btn--outline'; ?> btn--block" href="<?php echo esc_url( $qv_plan['href'] ); ?>"<?php echo $qv_fa ? ' data-plan="' . esc_attr( $qv_key ) . '"' : ''; ?>><?php echo esc_html( $qv_plan['button'] ); ?></a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( $qv_fa ) : ?>
		<section class="section" id="order" aria-labelledby="clinic-order">
			<div class="container container--narrow">
				<?php get_template_part( 'template-parts/clinic/order-form' ); ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="section section--muted" aria-labelledby="clinic-faq">
		<div class="container container--narrow">
			<?php qv_section_head( array( 'eyebrow' => $qv_t['faq_eyebrow'], 'title' => $qv_t['faq_title'], 'id' => 'clinic-faq' ) ); ?>
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
