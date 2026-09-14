<?php
/**
 * Search results: products and articles shown in separate groups.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

$qv_term = get_search_query();

qv_page_hero(
	array(
		'eyebrow' => __( 'Search', 'quantivista' ),
		/* translators: %s: search term */
		'title'   => sprintf( __( 'Results for &ldquo;%s&rdquo;', 'quantivista' ), esc_html( $qv_term ) ),
	)
);

$qv_products = class_exists( 'WooCommerce' ) ? new WP_Query(
	array(
		's'              => $qv_term,
		'post_type'      => 'product',
		'posts_per_page' => 6,
		'no_found_rows'  => true,
		'tax_query'      => WC()->query->get_tax_query(), // phpcs:ignore WordPress.DB.SlowDBQuery -- respects catalog visibility.
	)
) : null;

$qv_posts = new WP_Query(
	array(
		's'              => $qv_term,
		'post_type'      => array( 'post', 'page' ),
		'posts_per_page' => 9,
		'paged'          => max( 1, get_query_var( 'paged' ) ),
	)
);
?>
<section class="section section--tight">
	<div class="container">
		<div class="search-page__form"><?php get_search_form(); ?></div>

		<?php if ( $qv_products && $qv_products->have_posts() ) : ?>
			<div class="search-group">
				<h2 class="search-group__title"><?php esc_html_e( 'Products', 'quantivista' ); ?></h2>
				<ul class="products product-grid columns-3">
					<?php
					while ( $qv_products->have_posts() ) :
						$qv_products->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="search-group">
			<h2 class="search-group__title"><?php esc_html_e( 'Articles & pages', 'quantivista' ); ?></h2>
			<?php if ( $qv_posts->have_posts() ) : ?>
				<ul class="search-list">
					<?php
					while ( $qv_posts->have_posts() ) :
						$qv_posts->the_post();
						?>
						<li class="search-list__item">
							<p class="search-list__type"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></p>
							<h3 class="search-list__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="search-list__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			<?php elseif ( ! $qv_products || ! $qv_products->have_posts() ) : ?>
				<div class="empty-state">
					<?php qv_the_icon( 'search', array( 'size' => 40 ) ); ?>
					<h3><?php esc_html_e( 'No matches found', 'quantivista' ); ?></h3>
					<p><?php esc_html_e( 'Try a broader keyword such as “CRM”, “automation” or “mobile”.', 'quantivista' ); ?></p>
				</div>
			<?php else : ?>
				<p class="muted"><?php esc_html_e( 'No articles matched your search.', 'quantivista' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
