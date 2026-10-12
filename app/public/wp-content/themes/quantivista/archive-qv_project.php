<?php
/**
 * Portfolio: all projects (/work/) and projects by technology.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

qv_page_hero(
	array(
		'eyebrow' => __( 'Work', 'quantivista' ),
		'title'   => is_tax( 'qv_tech' ) ? single_term_title( '', false ) : __( 'Selected work', 'quantivista' ),
		'lead'    => __( 'Case studies from production-minded projects: what was built, how it is structured, and what it proves.', 'quantivista' ),
	)
);
?>
<section class="section section--tight">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3 work-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/work/card', null, array( 'project' => get_post() ) );
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'No projects yet.', 'quantivista' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/home/cta' );
get_footer();
