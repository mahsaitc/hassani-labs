<?php
/**
 * Product catalogue: product categories grouped by platform and by field,
 * shown on the "Products" page (both languages) and in the Products menu.
 *
 * The WooCommerce store is English only, so each category also keeps a
 * Persian name and description in term meta (qv_name_fa, qv_desc_fa). On the
 * Persian site a category links to its card on the Persian Products page.
 * A category can list pages as its products (qv_cat_pages, English slugs),
 * e.g. the clinic software sales page under "Medical & healthcare".
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/**
 * Top-level categories that group the others, in display order.
 */
const QV_CATALOG_GROUPS = array( 'platform', 'industry' );

/**
 * Name or description of a product category in the current language.
 */
function qv_cat_text( $term, $field = 'name' ) {
	if ( is_rtl() ) {
		$fa = get_term_meta( $term->term_id, 'name' === $field ? 'qv_name_fa' : 'qv_desc_fa', true );
		if ( $fa ) {
			return $fa;
		}
	}
	return 'name' === $field ? $term->name : wp_strip_all_tags( $term->description );
}

/**
 * Groups with their categories: array of array( 'term' => WP_Term, 'children' => WP_Term[] ).
 */
function qv_catalog_groups() {
	$groups = array();
	foreach ( QV_CATALOG_GROUPS as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term ) {
			continue;
		}
		$children = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $term->term_id,
				'hide_empty' => false,
				'orderby'    => 'menu_order',
			)
		);
		$groups[] = array(
			'term'     => $term,
			'children' => is_wp_error( $children ) ? array() : $children,
		);
	}
	return $groups;
}

/**
 * Pages listed as products of a category, in the current language.
 *
 * @return WP_Post[]
 */
function qv_cat_pages( $term ) {
	$pages = array();
	foreach ( array_filter( array_map( 'trim', explode( ',', (string) get_term_meta( $term->term_id, 'qv_cat_pages', true ) ) ) ) as $path ) {
		$page = qv_page_by_path( $path );
		if ( $page && 'publish' === $page->post_status ) {
			$pages[] = $page;
		}
	}
	return $pages;
}

/**
 * Number of visible store products in a category (English store only).
 */
function qv_cat_product_count( $term ) {
	if ( is_rtl() || ! function_exists( 'wc_get_products' ) ) {
		return 0;
	}
	$count = (int) get_term_meta( $term->term_id, 'product_count_product_cat', true );
	return $count ? $count : (int) $term->count;
}

/**
 * The Products page (catalogue) in the current language, or the shop.
 */
function qv_products_page_url() {
	$page = qv_page_by_path( 'products' );
	if ( $page ) {
		return get_permalink( $page );
	}
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Where a category link goes: the store archive in English, its card on the
 * Products page in Persian.
 */
function qv_cat_url( $term ) {
	if ( is_rtl() ) {
		return qv_products_page_url() . '#cat-' . $term->slug;
	}
	$link = get_term_link( $term );
	return is_wp_error( $link ) ? qv_products_page_url() : $link;
}

/**
 * Category card: icon, name, description, its products and a custom-order
 * link when nothing is listed yet.
 */
function qv_cat_tile( $term ) {
	$icon  = get_term_meta( $term->term_id, 'qv_cat_icon', true );
	$pages = qv_cat_pages( $term );
	$count = qv_cat_product_count( $term );
	$empty = ! $pages && ! $count;
	?>
	<article class="cat-tile<?php echo $empty ? ' cat-tile--soon' : ''; ?>" id="cat-<?php echo esc_attr( $term->slug ); ?>" data-reveal>
		<span class="cat-tile__icon" aria-hidden="true"><?php qv_the_icon( $icon ? $icon : 'code', array( 'size' => 22 ) ); ?></span>
		<h3 class="cat-tile__title">
			<?php if ( ! is_rtl() ) : ?>
				<a href="<?php echo esc_url( qv_cat_url( $term ) ); ?>"><?php echo esc_html( qv_cat_text( $term ) ); ?></a>
			<?php else : ?>
				<?php echo esc_html( qv_cat_text( $term ) ); ?>
			<?php endif; ?>
		</h3>
		<p class="cat-tile__text"><?php echo esc_html( qv_cat_text( $term, 'description' ) ); ?></p>
		<?php if ( $pages ) : ?>
			<ul class="cat-tile__items">
				<?php foreach ( $pages as $page ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php qv_the_icon( 'check-circle', array( 'size' => 16 ) ); ?> <?php echo esc_html( get_the_title( $page ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( $count || $empty ) : ?>
		<div class="cat-tile__foot">
			<?php if ( $count ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( qv_cat_url( $term ) ); ?>">
					<?php
					/* translators: %d: product count */
					printf( esc_html( _n( 'Browse %d product', 'Browse %d products', $count, 'quantivista' ) ), (int) $count );
					?>
					<?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?>
				</a>
			<?php elseif ( $empty ) : ?>
				<span class="cat-tile__badge"><?php esc_html_e( 'Coming soon', 'quantivista' ); ?></span>
				<a class="link-arrow" href="<?php echo esc_url( qv_localize_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Order custom software', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</article>
	<?php
}

/**
 * Menu links to the Products page are saved as "#cat-…" (class
 * qv-catalog-link): point them at the Products page in the current language.
 */
add_filter(
	'nav_menu_link_attributes',
	function ( $atts, $item ) {
		if ( in_array( 'qv-catalog-link', (array) $item->classes, true ) && str_starts_with( (string) ( $atts['href'] ?? '' ), '#' ) ) {
			$atts['href'] = qv_products_page_url() . $atts['href'];
		}
		return $atts;
	},
	10,
	2
);
