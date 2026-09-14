<?php
/**
 * License keys for downloadable products.
 *
 * A key per purchased unit is generated when an order reaches Processing or
 * Completed (i.e. once payment is confirmed — the same moment WooCommerce
 * grants download access). Keys are stored as order item meta, so they show
 * in the admin order screen, customer emails, order details, and the
 * My Account → Licenses page.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

function qvc_generate_license_key() {
	$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No 0/O/1/I ambiguity.
	$groups   = array();
	for ( $g = 0; $g < 4; $g++ ) {
		$chunk = '';
		for ( $i = 0; $i < 5; $i++ ) {
			$chunk .= $alphabet[ random_int( 0, strlen( $alphabet ) - 1 ) ];
		}
		$groups[] = $chunk;
	}
	return 'QV-' . implode( '-', $groups );
}

add_action( 'woocommerce_order_status_processing', 'qvc_assign_license_keys' );
add_action( 'woocommerce_order_status_completed', 'qvc_assign_license_keys' );

function qvc_assign_license_keys( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$generated = 0;
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( ! $product || ! $product->is_downloadable() || $item->get_meta( '_qv_license_key' ) ) {
			continue;
		}
		$keys = array();
		for ( $i = 0; $i < max( 1, (int) $item->get_quantity() ); $i++ ) {
			$keys[] = qvc_generate_license_key();
		}
		$item->update_meta_data( '_qv_license_key', implode( ', ', $keys ) );
		$item->save();
		$generated += count( $keys );
	}

	if ( $generated ) {
		/* translators: %d: number of keys */
		$order->add_order_note( sprintf( _n( '%d license key generated.', '%d license keys generated.', $generated, 'quantivista' ), $generated ) );
	}
}

/**
 * Show the (hidden, underscore-prefixed) key wherever item meta is displayed.
 */
add_filter(
	'woocommerce_order_item_get_formatted_meta_data',
	function ( $formatted, $item ) {
		$key = $item->get_meta( '_qv_license_key' );
		if ( $key ) {
			$formatted['qv_license_key'] = (object) array(
				'key'           => '_qv_license_key',
				'value'         => $key,
				'display_key'   => __( 'License key', 'quantivista' ),
				'display_value' => '<code class="license-key">' . esc_html( $key ) . '</code>',
			);
		}
		return $formatted;
	},
	10,
	2
);

/**
 * All license keys owned by a customer.
 *
 * @return array<int, array{product:string, product_id:int, key:string, order:WC_Order}>
 */
function qvc_get_customer_licenses( $customer_id ) {
	$rows   = array();
	$orders = wc_get_orders(
		array(
			'customer_id' => (int) $customer_id,
			'status'      => array( 'processing', 'completed' ),
			'limit'       => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);
	foreach ( $orders as $order ) {
		foreach ( $order->get_items() as $item ) {
			$keys = (string) $item->get_meta( '_qv_license_key' );
			if ( '' === $keys ) {
				continue;
			}
			foreach ( array_filter( array_map( 'trim', explode( ',', $keys ) ) ) as $key ) {
				$rows[] = array(
					'product'    => $item->get_name(),
					'product_id' => $item->get_product_id(),
					'key'        => $key,
					'order'      => $order,
				);
			}
		}
	}
	return $rows;
}

/**
 * My Account → Licenses endpoint.
 */
add_action( 'init', fn() => add_rewrite_endpoint( 'licenses', EP_ROOT | EP_PAGES ) );

add_filter(
	'woocommerce_get_query_vars',
	function ( $vars ) {
		$vars['licenses'] = 'licenses';
		return $vars;
	}
);

add_filter( 'woocommerce_endpoint_licenses_title', fn() => __( 'Licenses', 'quantivista' ) );

add_filter(
	'woocommerce_account_menu_items',
	fn( $items ) => qvc_array_insert_after( $items, 'downloads', array( 'licenses' => __( 'Licenses', 'quantivista' ) ) ),
	20
);

add_action( 'woocommerce_account_licenses_endpoint', 'qvc_render_licenses_endpoint' );
function qvc_render_licenses_endpoint() {
	$licenses = qvc_get_customer_licenses( get_current_user_id() );

	if ( ! $licenses ) {
		wc_print_notice(
			sprintf(
				/* translators: %s: shop URL */
				__( 'You don\'t have any license keys yet. Keys appear here as soon as a software order is paid. <a href="%s">Browse products</a>', 'quantivista' ),
				esc_url( wc_get_page_permalink( 'shop' ) )
			),
			'notice'
		);
		return;
	}
	?>
	<p><?php esc_html_e( 'Use these keys to activate your software. Each key is valid for the license tier you purchased.', 'quantivista' ); ?></p>
	<table class="shop_table shop_table_responsive account-licenses">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Product', 'quantivista' ); ?></th>
				<th scope="col"><?php esc_html_e( 'License key', 'quantivista' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Order', 'quantivista' ); ?></th>
				<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'quantivista' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $licenses as $row ) : ?>
				<tr>
					<td data-title="<?php esc_attr_e( 'Product', 'quantivista' ); ?>">
						<a href="<?php echo esc_url( get_permalink( $row['product_id'] ) ); ?>"><?php echo esc_html( $row['product'] ); ?></a>
					</td>
					<td data-title="<?php esc_attr_e( 'License key', 'quantivista' ); ?>"><code class="license-key"><?php echo esc_html( $row['key'] ); ?></code></td>
					<td data-title="<?php esc_attr_e( 'Order', 'quantivista' ); ?>">
						<a href="<?php echo esc_url( $row['order']->get_view_order_url() ); ?>">#<?php echo esc_html( $row['order']->get_order_number() ); ?></a>
						<small class="muted"><?php echo esc_html( wc_format_datetime( $row['order']->get_date_created() ) ); ?></small>
					</td>
					<td>
						<button type="button" class="button btn btn--outline btn--sm" data-copy-text="<?php echo esc_attr( $row['key'] ); ?>" data-copy-message="<?php esc_attr_e( 'License key copied', 'quantivista' ); ?>">
							<?php esc_html_e( 'Copy', 'quantivista' ); ?>
						</button>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
