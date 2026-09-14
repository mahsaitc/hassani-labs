<?php
/**
 * Product card — used in shop archives, home carousel, related products,
 * search results and the wishlist.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$qv_cat      = qv_product_primary_cat( $product );
$qv_heading  = ( is_shop() || is_product_taxonomy() ) ? 'h2' : 'h3';
$qv_rating   = (float) $product->get_average_rating();
$qv_reviews  = (int) $product->get_review_count();
$qv_excerpt  = wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 16 );
$qv_wishlist = ! empty( $GLOBALS['qv_wishlist_context'] );
?>
<li <?php wc_product_class( 'product-card', $product ); ?><?php echo $qv_wishlist ? ' data-wishlist-item' : ''; ?>>
	<div class="product-card__media">
		<a class="product-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
		<div class="product-card__badges">
			<?php if ( $product->is_on_sale() ) : ?>
				<?php echo apply_filters( 'woocommerce_sale_flash', '', get_post(), $product ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<?php if ( $product->is_featured() ) : ?>
				<span class="badge badge--featured"><?php esc_html_e( 'Featured', 'quantivista' ); ?></span>
			<?php endif; ?>
		</div>
		<?php
		if ( function_exists( 'qv_wishlist_button' ) ) {
			echo qv_wishlist_button( $product->get_id(), array( 'class' => 'product-card__wish' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</div>

	<div class="product-card__body">
		<div class="product-card__meta">
			<?php if ( $qv_cat ) : ?>
				<a class="product-card__cat" href="<?php echo esc_url( get_term_link( $qv_cat ) ); ?>"><?php echo esc_html( $qv_cat->name ); ?></a>
			<?php endif; ?>
			<?php echo qv_delivery_badge( $product ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<<?php echo esc_attr( $qv_heading ); ?> class="product-card__title woocommerce-loop-product__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</<?php echo esc_attr( $qv_heading ); ?>>

		<?php if ( $qv_reviews ) : ?>
			<div class="product-card__rating">
				<?php echo qv_stars( $qv_rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="product-card__reviews"><?php echo esc_html( number_format_i18n( $qv_rating, 1 ) ); ?> <span class="muted">(<?php echo esc_html( $qv_reviews ); ?>)</span></span>
			</div>
		<?php endif; ?>

		<?php if ( $qv_excerpt ) : ?>
			<p class="product-card__excerpt"><?php echo esc_html( $qv_excerpt ); ?></p>
		<?php endif; ?>

		<div class="product-card__foot">
			<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			<?php woocommerce_template_loop_add_to_cart(); ?>
		</div>
	</div>
</li>
