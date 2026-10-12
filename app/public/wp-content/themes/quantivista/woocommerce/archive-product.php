<?php
/**
 * The Template for displaying product archives, including the main shop page
 * which is a post type archive.
 *
 * Override: hero with subcategory chips, filter sidebar/drawer, toolbar.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Quantivista\WooCommerce
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$qv_term = is_product_category() ? get_queried_object() : null;
$qv_lead = '';
if ( $qv_term && $qv_term->description ) {
	$qv_lead = wp_strip_all_tags( $qv_term->description );
} elseif ( is_shop() ) {
	$qv_lead = __( 'Clinic software, web applications and website packages — built, documented and supported by Hassani Labs.', 'quantivista' );
}

$qv_eyebrow = __( 'Store', 'quantivista' );
if ( $qv_term ) {
	$qv_parent  = $qv_term->parent ? get_term( $qv_term->parent, 'product_cat' ) : null;
	$qv_eyebrow = $qv_parent ? $qv_parent->name : __( 'Products', 'quantivista' );
} elseif ( is_search() ) {
	$qv_eyebrow = __( 'Search', 'quantivista' );
}

// Chips: children of the current category (or siblings for a leaf), top-level on the shop page.
$qv_chip_parent = 0;
if ( $qv_term ) {
	$qv_has_children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $qv_term->term_id, 'hide_empty' => true, 'fields' => 'ids' ) );
	$qv_chip_parent  = $qv_has_children ? $qv_term->term_id : $qv_term->parent;
}
$qv_chips = ( is_shop() || $qv_term ) ? get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => $qv_chip_parent,
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'menu_order',
	)
) : array();
?>
<section class="page-hero shop-hero">
	<div class="page-hero__glow" aria-hidden="true"></div>
	<div class="container page-hero__inner">
		<?php qv_breadcrumbs(); ?>
		<p class="eyebrow"><?php echo esc_html( $qv_eyebrow ); ?></p>
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="page-hero__title woocommerce-products-header__title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>
		<?php if ( $qv_lead ) : ?>
			<p class="page-hero__lead"><?php echo esc_html( $qv_lead ); ?></p>
		<?php endif; ?>

		<?php if ( $qv_chips && ! is_wp_error( $qv_chips ) ) : ?>
			<nav class="shop-hero__chips" aria-label="<?php esc_attr_e( 'Categories', 'quantivista' ); ?>">
				<?php if ( $qv_chip_parent ) : ?>
					<?php $qv_parent_term = get_term( $qv_chip_parent, 'product_cat' ); ?>
					<a class="chip chip--dark<?php echo ( $qv_term && $qv_term->term_id === $qv_chip_parent ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $qv_parent_term ) ); ?>"><?php esc_html_e( 'All', 'quantivista' ); ?></a>
				<?php endif; ?>
				<?php foreach ( $qv_chips as $qv_chip ) : ?>
					<?php $qv_on = $qv_term && $qv_term->term_id === $qv_chip->term_id; ?>
					<a class="chip chip--dark<?php echo $qv_on ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $qv_chip ) ); ?>"<?php echo $qv_on ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $qv_chip->name ); ?> <span class="chip__count"><?php echo esc_html( $qv_chip->count ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>
</section>

<section class="section section--tight shop">
	<div class="container shop-layout">
		<aside id="shop-filters" class="shop-filters" tabindex="-1" aria-label="<?php esc_attr_e( 'Product filters', 'quantivista' ); ?>">
			<div class="shop-filters__head">
				<h2 class="shop-filters__title"><?php qv_the_icon( 'sliders', array( 'size' => 18 ) ); ?> <?php esc_html_e( 'Filters', 'quantivista' ); ?></h2>
				<button type="button" class="shop-filters__close" data-filter-close>
					<?php qv_the_icon( 'close' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Close filters', 'quantivista' ); ?></span>
				</button>
			</div>
			<?php get_template_part( 'template-parts/product/filters' ); ?>
			<?php if ( is_active_sidebar( 'shop-sidebar' ) ) : ?>
				<div class="shop-filters__widgets"><?php dynamic_sidebar( 'shop-sidebar' ); ?></div>
			<?php endif; ?>
		</aside>

		<div class="shop-main">
			<?php
			/**
			 * Hook: woocommerce_before_main_content.
			 *
			 * @hooked WC_Structured_Data::generate_website_data() - 30
			 */
			do_action( 'woocommerce_before_main_content' );
			?>

			<div class="shop-toolbar">
				<button type="button" class="btn btn--outline btn--sm shop-toolbar__filters" data-filter-toggle aria-controls="shop-filters" aria-expanded="false">
					<?php qv_the_icon( 'sliders', array( 'size' => 16 ) ); ?> <?php esc_html_e( 'Filters', 'quantivista' ); ?>
				</button>
				<?php
				if ( woocommerce_product_loop() ) {
					woocommerce_result_count();
					woocommerce_catalog_ordering();
				}
				?>
			</div>

			<?php
			if ( woocommerce_product_loop() ) {
				/**
				 * Hook: woocommerce_before_shop_loop.
				 *
				 * @hooked woocommerce_output_all_notices - 10
				 */
				do_action( 'woocommerce_before_shop_loop' );

				woocommerce_product_loop_start();

				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();

						/**
						 * Hook: woocommerce_shop_loop.
						 */
						do_action( 'woocommerce_shop_loop' );

						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();

				/**
				 * Hook: woocommerce_after_shop_loop.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			} else {
				/**
				 * Hook: woocommerce_no_products_found.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
			}

			/**
			 * Hook: woocommerce_after_main_content.
			 */
			do_action( 'woocommerce_after_main_content' );
			?>
		</div>
	</div>
	<div class="shop-filters-backdrop" data-filter-close aria-hidden="true"></div>
</section>
<?php
get_footer( 'shop' );
