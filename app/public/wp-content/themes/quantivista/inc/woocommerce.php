<?php
/**
 * WooCommerce integration: hooks, product card helpers, tabs, license tier
 * selector and cart fragments. Template overrides live in /woocommerce.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
 * Assets & wrappers
 * ----------------------------------------------------------------------- */

// The theme ships complete store styles (assets/css/woocommerce.css).
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// Toolbar is rendered in archive-product.php.
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

add_filter( 'loop_shop_per_page', fn() => 12 );
add_filter( 'loop_shop_columns', fn() => 3 );
add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string' );

add_filter(
	'woocommerce_output_related_products_args',
	function ( $args ) {
		$args['posts_per_page'] = 3;
		$args['columns']        = 3;
		return $args;
	}
);

/* --------------------------------------------------------------------------
 * Product helpers
 * ----------------------------------------------------------------------- */

/**
 * "download" or "service".
 */
function qv_product_delivery( $product ) {
	if ( function_exists( 'qvc_product_delivery' ) ) {
		return qvc_product_delivery( $product );
	}
	return ( $product instanceof WC_Product && $product->is_virtual() && ! $product->is_downloadable() ) ? 'service' : 'download';
}

/**
 * Most specific category (a subcategory when assigned).
 */
function qv_product_primary_cat( $product ) {
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	usort( $terms, fn( $a, $b ) => (int) ( 0 === $a->parent ) - (int) ( 0 === $b->parent ) );
	return $terms[0];
}

function qv_delivery_badge( $product ) {
	$service = 'service' === qv_product_delivery( $product );
	return sprintf(
		'<span class="badge badge--%1$s">%2$s%3$s</span>',
		$service ? 'service' : 'download',
		qv_icon( $service ? 'users' : 'download', array( 'size' => 13 ) ),
		esc_html( $service ? __( 'Service', 'quantivista' ) : __( 'Download', 'quantivista' ) )
	);
}

/* --------------------------------------------------------------------------
 * Loop: buttons, prices, badges
 * ----------------------------------------------------------------------- */

add_filter(
	'woocommerce_loop_add_to_cart_args',
	function ( $args ) {
		$args['class'] = trim( ( $args['class'] ?? '' ) . ' btn btn--primary btn--sm' );
		return $args;
	}
);

add_filter(
	'woocommerce_product_add_to_cart_text',
	function ( $text, $product ) {
		if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return $text;
		}
		if ( $product->is_type( 'variable' ) ) {
			return __( 'Choose license', 'quantivista' );
		}
		if ( 'service' === qv_product_delivery( $product ) ) {
			return __( 'Order package', 'quantivista' );
		}
		return __( 'Add to cart', 'quantivista' );
	},
	10,
	2
);

add_filter(
	'woocommerce_product_single_add_to_cart_text',
	function ( $text, $product ) {
		return 'service' === qv_product_delivery( $product ) ? __( 'Order this package', 'quantivista' ) : __( 'Add to cart', 'quantivista' );
	},
	10,
	2
);

add_filter(
	'woocommerce_variable_price_html',
	function ( $price, $product ) {
		$min = $product->get_variation_price( 'min', true );
		$max = $product->get_variation_price( 'max', true );
		if ( $min !== $max ) {
			return '<span class="price-from">' . esc_html__( 'From', 'quantivista' ) . '</span> ' . wc_price( $min );
		}
		return $price;
	},
	10,
	2
);

add_filter(
	'woocommerce_sale_flash',
	function ( $html, $post, $product ) {
		$percent = 0;
		if ( $product->is_type( 'simple' ) && $product->get_regular_price() > 0 ) {
			$percent = round( 100 - ( (float) $product->get_sale_price() / (float) $product->get_regular_price() * 100 ) );
		}
		return '<span class="onsale badge badge--sale">' . ( $percent ? esc_html( sprintf( '-%d%%', $percent ) ) : esc_html__( 'Sale', 'quantivista' ) ) . '</span>';
	},
	10,
	3
);

/**
 * Header cart count via AJAX add-to-cart fragments.
 */
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

		$fragments['span.cart-count'] = '<span class="cart-count' . ( $count ? '' : ' is-empty' ) . '" aria-hidden="true">' . (int) $count . '</span>';

		/* translators: %d: number of items */
		$fragments['span.cart-count-label'] = '<span class="screen-reader-text cart-count-label">' . esc_html( sprintf( _n( 'Cart, %d item', 'Cart, %d items', $count, 'quantivista' ), $count ) ) . '</span>';

		return $fragments;
	}
);

/* --------------------------------------------------------------------------
 * Single product
 * ----------------------------------------------------------------------- */

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

