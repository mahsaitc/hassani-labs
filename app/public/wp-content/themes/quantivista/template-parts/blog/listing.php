<?php
/**
 * Shared layout for the blog index and archives.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_is_index = ! empty( $args['is_index'] );

if ( $qv_is_index ) {
	$qv_blog_page = (int) get_option( 'page_for_posts' );
	$qv_title     = $qv_blog_page ? get_the_title( $qv_blog_page ) : __( 'Blog', 'quantivista' );
	$qv_lead      = $qv_blog_page && has_excerpt( $qv_blog_page ) ? get_the_excerpt( $qv_blog_page ) : __( 'Notes on software design, testing and building software for clinics.', 'quantivista' );
	$qv_eyebrow   = __( 'Insights', 'quantivista' );
} else {
	$qv_title   = get_the_archive_title();
	$qv_lead    = get_the_archive_description();
	$qv_eyebrow = is_category() ? __( 'Category', 'quantivista' ) : ( is_tag() ? __( 'Tag', 'quantivista' ) : ( is_author() ? __( 'Author', 'quantivista' ) : __( 'Archive', 'quantivista' ) ) );
	if ( is_category() || is_tag() ) {
		$qv_title = single_term_title( '', false );
	} elseif ( is_author() ) {
		$qv_title = get_the_author();
	}
}

qv_page_hero(
	array(
		'eyebrow' => $qv_eyebrow,
		'title'   => $qv_title,
		'lead'    => wp_strip_all_tags( (string) $qv_lead ),
	)
);

$qv_cats = get_categories(
	array(
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	)
);
?>
<section class="section section--tight">
	<div class="container">
		<?php if ( $qv_cats ) : ?>
			<nav class="chip-nav" aria-label="<?php esc_attr_e( 'Blog categories', 'quantivista' ); ?>">
				<?php $qv_blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ); ?>
				<a class="chip<?php echo $qv_is_index ? ' is-active' : ''; ?>" href="<?php echo esc_url( $qv_blog_url ); ?>"<?php echo $qv_is_index ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All', 'quantivista' ); ?></a>
				<?php foreach ( $qv_cats as $qv_cat ) : ?>
					<?php $qv_active = is_category( $qv_cat->term_id ); ?>
					<a class="chip<?php echo $qv_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $qv_cat ) ); ?>"<?php echo $qv_active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $qv_cat->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<div class="blog-layout<?php echo is_active_sidebar( 'blog-sidebar' ) ? ' blog-layout--sidebar' : ''; ?>">
			<div class="blog-layout__main">
				<?php if ( have_posts() ) : ?>
					<div class="grid grid--3 post-grid">
						<?php
						$qv_i = 0;
						while ( have_posts() ) :
							the_post();
							$qv_featured = $qv_is_index && 0 === $qv_i && ! is_paged();
							get_template_part( 'template-parts/blog/post-card', null, array( 'featured' => $qv_featured ) );
							++$qv_i;
						endwhile;
						?>
					</div>
					<?php qv_pagination(); ?>
				<?php else : ?>
					<div class="empty-state">
						<?php qv_the_icon( 'file-text', array( 'size' => 40 ) ); ?>
						<h2><?php esc_html_e( 'Nothing published here yet', 'quantivista' ); ?></h2>
						<p><?php esc_html_e( 'Check back soon, or search for something specific.', 'quantivista' ); ?></p>
						<?php get_search_form(); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
				<aside class="blog-layout__sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'quantivista' ); ?>">
					<?php dynamic_sidebar( 'blog-sidebar' ); ?>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</section>
