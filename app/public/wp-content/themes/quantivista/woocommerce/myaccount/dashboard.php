<?php
/**
 * My Account Dashboard
 *
 * Override: personalised overview with stats, "My Orders" and "My Downloads".
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Quantivista\WooCommerce
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$qv_user        = wp_get_current_user();
$qv_customer_id = get_current_user_id();
$qv_orders      = wc_get_orders(
	array(
		'customer_id' => $qv_customer_id,
		'limit'       => 5,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
$qv_order_count = wc_get_customer_order_count( $qv_customer_id );
$qv_downloads   = WC()->customer ? WC()->customer->get_downloadable_products() : array();
$qv_licenses    = function_exists( 'qvc_get_customer_licenses' ) ? qvc_get_customer_licenses( $qv_customer_id ) : array();
$qv_wishlist    = function_exists( 'qvc_wishlist_get' ) ? qvc_wishlist_get() : array();
$qv_name        = $qv_user->first_name ? $qv_user->first_name : $qv_user->display_name;

$qv_stats = array(
	array( 'package', $qv_order_count, __( 'Orders', 'quantivista' ), wc_get_account_endpoint_url( 'orders' ) ),
	array( 'download', count( $qv_downloads ), __( 'Downloads', 'quantivista' ), wc_get_account_endpoint_url( 'downloads' ) ),
	array( 'key', count( $qv_licenses ), __( 'License keys', 'quantivista' ), wc_get_account_endpoint_url( 'licenses' ) ),
	array( 'heart', count( $qv_wishlist ), __( 'Saved items', 'quantivista' ), wc_get_account_endpoint_url( 'wishlist' ) ),
);
?>
<div class="account-welcome">
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Dashboard', 'quantivista' ); ?></p>
		<h2 class="account-welcome__title">
			<?php
			/* translators: %s: customer first name */
			printf( esc_html__( 'Welcome back, %s', 'quantivista' ), esc_html( $qv_name ) );
			?>
		</h2>
		<p class="account-welcome__text"><?php esc_html_e( 'Track orders, download your software, copy license keys and manage your billing details — all in one place.', 'quantivista' ); ?></p>
	</div>
	<a class="btn btn--outline btn--sm" href="<?php echo esc_url( wc_logout_url() ); ?>"><?php qv_the_icon( 'log-out', array( 'size' => 16 ) ); ?> <?php esc_html_e( 'Log out', 'quantivista' ); ?></a>
</div>

<ul class="account-stats">
	<?php foreach ( $qv_stats as $qv_stat ) : ?>
		<li>
			<a class="account-stat" href="<?php echo esc_url( $qv_stat[3] ); ?>">
				<span class="account-stat__icon"><?php qv_the_icon( $qv_stat[0], array( 'size' => 20 ) ); ?></span>
				<span class="account-stat__value"><?php echo esc_html( number_format_i18n( $qv_stat[1] ) ); ?></span>
				<span class="account-stat__label"><?php echo esc_html( $qv_stat[2] ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>

<section class="account-panel" aria-labelledby="account-orders-title">
	<div class="account-panel__head">
		<h3 id="account-orders-title"><?php esc_html_e( 'My Orders', 'quantivista' ); ?></h3>
		<?php if ( $qv_orders ) : ?>
			<a class="link-arrow" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'View all', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
		<?php endif; ?>
	</div>

	<?php if ( $qv_orders ) : ?>
		<table class="shop_table shop_table_responsive account-orders-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Order', 'quantivista' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Date', 'quantivista' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'quantivista' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Total', 'quantivista' ); ?></th>
					<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'quantivista' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $qv_orders as $qv_order ) : ?>
					<tr>
						<td data-title="<?php esc_attr_e( 'Order', 'quantivista' ); ?>"><a href="<?php echo esc_url( $qv_order->get_view_order_url() ); ?>">#<?php echo esc_html( $qv_order->get_order_number() ); ?></a></td>
						<td data-title="<?php esc_attr_e( 'Date', 'quantivista' ); ?>"><time datetime="<?php echo esc_attr( $qv_order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $qv_order->get_date_created() ) ); ?></time></td>
						<td data-title="<?php esc_attr_e( 'Status', 'quantivista' ); ?>"><span class="status-badge status-badge--<?php echo esc_attr( $qv_order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $qv_order->get_status() ) ); ?></span></td>
						<td data-title="<?php esc_attr_e( 'Total', 'quantivista' ); ?>"><?php echo wp_kses_post( $qv_order->get_formatted_order_total() ); ?></td>
						<td><a class="btn btn--outline btn--sm" href="<?php echo esc_url( $qv_order->get_view_order_url() ); ?>"><?php esc_html_e( 'View', 'quantivista' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="account-empty">
			<p><?php esc_html_e( 'You haven\'t placed any orders yet.', 'quantivista' ); ?></p>
			<a class="btn btn--primary btn--sm" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse products', 'quantivista' ); ?></a>
		</div>
	<?php endif; ?>
</section>

<section class="account-panel" aria-labelledby="account-downloads-title">
	<div class="account-panel__head">
		<h3 id="account-downloads-title"><?php esc_html_e( 'My Downloads', 'quantivista' ); ?></h3>
		<?php if ( $qv_downloads ) : ?>
			<a class="link-arrow" href="<?php echo esc_url( wc_get_account_endpoint_url( 'downloads' ) ); ?>"><?php esc_html_e( 'View all', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
		<?php endif; ?>
	</div>

	<?php if ( $qv_downloads ) : ?>
		<ul class="download-list">
			<?php foreach ( array_slice( $qv_downloads, 0, 5 ) as $qv_download ) : ?>
				<li class="download-list__item">
					<span class="download-list__icon"><?php qv_the_icon( 'file-text', array( 'size' => 20 ) ); ?></span>
					<span class="download-list__body">
						<a class="download-list__product" href="<?php echo esc_url( get_permalink( $qv_download['product_id'] ) ); ?>"><?php echo esc_html( $qv_download['product_name'] ); ?></a>
						<span class="download-list__file"><?php echo esc_html( $qv_download['download_name'] ); ?></span>
					</span>
					<a class="btn btn--primary btn--sm" href="<?php echo esc_url( $qv_download['download_url'] ); ?>"><?php qv_the_icon( 'download', array( 'size' => 16 ) ); ?> <?php esc_html_e( 'Download', 'quantivista' ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<div class="account-empty">
			<p><?php esc_html_e( 'Software you purchase will be available to download here once payment is confirmed.', 'quantivista' ); ?></p>
		</div>
	<?php endif; ?>
</section>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
