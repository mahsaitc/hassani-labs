<?php
/**
 * 404 page.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="error-404">
	<div class="page-hero__glow" aria-hidden="true"></div>
	<div class="container container--narrow error-404__inner">
		<p class="error-404__code" aria-hidden="true">404</p>
		<h1 class="error-404__title"><?php esc_html_e( 'This page drifted out of orbit', 'quantivista' ); ?></h1>
		<p class="error-404__lead"><?php esc_html_e( 'The link may be broken or the page may have moved. Try a search or head back to safer ground.', 'quantivista' ); ?></p>
		<?php get_search_form(); ?>
		<div class="btn-row btn-row--center">
			<a class="btn btn--accent" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'quantivista' ); ?></a>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="btn btn--ghost" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse products', 'quantivista' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
