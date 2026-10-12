<?php
/**
 * Home: the founder and selected work.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qvc_projects' ) ) {
	return;
}

$qv_projects = qvc_projects( 3, true );
$qv_about    = get_page_by_path( 'about' );
$qv_archive  = get_post_type_archive_link( 'qv_project' );
?>
<section class="section" aria-labelledby="founder-title">
	<div class="container founder-strip" data-reveal>
		<span class="founder-strip__avatar" aria-hidden="true"><?php echo esc_html( qv_mod( 'founder_initials' ) ); ?></span>
		<div>
			<p class="eyebrow"><?php esc_html_e( 'Founder', 'quantivista' ); ?></p>
			<h2 id="founder-title"><?php echo esc_html( qv_mod( 'founder_name' ) ); ?></h2>
			<p class="founder-strip__role"><?php echo esc_html( qv_mod( 'founder_role' ) ); ?></p>
			<p><?php echo esc_html( qv_mod( 'founder_intro' ) ); ?></p>
			<?php if ( $qv_about ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( get_permalink( $qv_about ) ); ?>"><?php esc_html_e( 'More about me', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php if ( $qv_projects ) : ?>
	<section class="section section--muted" aria-labelledby="work-title">
		<div class="container">
			<div class="section-head-row">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'Selected work', 'quantivista' ),
						'title'   => __( 'Case studies', 'quantivista' ),
						'id'      => 'work-title',
						'align'   => 'left',
					)
				);
				?>
				<?php if ( $qv_archive ) : ?>
					<a class="btn btn--outline btn--sm" href="<?php echo esc_url( $qv_archive ); ?>"><?php esc_html_e( 'All work', 'quantivista' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="grid grid--3 work-grid">
				<?php foreach ( $qv_projects as $qv_project ) : ?>
					<?php get_template_part( 'template-parts/work/card', null, array( 'project' => $qv_project ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