add_action( 'woocommerce_single_product_summary', 'qv_single_eyebrow', 3 );
add_action( 'woocommerce_single_product_summary', 'qv_single_highlights', 25 );
add_action( 'woocommerce_single_product_summary', 'qv_single_actions', 35 );
add_action( 'woocommerce_single_product_summary', 'qv_single_meta', 40 );

function qv_single_eyebrow() {
	global $product;
	$cat = qv_product_primary_cat( $product );
	echo '<div class="product-eyebrow">';
	if ( $cat ) {
		printf( '<a class="pill" href="%s">%s</a>', esc_url( get_term_link( $cat ) ), esc_html( $cat->name ) );
	}
	echo qv_delivery_badge( $product ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</div>';
}

function qv_single_highlights() {
	global $product;
	$lines = qv_lines( qv_field( 'qv_highlights', $product->get_id() ) );
	if ( ! $lines ) {
		return;
	}
	echo '<ul class="check-list product-highlights">';
	foreach ( $lines as $line ) {
		echo '<li>' . qv_icon( 'check', array( 'size' => 16 ) ) . '<span>' . esc_html( $line ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</ul>';
}

function qv_single_actions() {
	global $product;
	$demo    = qv_field( 'qv_demo_url', $product->get_id() );
	$service = 'service' === qv_product_delivery( $product );
	?>
	<div class="product-actions">
		<?php
		if ( function_exists( 'qv_wishlist_button' ) ) {
			echo qv_wishlist_button( $product->get_id(), array( 'show_label' => true, 'class' => 'btn btn--outline btn--sm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $demo ) {
			printf(
				'<a class="btn btn--outline btn--sm" href="%1$s" target="_blank" rel="noopener">%2$s %3$s<span class="screen-reader-text"> %4$s</span></a>',
				esc_url( $demo ),
				esc_html__( 'Live demo', 'quantivista' ),
				qv_icon( 'arrow-up-right', array( 'size' => 16 ) ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html__( '(opens in a new tab)', 'quantivista' )
			);
		}
		?>
	</div>
	<ul class="trust-row">
		<li><?php qv_the_icon( 'lock', array( 'size' => 16 ) ); ?> <?php esc_html_e( 'Secure checkout', 'quantivista' ); ?></li>
		<li><?php qv_the_icon( 'refresh', array( 'size' => 16 ) ); ?> <?php $service ? esc_html_e( 'Milestone-based delivery', 'quantivista' ) : esc_html_e( '14-day money-back', 'quantivista' ); ?></li>
		<li><?php qv_the_icon( 'headphones', array( 'size' => 16 ) ); ?> <?php $service ? esc_html_e( 'Dedicated project manager', 'quantivista' ) : esc_html_e( '12 months support', 'quantivista' ); ?></li>
	</ul>
	<?php
}

function qv_single_meta() {
	global $product;
	$id      = $product->get_id();
	$service = 'service' === qv_product_delivery( $product );
	$rows    = array(
		__( 'Delivery', 'quantivista' )     => $service ? __( 'Delivered by our team', 'quantivista' ) : __( 'Instant download + license key', 'quantivista' ),
		__( 'Version', 'quantivista' )      => qv_field( 'qv_version', $id ),
		__( 'Last updated', 'quantivista' ) => qv_field( 'qv_last_updated', $id ),
		__( 'SKU', 'quantivista' )          => $product->get_sku(),
	);
	echo '<dl class="product-meta">';
	foreach ( array_filter( $rows ) as $label => $value ) {
		printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html( $label ), esc_html( $value ) );
	}
	$cats = wc_get_product_category_list( $id, ', ' );
	if ( $cats ) {
		printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html__( 'Category', 'quantivista' ), wp_kses_post( $cats ) );
	}
	echo '</dl>';
}

/**
 * Tabs: Overview, Features, Requirements / What's included, Reviews.
 */
add_filter(
	'woocommerce_product_tabs',
	function ( $tabs ) {
		global $product;
		if ( ! $product ) {
			return $tabs;
		}

		if ( isset( $tabs['description'] ) ) {
			$tabs['description']['title']    = __( 'Overview', 'quantivista' );
			$tabs['description']['priority'] = 10;
		}

		if ( qv_field( 'qv_features', $product->get_id() ) ) {
			$tabs['qv_features'] = array(
				'title'    => __( 'Features', 'quantivista' ),
				'priority' => 20,
				'callback' => 'qv_tab_features',
			);
		}

		if ( qv_field( 'qv_requirements', $product->get_id() ) ) {
			$tabs['qv_requirements'] = array(
				'title'    => 'service' === qv_product_delivery( $product ) ? __( 'What\'s Included', 'quantivista' ) : __( 'Requirements', 'quantivista' ),
				'priority' => 30,
				'callback' => 'qv_tab_requirements',
			);
		}

		unset( $tabs['additional_information'] );

		if ( isset( $tabs['reviews'] ) ) {
			$tabs['reviews']['priority'] = 40;
		}

		return $tabs;
	},
	98
);

function qv_tab_features() {
	global $product;
	echo '<div class="feature-grid">';
	foreach ( qv_lines( qv_field( 'qv_features', $product->get_id() ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		echo '<div class="feature-item">';
		echo '<span class="feature-item__icon">' . qv_icon( 'check', array( 'size' => 18 ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div><h3 class="feature-item__title">' . esc_html( $parts[0] ) . '</h3>';
		if ( ! empty( $parts[1] ) ) {
			echo '<p class="feature-item__text">' . esc_html( $parts[1] ) . '</p>';
		}
		echo '</div></div>';
	}
	echo '</div>';
}

function qv_tab_requirements() {
	global $product;
	echo '<table class="spec-table"><tbody>';
	foreach ( qv_lines( qv_field( 'qv_requirements', $product->get_id() ) ) as $line ) {
		$parts = array_map( 'trim', explode( ':', $line, 2 ) );
		printf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			esc_html( $parts[0] ),
			esc_html( $parts[1] ?? '' )
		);
	}
	echo '</tbody></table>';
}

/**
 * License tiers: render the "License" variation attribute as selectable
 * cards. The original <select> stays in the DOM (visually hidden) because
 * WooCommerce's variation script reads it; main.js keeps both in sync.
 */
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'qv_license_tier_cards', 10, 2 );
function qv_license_tier_cards( $html, $args ) {
	if ( 'pa_license' !== ( $args['attribute'] ?? '' ) || empty( $args['product'] ) ) {
		return $html;
	}

	$product   = $args['product'];
	$select_id = $args['id'] ? $args['id'] : sanitize_title( $args['attribute'] );
	$terms     = wc_get_product_terms( $product->get_id(), 'pa_license', array( 'fields' => 'all' ) );

	$prices = array();
	foreach ( $product->get_available_variations( 'objects' ) as $variation ) {
		$slug = $variation->get_attributes()['pa_license'] ?? '';
		if ( $slug ) {
			$prices[ $slug ] = $variation;
		}
	}

	$html = str_replace( '<select ', '<select tabindex="-1" aria-hidden="true" ', $html );

	ob_start();
	?>
	<div class="tier-select-native"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- core-generated markup. ?></div>
	<fieldset class="tier-options" data-tier-group="<?php echo esc_attr( $select_id ); ?>">
		<legend class="screen-reader-text"><?php esc_html_e( 'Choose a license', 'quantivista' ); ?></legend>
		<?php foreach ( $terms as $term ) : ?>
			<?php
			if ( ! isset( $prices[ $term->slug ] ) ) {
				continue;
			}
			$variation = $prices[ $term->slug ];
			$popular   = (bool) get_term_meta( $term->term_id, 'qv_popular', true );
			$input_id  = $select_id . '-' . $term->slug;
			?>
			<label class="tier-option<?php echo $popular ? ' tier-option--popular' : ''; ?>" for="<?php echo esc_attr( $input_id ); ?>">
				<input class="tier-option__input" type="radio" id="<?php echo esc_attr( $input_id ); ?>" name="qv_tier_<?php echo esc_attr( $select_id ); ?>" value="<?php echo esc_attr( $term->slug ); ?>">
				<span class="tier-option__card">
					<span class="tier-option__head">
						<span class="tier-option__name"><?php echo esc_html( $term->name ); ?></span>
						<?php if ( $popular ) : ?>
							<span class="tier-option__flag"><?php esc_html_e( 'Most popular', 'quantivista' ); ?></span>
						<?php endif; ?>
					</span>
					<?php if ( $term->description ) : ?>
						<span class="tier-option__desc"><?php echo esc_html( $term->description ); ?></span>
					<?php endif; ?>
					<span class="tier-option__price"><?php echo wp_kses_post( $variation->get_price_html() ); ?></span>
				</span>
			</label>
		<?php endforeach; ?>
	</fieldset>
	<?php
	return ob_get_clean();
}

/* --------------------------------------------------------------------------
 * Account
 * ----------------------------------------------------------------------- */

add_filter(
	'woocommerce_account_menu_item_classes',
	function ( $classes, $endpoint ) {
		$classes[] = 'account-nav__item';
		return $classes;
	},
	10,
	2
);

/**
 * Small icons for the account navigation.
 */
function qv_account_icon( $endpoint ) {
	$map = array(
		'dashboard'       => 'grid',
		'orders'          => 'package',
		'downloads'       => 'download',
		'licenses'        => 'key',
		'wishlist'        => 'heart',
		'edit-address'    => 'map-pin',
		'payment-methods' => 'lock',
		'edit-account'    => 'user',
		'customer-logout' => 'log-out',
	);
	return qv_icon( $map[ $endpoint ] ?? 'chevron-right', array( 'size' => 18 ) );
}
