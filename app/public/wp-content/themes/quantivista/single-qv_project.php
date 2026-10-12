<?php
/**
 * Portfolio: one project case study.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$qv_id      = get_the_ID();
	$qv_repo    = get_post_meta( $qv_id, 'qv_repo', true );
	$qv_demo    = get_post_meta( $qv_id, 'qv_demo', true );
	$qv_stack   = qv_lines( get_post_meta( $qv_id, 'qv_stack', true ) );
	$qv_metrics = qv_lines( get_post_meta( $qv_id, 'qv_metrics', true ) );
	$qv_terms   = get_the_terms( $qv_id, 'qv_tech' );
	$qv_poster  = qv_project_poster( $qv_id );
	$qv_archive = get_post_type_archive_link( 'qv_project' );

	qv_page_hero(
		array(
			'eyebrow' => __( 'Case study', 'quantivista' ) . ' · ' . get_post_meta( $qv_id, 'qv_year', true ),
			'title'   => get_the_title(),
			'lead'    => get_post_meta( $qv_id, 'qv_tagline', true ),
		)
	);
	?>
	<section class="section section--tight">
		<div class="container work-layout">
			<div class="work-layout__main">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="work-cover"><?php the_post_thumbnail( 'qv-wide' ); ?></figure>
				<?php elseif ( $qv_poster ) : ?>
					<figure class="work-cover"><img src="<?php echo esc_url( $qv_poster ); ?>" alt="<?php echo esc_attr( get_post_meta( $qv_id, 'qv_poster_alt', true ) ); ?>" width="1600" height="1000"></figure>
				<?php endif; ?>
				<?php if ( has_excerpt() ) : ?>
					<p class="work-summary"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div class="prose"><?php the_content(); ?></div>
			</div>

			<aside class="work-layout__rail">
				<div class="work-facts">
					<?php if ( $qv_metrics ) : ?>
						<ul class="work-metrics">
							<?php foreach ( $qv_metrics as $qv_line ) : ?>
								<?php $qv_m = array_map( 'trim', explode( '|', $qv_line, 2 ) ); ?>
								<li><strong><?php echo esc_html( $qv_m[1] ?? '' ); ?></strong><span><?php echo esc_html( $qv_m[0] ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $qv_stack ) : ?>
						<h2 class="work-facts__title"><?php esc_html_e( 'Stack', 'quantivista' ); ?></h2>
						<ul class="check-list">
							<?php foreach ( $qv_stack as $qv_line ) : ?>
								<li><?php qv_the_icon( 'check', array( 'size' => 16 ) ); ?> <span><?php echo esc_html( $qv_line ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $qv_terms && ! is_wp_error( $qv_terms ) ) : ?>
						<ul class="work-card__tags">
							<?php foreach ( $qv_terms as $qv_term ) : ?>
								<li><a class="chip" href="<?php echo esc_url( get_term_link( $qv_term ) ); ?>"><?php echo esc_html( $qv_term->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<div class="btn-row">
						<?php if ( $qv_repo ) : ?>
							<a class="btn btn--primary" href="<?php echo esc_url( $qv_repo ); ?>" target="_blank" rel="noopener"><?php qv_the_icon( 'github', array( 'size' => 16 ) ); ?> <?php esc_html_e( 'Source code', 'quantivista' ); ?></a>
						<?php endif; ?>
						<?php if ( $qv_demo ) : ?>
							<a class="btn btn--outline" href="<?php echo esc_url( $qv_demo ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Live demo', 'quantivista' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( $qv_archive ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( $qv_archive ); ?>"><?php esc_html_e( 'All work', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
				<?php endif; ?>
			</aside>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/home/cta' );
endwhile;

get_footer();
