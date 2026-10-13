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
		array( 'zap', __( 'Tested code', 'quantivista' ), __( 'Typed, tested and documented. The rules that matter, like no double bookings, are proven by automated tests.', 'quantivista' ) ),
		array( 'shield', __( 'Secure by default', 'quantivista' ), __( 'Two-factor login for staff, encrypted sensitive fields, rate limits and encrypted backups.', 'quantivista' ) ),
		array( 'headphones', __( 'Talk to the author', 'quantivista' ), __( 'Support comes from the person who wrote the code, in English or Persian.', 'quantivista' ) ),
		array( 'rocket', __( 'Clear setup', 'quantivista' ), __( 'Install packages, step-by-step guides and a written handover checklist for every installation.', 'quantivista' ) ),
	)
);

$qv_checks = array(
	__( 'Designed and built by me, not outsourced', 'quantivista' ),
	__( 'A written scope and a fixed price before work starts', 'quantivista' ),
	__( 'Your data and your domain stay yours', 'quantivista' ),
	__( 'Accessibility and performance checked on every page', 'quantivista' ),
	__( 'Medical software shaped by real clinical work', 'quantivista' ),
);

$qv_about = qv_page_by_path( 'about' );
?>
<section class="section section--dark" aria-labelledby="why-title">
	<div class="container">
		<?php
		qv_section_head(
			array(
				'eyebrow' => __( 'Why Hassani Labs', 'quantivista' ),
				'title'   => __( 'From a real problem to a smart software solution', 'quantivista' ),
				'lead'    => __( 'Websites, custom software and digital tools, built with an analytical, structured approach that fits your real needs.', 'quantivista' ),
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
				<p class="eyebrow"><?php esc_html_e( 'How I work', 'quantivista' ); ?></p>
				<h3><?php esc_html_e( 'One person for your software and your website', 'quantivista' ); ?></h3>
				<p><?php esc_html_e( 'Start with a product, extend it with custom features, and launch it on a website built by the same person. Fewer handoffs, clearer results.', 'quantivista' ); ?></p>
				<?php if ( $qv_about ) : ?>
					<a class="btn btn--accent" href="<?php echo esc_url( get_permalink( $qv_about ) ); ?>"><?php esc_html_e( 'About me', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
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
