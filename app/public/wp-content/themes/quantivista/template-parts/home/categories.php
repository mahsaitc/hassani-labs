<?php
/**
 * Three top-level product categories as visual cards.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_terms = array_filter(
	array_map(
		fn( $slug ) => get_term_by( 'slug', $slug, 'product_cat' ),
		apply_filters( 'qv_home_category_slugs', array( 'web-software', 'mobile-apps', 'website-design' ) )
	)
);

if ( ! $qv_terms ) {
	return;
}
?>
<section class="section section--muted" aria-labelledby="categories-title">
	<div class="container">
		<?php
		qv_section_head(
			array(
				'eyebrow' => __( 'What we build', 'quantivista' ),
				'title'   => __( 'Software and services for every stage of growth', 'quantivista' ),
				'lead'    => __( 'Buy a proven product and launch this week, or partner with our studio for a website designed around your customers.', 'quantivista' ),
				'id'      => 'categories-title',
			)
		);
		?>
		<div class="grid grid--3">
			<?php foreach ( $qv_terms as $qv_term ) : ?>
				<?php
				$qv_thumb    = (int) get_term_meta( $qv_term->term_id, 'thumbnail_id', true );
				$qv_icon     = get_term_meta( $qv_term->term_id, 'qv_cat_icon', true );
				$qv_count    = (int) get_term_meta( $qv_term->term_id, 'product_count_product_cat', true );
				$qv_count    = $qv_count ? $qv_count : (int) $qv_term->count;
				$qv_children = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'parent'     => $qv_term->term_id,
						'hide_empty' => false,
						'number'     => 4,
						'orderby'    => 'menu_order',
					)
				);
				?>
				<article class="cat-card" data-reveal>
					<div class="cat-card__media">
						<?php
						if ( $qv_thumb ) {
							echo wp_get_attachment_image( $qv_thumb, 'qv-card', false, array( 'alt' => '', 'sizes' => '(min-width: 960px) 33vw, (min-width: 640px) 50vw, 100vw' ) );
						}
						?>
						<span class="cat-card__icon"><?php qv_the_icon( $qv_icon ? $qv_icon : 'code', array( 'size' => 22 ) ); ?></span>
					</div>
					<div class="cat-card__body">
						<h3 class="cat-card__title"><a href="<?php echo esc_url( get_term_link( $qv_term ) ); ?>"><?php echo esc_html( $qv_term->name ); ?></a></h3>
						<p class="cat-card__text"><?php echo esc_html( wp_strip_all_tags( $qv_term->description ) ); ?></p>
						<?php if ( $qv_children && ! is_wp_error( $qv_children ) ) : ?>
							<ul class="cat-card__sub">
								<?php foreach ( $qv_children as $qv_child ) : ?>
									<li><?php echo esc_html( $qv_child->name ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<div class="cat-card__foot">
							<span>
								<?php
								/* translators: %d: product count */
								printf( esc_html( _n( '%d product', '%d products', $qv_count, 'quantivista' ) ), (int) $qv_count );
								?>
							</span>
							<span class="link-arrow" aria-hidden="true"><?php esc_html_e( 'Explore', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></span>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
