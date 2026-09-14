<?php
/**
 * Default page template.
 *
 * WooCommerce cart/checkout/account pages render full-width without the
 * prose wrapper so their own layouts apply.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

$qv_is_wc_page = ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) )
	|| has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'qv_wishlist' );

while ( have_posts() ) :
	the_post();

	qv_page_hero(
		array(
			'title' => get_the_title(),
			'lead'  => has_excerpt() ? get_the_excerpt() : '',
			'class' => $qv_is_wc_page ? 'page-hero--compact' : '',
		)
	);
	?>
	<section class="section section--tight">
		<div class="container<?php echo $qv_is_wc_page ? '' : ' container--narrow'; ?>">
			<div class="<?php echo $qv_is_wc_page ? 'wc-page' : 'prose entry-content'; ?>">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
