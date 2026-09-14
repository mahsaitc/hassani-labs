<?php
/**
 * Single blog post.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>
		<header class="post-hero">
			<div class="page-hero__glow" aria-hidden="true"></div>
			<div class="container container--narrow post-hero__inner">
				<?php qv_breadcrumbs(); ?>
				<?php qv_primary_category(); ?>
				<h1 class="post-hero__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="post-hero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div class="post-hero__meta">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 44, '', '', array( 'class' => 'avatar' ) ); ?>
					<div>
						<a class="post-hero__author" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
						<?php qv_post_meta(); ?>
					</div>
				</div>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="post-cover container container--wide">
				<?php the_post_thumbnail( 'qv-wide', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				<?php if ( get_the_post_thumbnail_caption() ) : ?>
					<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

		<div class="container post-layout">
			<aside class="post-layout__rail" aria-label="<?php esc_attr_e( 'Share this article', 'quantivista' ); ?>">
				<?php qv_share_links(); ?>
			</aside>

			<div class="post-layout__content">
				<div class="prose entry-content">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'quantivista' ),
							'after'  => '</nav>',
						)
					);
					?>
				</div>

				<footer class="post-footer">
					<?php
					$qv_tags = get_the_tags();
					if ( $qv_tags ) :
						?>
						<ul class="tag-list" aria-label="<?php esc_attr_e( 'Tags', 'quantivista' ); ?>">
							<?php foreach ( $qv_tags as $qv_tag ) : ?>
								<li><a class="chip" href="<?php echo esc_url( get_tag_link( $qv_tag ) ); ?>">#<?php echo esc_html( $qv_tag->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<div class="post-footer__share"><?php qv_share_links(); ?></div>

					<?php get_template_part( 'template-parts/blog/author-bio' ); ?>

					<?php
					the_post_navigation(
						array(
							'class'     => 'post-nav',
							'prev_text' => '<span class="post-nav__label">' . esc_html__( 'Previous article', 'quantivista' ) . '</span><span class="post-nav__title">%title</span>',
							'next_text' => '<span class="post-nav__label">' . esc_html__( 'Next article', 'quantivista' ) . '</span><span class="post-nav__title">%title</span>',
						)
					);
					?>
				</footer>
			</div>
		</div>
	</article>

	<?php
	$qv_related = qv_related_posts_query( get_the_ID() );
	if ( $qv_related->have_posts() ) :
		?>
		<section class="section section--muted" aria-labelledby="related-title">
			<div class="container">
				<?php
				qv_section_head(
					array(
						'eyebrow' => __( 'Keep reading', 'quantivista' ),
						'title'   => __( 'Related articles', 'quantivista' ),
						'id'      => 'related-title',
						'align'   => 'left',
					)
				);
				?>
				<div class="grid grid--3 post-grid">
					<?php
					while ( $qv_related->have_posts() ) :
						$qv_related->the_post();
						get_template_part( 'template-parts/blog/post-card', null, array( 'heading' => 'h3' ) );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( comments_open() || get_comments_number() ) : ?>
		<section class="section" id="comments-section">
			<div class="container container--narrow">
				<?php comments_template(); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
