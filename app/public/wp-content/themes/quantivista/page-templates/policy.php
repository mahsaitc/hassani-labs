<?php
/**
 * Template Name: Policy Page
 *
 * Legal page with sibling-policy navigation and "last updated" meta.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$qv_parent   = wp_get_post_parent_id( get_the_ID() );
	$qv_policies = get_pages(
		array(
			'parent'      => $qv_parent ? $qv_parent : get_the_ID(),
			'sort_column' => 'menu_order,post_title',
		)
	);

	qv_page_hero(
		array(
			'eyebrow' => __( 'Policies', 'quantivista' ),
			'title'   => get_the_title(),
			'lead'    => has_excerpt() ? get_the_excerpt() : '',
			'class'   => 'page-hero--compact',
		)
	);
	?>
	<section class="section section--tight">
		<div class="container policy-layout">
			<?php if ( $qv_policies ) : ?>
				<nav class="policy-nav" aria-label="<?php esc_attr_e( 'Policies', 'quantivista' ); ?>">
					<p class="policy-nav__title"><?php esc_html_e( 'Policies', 'quantivista' ); ?></p>
					<ul>
						<?php foreach ( $qv_policies as $qv_policy ) : ?>
							<?php $qv_current = get_the_ID() === $qv_policy->ID; ?>
							<li>
								<a href="<?php echo esc_url( get_permalink( $qv_policy ) ); ?>"<?php echo $qv_current ? ' aria-current="page"' : ''; ?>>
									<?php qv_the_icon( 'file-text', array( 'size' => 16 ) ); ?>
									<?php echo esc_html( get_the_title( $qv_policy ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<article class="policy-content">
				<div class="policy-meta">
					<span><?php qv_the_icon( 'calendar', array( 'size' => 16 ) ); ?> <?php esc_html_e( 'Last updated:', 'quantivista' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date() ); ?></time></span>
					<span><?php qv_the_icon( 'clock', array( 'size' => 16 ) ); ?> <?php echo esc_html( sprintf( /* translators: %d: minutes */ _n( '%d min read', '%d min read', qv_reading_time(), 'quantivista' ), qv_reading_time() ) ); ?></span>
				</div>
				<div class="prose entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		</div>
	</section>
	<?php
endwhile;

get_footer();
