<?php
/**
 * Testimonials slider (scroll-snap carousel).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_items = get_posts(
	array(
		'post_type'      => 'qv_testimonial',
		'posts_per_page' => 9,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);

if ( ! $qv_items ) {
	return;
}
?>
<section class="section" aria-labelledby="testimonials-title" data-carousel>
	<div class="container">
		<div class="section-head-row">
			<?php
			qv_section_head(
				array(
					'eyebrow' => __( 'Testimonials', 'quantivista' ),
					'title'   => __( 'Teams that ship with Quantivista', 'quantivista' ),
					'id'      => 'testimonials-title',
					'align'   => 'left',
				)
			);
			?>
			<div class="carousel__nav">
				<button type="button" class="carousel__btn" data-carousel-prev>
					<?php qv_the_icon( 'chevron-left' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Previous testimonials', 'quantivista' ); ?></span>
				</button>
				<button type="button" class="carousel__btn" data-carousel-next>
					<?php qv_the_icon( 'chevron-right' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Next testimonials', 'quantivista' ); ?></span>
				</button>
			</div>
		</div>

		<ul class="carousel__track" data-carousel-track>
			<?php foreach ( $qv_items as $qv_item ) : ?>
				<?php
				$qv_role    = trim( qv_field( 'qv_role', $qv_item->ID ) . ', ' . qv_field( 'qv_company', $qv_item->ID ), ', ' );
				$qv_rating  = (int) qv_field( 'qv_rating', $qv_item->ID );
				$qv_name    = get_the_title( $qv_item );
				$qv_words   = preg_split( '/\s+/', $qv_name );
				$qv_initial = strtoupper( substr( $qv_words[0], 0, 1 ) . ( isset( $qv_words[1] ) ? substr( $qv_words[1], 0, 1 ) : '' ) );
				?>
				<li>
					<figure class="testimonial">
						<span class="testimonial__quote-icon"><?php qv_the_icon( 'quote', array( 'size' => 28 ) ); ?></span>
						<blockquote class="testimonial__text"><?php echo wp_kses_post( wpautop( $qv_item->post_content ) ); ?></blockquote>
						<figcaption class="testimonial__foot">
							<span class="testimonial__avatar" aria-hidden="true">
								<?php
								if ( has_post_thumbnail( $qv_item ) ) {
									echo get_the_post_thumbnail( $qv_item, 'thumbnail', array( 'alt' => '' ) );
								} else {
									echo esc_html( $qv_initial );
								}
								?>
							</span>
							<span>
								<cite class="testimonial__name"><?php echo esc_html( $qv_name ); ?></cite>
								<?php if ( $qv_role ) : ?>
									<span class="testimonial__role"><?php echo esc_html( $qv_role ); ?></span>
								<?php endif; ?>
							</span>
							<?php
							if ( $qv_rating ) {
								echo qv_stars( $qv_rating ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
						</figcaption>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
