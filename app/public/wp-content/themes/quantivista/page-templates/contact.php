<?php
/**
 * Template Name: Contact
 *
 * Contact details, Contact Form 7 form and FAQ.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$qv_id        = get_the_ID();
	$qv_shortcode = qv_field( 'qv_form_shortcode', $qv_id );
	$qv_faq       = qv_lines( qv_field( 'qv_faq', $qv_id ) );
	$qv_email     = qv_mod( 'contact_email' );
	$qv_phone     = qv_mod( 'contact_phone' );

	qv_page_hero(
		array(
			'eyebrow' => __( 'Contact', 'quantivista' ),
			'title'   => get_the_title(),
			'lead'    => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>
	<section class="section section--tight">
		<div class="container contact-grid">
			<div>
				<div class="prose" style="margin-bottom:24px;"><?php the_content(); ?></div>
				<ul class="info-list">
					<li class="info-card">
						<span class="info-card__icon"><?php qv_the_icon( 'mail' ); ?></span>
						<span>
							<span class="info-card__label"><?php esc_html_e( 'Email', 'quantivista' ); ?></span>
							<a class="info-card__value" href="mailto:<?php echo esc_attr( antispambot( $qv_email ) ); ?>"><?php echo esc_html( antispambot( $qv_email ) ); ?></a>
						</span>
					</li>
					<?php if ( $qv_phone ) : ?>
					<li class="info-card">
						<span class="info-card__icon"><?php qv_the_icon( 'phone' ); ?></span>
						<span>
							<span class="info-card__label"><?php esc_html_e( 'Phone', 'quantivista' ); ?></span>
							<a class="info-card__value" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $qv_phone ) ); ?>"><?php echo esc_html( $qv_phone ); ?></a>
						</span>
					</li>
					<?php endif; ?>
					<li class="info-card">
						<span class="info-card__icon"><?php qv_the_icon( 'map-pin' ); ?></span>
						<span>
							<span class="info-card__label"><?php esc_html_e( 'Office', 'quantivista' ); ?></span>
							<span class="info-card__value"><?php echo esc_html( qv_mod( 'contact_address' ) ); ?></span>
						</span>
					</li>
					<?php if ( qv_mod( 'contact_hours' ) ) : ?>
					<li class="info-card">
						<span class="info-card__icon"><?php qv_the_icon( 'clock' ); ?></span>
						<span>
							<span class="info-card__label"><?php esc_html_e( 'Hours', 'quantivista' ); ?></span>
							<span class="info-card__value"><?php echo esc_html( qv_mod( 'contact_hours' ) ); ?></span>
						</span>
					</li>
					<?php endif; ?>
				</ul>
				<?php qv_social_links(); ?>
			</div>

			<div class="form-card" id="contact-form">
				<h2><?php esc_html_e( 'Tell me about your project', 'quantivista' ); ?></h2>
				<p class="muted"><?php esc_html_e( 'I reply within two business days. Fields marked * are required.', 'quantivista' ); ?></p>
				<?php
				if ( $qv_shortcode && shortcode_exists( 'contact-form-7' ) ) {
					echo do_shortcode( $qv_shortcode );
				} else {
					printf(
						'<p>%s <a href="mailto:%2$s">%2$s</a></p>',
						esc_html__( 'The contact form is being set up. Meanwhile, email me at', 'quantivista' ),
						esc_html( antispambot( $qv_email ) )
					);
				}
				?>
			</div>
		</div>
	</section>

	<?php if ( $qv_faq ) : ?>
		<section class="section section--muted" aria-labelledby="faq-title">
			<div class="container container--narrow">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'FAQ', 'quantivista' ),
						'title'   => __( 'Questions we hear a lot', 'quantivista' ),
						'id'      => 'faq-title',
					)
				);
				?>
				<div class="faq">
					<?php foreach ( $qv_faq as $qv_line ) : ?>
						<?php $qv_parts = array_map( 'trim', explode( '|', $qv_line, 2 ) ); ?>
						<details>
							<summary><?php echo esc_html( $qv_parts[0] ); ?> <?php qv_the_icon( 'chevron-down', array( 'size' => 18 ) ); ?></summary>
							<div><p><?php echo esc_html( $qv_parts[1] ?? '' ); ?></p></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
