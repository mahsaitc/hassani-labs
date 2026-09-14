<?php
/**
 * Template Name: Policies Index
 *
 * Lists child policy pages as cards.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$qv_children = get_pages(
		array(
			'parent'      => get_the_ID(),
			'sort_column' => 'menu_order,post_title',
		)
	);
	$qv_icons    = array( 'file-text', 'lock', 'refresh', 'key', 'shield' );

	qv_page_hero(
		array(
			'eyebrow' => __( 'Legal', 'quantivista' ),
			'title'   => get_the_title(),
			'lead'    => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>
	<section class="section section--tight">
		<div class="container">
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="prose container--narrow" style="margin: 0 0 32px;"><?php the_content(); ?></div>
			<?php endif; ?>
			<div class="grid grid--2 policy-cards">
				<?php foreach ( $qv_children as $qv_i => $qv_child ) : ?>
					<a class="info-card" href="<?php echo esc_url( get_permalink( $qv_child ) ); ?>" data-reveal>
						<span class="info-card__icon"><?php qv_the_icon( $qv_icons[ $qv_i % count( $qv_icons ) ] ); ?></span>
						<span>
							<h2><?php echo esc_html( get_the_title( $qv_child ) ); ?></h2>
							<p><?php echo esc_html( has_excerpt( $qv_child ) ? get_the_excerpt( $qv_child ) : wp_trim_words( wp_strip_all_tags( $qv_child->post_content ), 22 ) ); ?></p>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
