<?php
/**
 * Latest blog posts.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_posts = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $qv_posts->have_posts() ) {
	return;
}

$qv_blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
?>
<section class="section section--muted" aria-labelledby="blog-title">
	<div class="container">
		<div class="section-head-row">
			<?php
			qv_section_head(
				array(
					'eyebrow' => __( 'From the blog', 'quantivista' ),
					'title'   => __( 'Insights from our engineering & design teams', 'quantivista' ),
					'id'      => 'blog-title',
					'align'   => 'left',
				)
			);
			?>
			<a class="btn btn--outline" href="<?php echo esc_url( $qv_blog_url ); ?>"><?php esc_html_e( 'View all articles', 'quantivista' ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 16 ) ); ?></a>
		</div>
		<div class="grid grid--3 post-grid">
			<?php
			while ( $qv_posts->have_posts() ) {
				$qv_posts->the_post();
				get_template_part( 'template-parts/blog/post-card', null, array( 'heading' => 'h3' ) );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
