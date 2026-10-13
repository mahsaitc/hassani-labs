<?php
/**
 * Template Name: About Us
 *
 * The founder and the studio: story, links, mission/vision/values, skills
 * (qv_skills JSON), milestones (CPT), selected work, CTA.
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
	$qv_skills = json_decode( (string) get_post_meta( $qv_id, 'qv_skills', true ), true );
	$qv_resume = qv_field( 'qv_resume_url', $qv_id );
	$qv_work   = function_exists( 'qvc_projects' ) ? qvc_projects( 3, true ) : array();
	$qv_steps  = get_posts( array( 'post_type' => 'qv_milestone', 'posts_per_page' => 12, 'orderby' => 'menu_order', 'order' => 'ASC' ) );

	qv_page_hero(
		array(
			'eyebrow' => __( 'About Hassani Labs', 'quantivista' ),
			'title'   => get_the_title(),
			'lead'    => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>

	<?php
	// Split the story into an intro (beside the photo), themed sections shown as
	// cards (each <h3> starts one) and a closing statement (a paragraph that is
	// only bold text). Content without <h3> headings renders as one block.
	$qv_story    = apply_filters( 'the_content', get_the_content() );
	$qv_sections = array();
	$qv_closing  = '';
	if ( preg_match( '#<p>\s*<strong>(.*?)</strong>\s*</p>\s*$#su', $qv_story, $qv_m ) ) {
		$qv_closing = $qv_m[1];
		$qv_story   = substr( $qv_story, 0, -strlen( $qv_m[0] ) );
	}
	$qv_parts = preg_split( '#<h3[^>]*>#u', $qv_story );
	$qv_intro = array_shift( $qv_parts );
	foreach ( $qv_parts as $qv_part ) {
		$qv_bits       = explode( '</h3>', $qv_part, 2 );
		$qv_sections[] = array( 'title' => wp_strip_all_tags( $qv_bits[0] ), 'body' => $qv_bits[1] ?? '' );
	}
	$qv_icons = array( 'layers', 'shield', 'target', 'rocket', 'cpu', 'award' );
	?>
	<section class="section" aria-labelledby="story-title">
		<div class="container split story-split">
			<div data-reveal>
				<p class="eyebrow"><?php echo esc_html( qv_mod( 'founder_role' ) ); ?></p>
				<h2 id="story-title"><?php echo esc_html( qv_field( 'qv_story_title', $qv_id ) ? qv_field( 'qv_story_title', $qv_id ) : qv_mod( 'founder_name' ) ); ?></h2>
				<div class="prose"><?php echo $qv_intro; // phpcs:ignore WordPress.Security.EscapeOutput -- filtered post content. ?></div>
				<div class="btn-row founder-links">
					<?php if ( qv_mod( 'social_github' ) ) : ?>
						<a class="btn btn--primary" href="<?php echo esc_url( qv_mod( 'social_github' ) ); ?>" target="_blank" rel="noopener"><?php qv_the_icon( 'github', array( 'size' => 16 ) ); ?> GitHub</a>
					<?php endif; ?>
					<?php if ( qv_mod( 'social_linkedin' ) ) : ?>
						<a class="btn btn--outline" href="<?php echo esc_url( qv_mod( 'social_linkedin' ) ); ?>" target="_blank" rel="noopener"><?php qv_the_icon( 'linkedin', array( 'size' => 16 ) ); ?> LinkedIn</a>
					<?php endif; ?>
					<?php if ( $qv_resume ) : ?>
						<a class="btn btn--outline" href="<?php echo esc_url( $qv_resume ); ?>"><?php qv_the_icon( 'download', array( 'size' => 16 ) ); ?> <?php esc_html_e( 'Résumé (PDF)', 'quantivista' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="split__media founder-photo" data-reveal>
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'large', array( 'sizes' => '(min-width: 960px) 50vw, 100vw' ) );
				} elseif ( $qv_image ) {
					echo wp_get_attachment_image( $qv_image, 'large', false, array( 'sizes' => '(min-width: 960px) 50vw, 100vw' ) );
				} else {
					printf( '<img src="%s" alt="%s" width="900" height="900">', esc_url( QV_URI . '/assets/img/founder-portrait.webp?v=' . qv_asset_version( 'assets/img/founder-portrait.webp' ) ), esc_attr( qv_mod( 'founder_name' ) ) );
				}
				?>
				<div class="floating-stat">
					<strong><?php echo esc_html( qv_mod( 'stat_1_value' ) ); ?></strong>
					<span><?php echo esc_html( qv_mod( 'stat_1_label' ) ); ?></span>
				</div>
			</div>
		</div>

		<?php if ( $qv_sections ) : ?>
			<div class="container story-cards grid grid--<?php echo count( $qv_sections ) >= 3 ? 3 : count( $qv_sections ); ?>">
				<?php foreach ( $qv_sections as $qv_i => $qv_section ) : ?>
					<article class="story-card" data-reveal>
						<span class="story-card__icon"><?php qv_the_icon( $qv_icons[ $qv_i % count( $qv_icons ) ], array( 'size' => 22 ) ); ?></span>
						<h3><?php echo esc_html( $qv_section['title'] ); ?></h3>
						<div class="story-card__body"><?php echo $qv_section['body']; // phpcs:ignore WordPress.Security.EscapeOutput -- filtered post content. ?></div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $qv_closing ) : ?>
			<div class="container">
				<blockquote class="story-quote" data-reveal>
					<?php qv_the_icon( 'quote', array( 'size' => 28 ) ); ?>
					<p><?php echo wp_kses_post( $qv_closing ); ?></p>
					<cite><?php echo esc_html( qv_mod( 'founder_name' ) ); ?></cite>
				</blockquote>
			</div>
		<?php endif; ?>
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

	<?php if ( $qv_skills ) : ?>
		<section class="section" aria-labelledby="skills-title">
			<div class="container">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'Skills', 'quantivista' ),
						'title'   => __( 'What I work with', 'quantivista' ),
						'id'      => 'skills-title',
					)
				);
				?>
				<div class="grid grid--2 skills-grid">
					<?php foreach ( $qv_skills as $qv_group ) : ?>
						<div class="skill-group" data-reveal>
							<h3><?php echo esc_html( $qv_group['title'] ?? '' ); ?></h3>
							<p class="muted"><?php echo esc_html( $qv_group['blurb'] ?? '' ); ?></p>
							<ul class="skill-list">
								<?php foreach ( (array) ( $qv_group['skills'] ?? array() ) as $qv_skill ) : ?>
									<li>
										<strong><?php echo esc_html( $qv_skill['name'] ?? '' ); ?></strong>
										<span><?php echo esc_html( $qv_skill['note'] ?? '' ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $qv_steps ) : ?>
		<section class="section section--dark" aria-labelledby="timeline-title">
			<div class="container">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'Path so far', 'quantivista' ),
						'title'   => __( 'From medicine to software', 'quantivista' ),
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
								<?php if ( trim( wp_strip_all_tags( $qv_step->post_content ) ) ) : ?>
									<p><?php echo esc_html( wp_strip_all_tags( $qv_step->post_content ) ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

<?php if ( $qv_work ) : ?>
		<section class="section section--muted" aria-labelledby="about-work-title">
			<div class="container">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'Selected work', 'quantivista' ),
						'title'   => __( 'Case studies', 'quantivista' ),
						'id'      => 'about-work-title',
					)
				);
				?>
				<div class="grid grid--3 work-grid">
					<?php foreach ( $qv_work as $qv_project ) : ?>
						<?php get_template_part( 'template-parts/work/card', null, array( 'project' => $qv_project ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/home/cta',
		null,
		array(
			'eyebrow' => __( 'Let\'s work together', 'quantivista' ),
			'title'   => __( 'Have an idea that should become a product?', 'quantivista' ),
			'text'    => __( 'Whether you need a website, custom software or a better way to run a work process, we can start by understanding the problem and what you need.', 'quantivista' ),
			'label'   => __( 'Let\'s talk about your project', 'quantivista' ),
			'url'     => qv_localize_url( '/contact/' ),
		)
	);
endwhile;

get_footer();
