<?php
/**
 * Template Name: Products Catalogue
 *
 * Product categories grouped by platform and by field (inc/catalog.php).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	qv_page_hero(
		array(
			'eyebrow' => __( 'Products', 'quantivista' ),
			'title'   => get_the_title(),
			'lead'    => has_excerpt() ? get_the_excerpt() : '',
		)
	);

	foreach ( qv_catalog_groups() as $qv_i => $qv_group ) :
		?>
		<section class="section section--tight<?php echo $qv_i % 2 ? ' section--muted' : ''; ?>" id="<?php echo esc_attr( $qv_group['term']->slug ); ?>" aria-labelledby="group-<?php echo esc_attr( $qv_group['term']->slug ); ?>">
			<div class="container">
				<?php
				qv_section_head(
					array(
						'title' => esc_html( qv_cat_text( $qv_group['term'] ) ),
						'lead'  => esc_html( qv_cat_text( $qv_group['term'], 'description' ) ),
						'id'    => 'group-' . $qv_group['term']->slug,
					)
				);
				?>
				<div class="grid grid--3 cat-tiles">
					<?php
					foreach ( $qv_group['children'] as $qv_term ) {
						qv_cat_tile( $qv_term );
					}
					?>
				</div>
			</div>
		</section>
		<?php
	endforeach;
	?>
	<section class="section section--tight">
		<div class="container">
			<div class="empty-state catalog-cta">
				<?php qv_the_icon( 'message', array( 'size' => 28 ) ); ?>
				<h2 class="catalog-cta__title"><?php esc_html_e( 'Need software that is not listed here?', 'quantivista' ); ?></h2>
				<p><?php esc_html_e( 'Tell me what you need. I design and build custom software, apps and websites for your field.', 'quantivista' ); ?></p>
				<a class="btn btn--primary" href="<?php echo esc_url( qv_localize_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Order custom software', 'quantivista' ); ?></a>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
