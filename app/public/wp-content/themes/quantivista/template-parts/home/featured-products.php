<?php
/**
 * Featured products carousel.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_products = wc_get_products(
	array(
		'status'     => 'publish',
		'featured'   => true,
		'visibility' => 'catalog',
		'limit'      => 8,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	)
);

if ( count( $qv_products ) < 3 ) {
	$qv_products = wc_get_products( array( 'status' => 'publish', 'visibility' => 'catalog', 'limit' => 8, 'orderby' => 'popularity' ) );
}

if ( ! $qv_products ) {
	return;
}
?>
<section class="section" aria-labelledby="featured-title" data-carousel>
	<div class="container">
		<div class="section-head-row">
			<?php
			qv_section_head(
				array(
					'eyebrow' => __( 'Featured products', 'quantivista' ),
					'title'   => __( 'Software you can buy today', 'quantivista' ),
					'id'      => 'featured-title',
					'align'   => 'left',
				)
			);
			?>
			<div class="carousel__nav">
				<a class="btn btn--outline btn--sm" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'View all', 'quantivista' ); ?></a>
				<button type="button" class="carousel__btn" data-carousel-prev>
					<?php qv_the_icon( 'chevron-left' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Previous products', 'quantivista' ); ?></span>
				</button>
				<button type="button" class="carousel__btn" data-carousel-next>
					<?php qv_the_icon( 'chevron-right' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Next products', 'quantivista' ); ?></span>
				</button>
			</div>
		</div>

		<ul class="carousel__track carousel__track--4" data-carousel-track>
			<?php
			foreach ( $qv_products as $qv_product ) {
				$GLOBALS['post']    = get_post( $qv_product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				$GLOBALS['product'] = $qv_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				setup_postdata( $GLOBALS['post'] );
				get_template_part( 'template-parts/product/card' );
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
</section>
