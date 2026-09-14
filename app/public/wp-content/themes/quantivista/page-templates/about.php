<?php
/**
 * Template Name: About Us
 *
 * Story, mission/vision/values, stats, team (CPT), milestones (CPT), CTA.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$qv_id     = get_the_ID();
	$qv_image  = (int) qv_field( 'qv_story_image', $qv_id );
	$qv_values = qv_lines( qv_field( 'qv_values', $qv_id ) );
	$qv_team   = get_posts( array( 'post_type' => 'qv_team', 'posts_per_page' => 12, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
	$qv_steps  = get_posts( array( 'post_type' => 'qv_milestone', 'posts_per_page' => 12, 'orderby' => 'menu_order', 'order' => 'ASC' ) );

	qv_page_hero(
		array(
			'eyebrow' => __( 'About Quantivista', 'quantivista' ),
			'title'   => get_the_title(),
			'lead'    => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>

	<section class="section" aria-labelledby="story-title">
		<div class="container split">
			<div data-reveal>
				<p class="eyebrow"><?php esc_html_e( 'Our story', 'quantivista' ); ?></p>
				<h2 id="story-title"><?php echo esc_html( qv_field( 'qv_story_title', $qv_id ) ? qv_field( 'qv_story_title', $qv_id ) : __( 'From client projects to products used worldwide', 'quantivista' ) ); ?></h2>
				<div class="prose"><?php the_content(); ?></div>
			</div>
			<div class="split__media" data-reveal>
				<?php
				if ( $qv_image ) {
					echo wp_get_attachment_image( $qv_image, 'large', false, array( 'sizes' => '(min-width: 960px) 50vw, 100vw' ) );
				}
				?>
				<div class="floating-stat">
					<strong><?php echo esc_html( qv_mod( 'stat_2_value' ) ); ?>+</strong>
					<span><?php echo esc_html( qv_mod( 'stat_2_label' ) ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<section class="section section--muted" aria-labelledby="mission-title">
		<div class="container">
			<?php
			qv_section_head(
				array(
					'eyebrow' => __( 'What drives us', 'quantivista' ),
					'title'   => __( 'Mission, vision & values', 'quantivista' ),
					'id'      => 'mission-title',
				)
			);
			?>
			<div class="grid grid--2">
				<div class="mv-card" data-reveal>
					<span class="mv-card__icon"><?php qv_the_icon( 'target', array( 'size' => 24 ) ); ?></span>
					<h3><?php esc_html_e( 'Our mission', 'quantivista' ); ?></h3>
					<p><?php echo esc_html( qv_field( 'qv_mission', $qv_id ) ); ?></p>
				</div>
				<div class="mv-card" data-reveal>
					<span class="mv-card__icon"><?php qv_the_icon( 'eye', array( 'size' => 24 ) ); ?></span>
					<h3><?php esc_html_e( 'Our vision', 'quantivista' ); ?></h3>
					<p><?php echo esc_html( qv_field( 'qv_vision', $qv_id ) ); ?></p>
				</div>
			</div>

			<?php if ( $qv_values ) : ?>
				<div class="grid grid--4" style="margin-top: clamp(16px, 2.4vw, 28px);">
					<?php foreach ( $qv_values as $qv_value ) : ?>
						<?php $qv_parts = array_map( 'trim', explode( '|', $qv_value, 2 ) ); ?>
						<div class="mv-card" data-reveal>
							<h3 style="font-size:1.08rem;"><?php echo esc_html( $qv_parts[0] ); ?></h3>
							<p class="muted"><?php echo esc_html( $qv_parts[1] ?? '' ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/trust' ); ?>

	<?php if ( $qv_team ) : ?>
		<section class="section" aria-labelledby="team-title">
			<div class="container">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'The team', 'quantivista' ),
						'title'   => __( 'Meet the people behind Quantivista', 'quantivista' ),
						'lead'    => __( 'A senior, distributed team of engineers, designers and strategists across four time zones.', 'quantivista' ),
						'id'      => 'team-title',
					)
				);
				?>
				<ul class="grid grid--4" style="list-style:none;margin:0;padding:0;">
					<?php foreach ( $qv_team as $qv_member ) : ?>
						<?php $qv_linkedin = qv_field( 'qv_linkedin', $qv_member->ID ); ?>
						<li class="team-card" data-reveal>
							<div class="team-card__photo">
								<?php
								echo get_the_post_thumbnail(
									$qv_member,
									'qv-square',
									array(
										/* translators: %s: team member name */
										'alt' => sprintf( __( 'Portrait of %s', 'quantivista' ), get_the_title( $qv_member ) ),
									)
								);
								?>
								<?php if ( $qv_linkedin ) : ?>
									<a class="team-card__social" href="<?php echo esc_url( $qv_linkedin ); ?>" target="_blank" rel="noopener noreferrer">
										<?php qv_the_icon( 'linkedin', array( 'size' => 16 ) ); ?>
										<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: name */ __( '%s on LinkedIn', 'quantivista' ), get_the_title( $qv_member ) ) ); ?></span>
									</a>
								<?php endif; ?>
							</div>
							<h3 class="team-card__name"><?php echo esc_html( get_the_title( $qv_member ) ); ?></h3>
							<p class="team-card__role"><?php echo esc_html( qv_field( 'qv_role', $qv_member->ID ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $qv_steps ) : ?>
		<section class="section section--dark" aria-labelledby="timeline-title">
			<div class="container">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'Milestones', 'quantivista' ),
						'title'   => __( 'From a two-person studio to a global product company', 'quantivista' ),
						'id'      => 'timeline-title',
					)
				);
				?>
				<ol class="timeline">
					<?php foreach ( $qv_steps as $qv_step ) : ?>
						<li class="timeline__item" data-reveal>
							<span class="timeline__year"><?php echo esc_html( qv_field( 'qv_year', $qv_step->ID ) ); ?></span>
							<div class="timeline__card">
								<h3><?php echo esc_html( get_the_title( $qv_step ) ); ?></h3>
								<p><?php echo esc_html( wp_strip_all_tags( $qv_step->post_content ) ); ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/home/cta' );
endwhile;

get_footer();
