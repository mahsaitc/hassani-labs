<?php
/**
 * Why choose us.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_items = apply_filters(
	'qv_why_items',
	array(
		array( 'zap', __( 'Production-grade code', 'quantivista' ), __( 'Typed, tested and documented. Every product passes the same review process we use for enterprise client work.', 'quantivista' ) ),
		array( 'shield', __( 'Secure by default', 'quantivista' ), __( 'OWASP-aligned practices, dependency scanning and responsible disclosure — with security patches for every active license.', 'quantivista' ) ),
		array( 'headphones', __( 'Humans on support', 'quantivista' ), __( 'Talk to the engineers who wrote the code. Median first response under 4 business hours.', 'quantivista' ) ),
		array( 'rocket', __( 'Launch in days', 'quantivista' ), __( 'Docker setups, one-click installers and step-by-step guides get you from purchase to production fast.', 'quantivista' ) ),
	)
);

$qv_checks = array(
	__( 'Senior engineers and designers — no outsourcing', 'quantivista' ),
	__( 'Fixed prices and clear milestones on every service', 'quantivista' ),
	__( 'Lifetime licenses: software keeps working without renewal', 'quantivista' ),
	__( 'Accessibility (WCAG 2.2 AA) and performance built in', 'quantivista' ),
	__( '14-day money-back guarantee on all software', 'quantivista' ),
);

$qv_about = get_page_by_path( 'about' );
?>
<section class="section section--dark" aria-labelledby="why-title">
	<div class="container">
		<?php
		qv_section_head(
			array(
				'eyebrow' => __( 'Why Quantivista', 'quantivista' ),
				'title'   => __( 'Built by engineers who ship for a living', 'quantivista' ),
				'lead'    => __( 'For twelve years we have built software for startups and enterprises. Our products are the tools we wished we had.', 'quantivista' ),
				'id'      => 'why-title',
			)
		);
		?>
		<div class="grid grid--4 why-grid">
			<?php foreach ( $qv_items as $qv_item ) : ?>
				<div class="value-card" data-reveal>
					<span class="value-card__icon"><?php qv_the_icon( $qv_item[0], array( 'size' => 24 ) ); ?></span>
					<h3 class="value-card__title"><?php echo esc_html( $qv_item[1] ); ?></h3>
					<p class="value-card__text"><?php echo esc_html( $qv_item[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="why-split" data-reveal>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'How we work', 'quantivista' ); ?></p>
				<h3><?php esc_html_e( 'One partner for your software and your website', 'quantivista' ); ?></h3>
				<p><?php esc_html_e( 'Start with a product, extend it with custom features, and launch it on a website designed by the same team. Fewer handoffs, faster results.', 'quantivista' ); ?></p>
				<?php if ( $qv_about ) : ?>
					<a class="btn btn--accent" href="<?php echo esc_url( get_permalink( $qv_about ) ); ?>"><?php esc_html_e( 'Meet the team', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
				<?php endif; ?>
			</div>
			<ul class="check-list">
				<?php foreach ( $qv_checks as $qv_check ) : ?>
					<li><?php qv_the_icon( 'check', array( 'size' => 16 ) ); ?><span><?php echo esc_html( $qv_check ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
