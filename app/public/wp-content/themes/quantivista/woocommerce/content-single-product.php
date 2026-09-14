<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override: two-column hero (gallery + sticky summary), full-width tabs and
 * related products. All core hooks are preserved for extension compatibility.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Quantivista\WooCommerce
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="container product-notices">
	<?php
	/**
	 * Hook: woocommerce_before_single_product.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 */
	do_action( 'woocommerce_before_single_product' );
	?>
</div>
<?php
if ( post_password_required() ) {
	echo '<div class="container">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-single', $product ); ?>>
	<section class="product-hero">
		<div class="container product-hero__grid">
			<div class="product-hero__gallery">
				<?php
				/**
				 * Hook: woocommerce_before_single_product_summary.
				 *
				 * @hooked woocommerce_show_product_sale_flash - 10
				 * @hooked woocommerce_show_product_images - 20
				 */
				do_action( 'woocommerce_before_single_product_summary' );
				?>
			</div>

			<div class="summary entry-summary product-hero__summary">
				<?php
				/**
				 * Hook: woocommerce_single_product_summary.
				 *
				 * @hooked qv_single_eyebrow - 3
				 * @hooked woocommerce_template_single_title - 5
				 * @hooked woocommerce_template_single_rating - 10
				 * @hooked woocommerce_template_single_price - 10
				 * @hooked woocommerce_template_single_excerpt - 20
				 * @hooked qv_single_highlights - 25
				 * @hooked woocommerce_template_single_add_to_cart - 30
				 * @hooked qv_single_actions - 35
				 * @hooked qv_single_meta - 40
				 * @hooked WC_Structured_Data::generate_product_data() - 60
				 */
				do_action( 'woocommerce_single_product_summary' );
				?>
			</div>
		</div>
	</section>

	<div class="product-details">
		<div class="container">
			<?php
			/**
			 * Hook: woocommerce_after_single_product_summary.
			 *
			 * @hooked woocommerce_output_product_data_tabs - 10
			 * @hooked woocommerce_upsell_display - 15
			 * @hooked woocommerce_output_related_products - 20
			 */
			do_action( 'woocommerce_after_single_product_summary' );
			?>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
