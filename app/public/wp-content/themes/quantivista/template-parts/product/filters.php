<?php
/**
 * Shop filters: category tree, price range, rating, delivery type.
 * Uses WooCommerce's native query vars (min_price, max_price, rating_filter)
 * plus ?delivery handled in quantivista-core/shop.php.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only GET filters.
$qv_current   = is_product_category() ? get_queried_object() : null;
$qv_base      = $qv_current ? get_term_link( $qv_current ) : wc_get_page_permalink( 'shop' );
$qv_min       = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : '';
$qv_max       = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : '';
$qv_rating    = isset( $_GET['rating_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) : '';
$qv_delivery  = isset( $_GET['delivery'] ) ? sanitize_key( wp_unslash( $_GET['delivery'] ) ) : '';
$qv_orderby   = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : '';
$qv_keep_args = array_filter(
	array(
		'min_price'     => $qv_min,
		'max_price'     => $qv_max,
		'rating_filter' => $qv_rating,
		'delivery'      => $qv_delivery,
		'orderby'       => $qv_orderby,
	)
);
// phpcs:enable

$qv_top_cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'menu_order',
	)
);
?>
<form class="filter-form" method="get" action="<?php echo esc_url( $qv_base ); ?>">
	<?php if ( $qv_orderby ) : ?>
		<input type="hidden" name="orderby" value="<?php echo esc_attr( $qv_orderby ); ?>">
	<?php endif; ?>

	<fieldset class="filter-group">
		<legend class="filter-group__title"><?php esc_html_e( 'Category', 'quantivista' ); ?></legend>
		<ul class="filter-cats">
			<li>
				<a class="filter-cats__link<?php echo $qv_current ? '' : ' is-active'; ?>" href="<?php echo esc_url( add_query_arg( $qv_keep_args, wc_get_page_permalink( 'shop' ) ) ); ?>"<?php echo $qv_current ? '' : ' aria-current="page"'; ?>>
					<?php esc_html_e( 'All products', 'quantivista' ); ?>
				</a>
			</li>
			<?php foreach ( $qv_top_cats as $qv_cat ) : ?>
				<?php
				$qv_children = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'parent'     => $qv_cat->term_id,
						'hide_empty' => true,
						'orderby'    => 'menu_order',
					)
				);
				$qv_active   = $qv_current && ( $qv_current->term_id === $qv_cat->term_id );
				$qv_branch   = $qv_current && ( $qv_active || $qv_current->parent === $qv_cat->term_id );
				?>
				<li class="<?php echo $qv_branch ? 'is-branch' : ''; ?>">
					<a class="filter-cats__link<?php echo $qv_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( $qv_keep_args, get_term_link( $qv_cat ) ) ); ?>"<?php echo $qv_active ? ' aria-current="page"' : ''; ?>>
						<span><?php echo esc_html( $qv_cat->name ); ?></span>
						<span class="filter-cats__count"><?php echo esc_html( $qv_cat->count ); ?></span>
					</a>
					<?php if ( $qv_children && ! is_wp_error( $qv_children ) ) : ?>
						<ul>
							<?php foreach ( $qv_children as $qv_child ) : ?>
								<?php $qv_child_active = $qv_current && $qv_current->term_id === $qv_child->term_id; ?>
								<li>
									<a class="filter-cats__link filter-cats__link--child<?php echo $qv_child_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( $qv_keep_args, get_term_link( $qv_child ) ) ); ?>"<?php echo $qv_child_active ? ' aria-current="page"' : ''; ?>>
										<span><?php echo esc_html( $qv_child->name ); ?></span>
										<span class="filter-cats__count"><?php echo esc_html( $qv_child->count ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>

	<fieldset class="filter-group">
		<legend class="filter-group__title"><?php esc_html_e( 'Price', 'quantivista' ); ?> <span class="muted">(<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>)</span></legend>
		<div class="price-inputs">
			<div>
				<label for="filter-min-price"><?php esc_html_e( 'Min', 'quantivista' ); ?></label>
				<input type="number" id="filter-min-price" name="min_price" min="0" step="1" inputmode="numeric" placeholder="0" value="<?php echo esc_attr( $qv_min ); ?>">
			</div>
			<span class="price-inputs__sep" aria-hidden="true">–</span>
			<div>
				<label for="filter-max-price"><?php esc_html_e( 'Max', 'quantivista' ); ?></label>
				<input type="number" id="filter-max-price" name="max_price" min="0" step="1" inputmode="numeric" placeholder="10000" value="<?php echo esc_attr( $qv_max ); ?>">
			</div>
		</div>
		<div class="price-presets">
			<?php
			$qv_presets = array(
				array( __( 'Under $100', 'quantivista' ), '', 100 ),
				array( __( '$100–$500', 'quantivista' ), 100, 500 ),
				array( __( '$500+', 'quantivista' ), 500, '' ),
			);
			foreach ( $qv_presets as $qv_preset ) :
				$qv_args = array_merge( $qv_keep_args, array( 'min_price' => $qv_preset[1], 'max_price' => $qv_preset[2] ) );
				$qv_url  = remove_query_arg( array_keys( array_filter( $qv_args, fn( $v ) => '' === $v ) ), add_query_arg( array_filter( $qv_args ), $qv_base ) );
				$qv_on   = (string) $qv_min === (string) $qv_preset[1] && (string) $qv_max === (string) $qv_preset[2];
				?>
				<a class="chip<?php echo $qv_on ? ' is-active' : ''; ?>" href="<?php echo esc_url( $qv_url ); ?>"><?php echo esc_html( $qv_preset[0] ); ?></a>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset class="filter-group">
		<legend class="filter-group__title"><?php esc_html_e( 'Rating', 'quantivista' ); ?></legend>
		<?php
		$qv_ratings = array(
			''      => __( 'Any rating', 'quantivista' ),
			'5'     => '5',
			'4,5'   => '4',
			'3,4,5' => '3',
		);
		foreach ( $qv_ratings as $qv_value => $qv_label ) :
			$qv_id = 'rating-' . ( $qv_value ? str_replace( ',', '', $qv_value ) : 'any' );
			?>
			<div class="filter-radio">
				<input type="radio" id="<?php echo esc_attr( $qv_id ); ?>" name="rating_filter" value="<?php echo esc_attr( $qv_value ); ?>" <?php checked( $qv_rating, $qv_value ); ?>>
				<label for="<?php echo esc_attr( $qv_id ); ?>">
					<?php if ( $qv_value ) : ?>
						<?php echo qv_stars( (int) $qv_label ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><?php echo '5' === $qv_label ? esc_html__( '5 only', 'quantivista' ) : esc_html( sprintf( /* translators: %s: stars */ __( '%s & up', 'quantivista' ), $qv_label ) ); ?></span>
					<?php else : ?>
						<span><?php echo esc_html( $qv_label ); ?></span>
					<?php endif; ?>
				</label>
			</div>
		<?php endforeach; ?>
	</fieldset>

	<fieldset class="filter-group">
		<legend class="filter-group__title"><?php esc_html_e( 'Delivery', 'quantivista' ); ?></legend>
		<?php
		$qv_types = array(
			''         => __( 'All', 'quantivista' ),
			'download' => __( 'Software download', 'quantivista' ),
			'service'  => __( 'Service package', 'quantivista' ),
		);
		foreach ( $qv_types as $qv_value => $qv_label ) :
			$qv_id = 'delivery-' . ( $qv_value ? $qv_value : 'all' );
			?>
			<div class="filter-radio">
				<input type="radio" id="<?php echo esc_attr( $qv_id ); ?>" name="delivery" value="<?php echo esc_attr( $qv_value ); ?>" <?php checked( $qv_delivery, $qv_value ); ?>>
				<label for="<?php echo esc_attr( $qv_id ); ?>"><?php echo esc_html( $qv_label ); ?></label>
			</div>
		<?php endforeach; ?>
	</fieldset>

	<div class="filter-actions">
		<button type="submit" class="btn btn--primary btn--block"><?php esc_html_e( 'Apply filters', 'quantivista' ); ?></button>
		<?php if ( $qv_keep_args ) : ?>
			<a class="filter-reset" href="<?php echo esc_url( $qv_base ); ?>"><?php esc_html_e( 'Reset all', 'quantivista' ); ?></a>
		<?php endif; ?>
	</div>
</form>
