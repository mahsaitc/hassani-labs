<?php
/**
 * Search form.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_search_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $qv_search_id ); ?>"><?php esc_html_e( 'Search products and articles', 'quantivista' ); ?></label>
	<span class="search-form__icon" aria-hidden="true"><?php qv_the_icon( 'search' ); ?></span>
	<input type="search" id="<?php echo esc_attr( $qv_search_id ); ?>" class="search-form__input" placeholder="<?php esc_attr_e( 'Search products, articles…', 'quantivista' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button type="submit" class="btn btn--primary btn--sm"><?php esc_html_e( 'Search', 'quantivista' ); ?></button>
</form>
