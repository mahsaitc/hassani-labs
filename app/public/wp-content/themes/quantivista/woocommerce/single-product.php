<?php
/**
 * The Template for displaying all single products
 *
 * Override: breadcrumb bar + theme layout (see content-single-product.php).
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     Quantivista\WooCommerce
 * @version     1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>
<div class="product-crumbs">
	<div class="container">
		<?php qv_breadcrumbs(); ?>
	</div>
</div>

<?php
/**
 * Hook: woocommerce_before_main_content.
 */
do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) :
	the_post();
	wc_get_template_part( 'content', 'single-product' );
endwhile;

/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
