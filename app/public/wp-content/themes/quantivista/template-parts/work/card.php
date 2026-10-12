<?php
/**
 * Project card (portfolio).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_project = $args['project'] ?? get_post();
$qv_id      = $qv_project->ID;
$qv_terms   = get_the_terms( $qv_id, 'qv_tech' );
$qv_poster  = qv_project_poster( $qv_id );
?>
<article class="work-card" data-reveal>
	<a class="work-card__media" href="<?php echo esc_url( get_permalink( $qv_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail( $qv_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $qv_id, 'qv-card', array( 'alt' => '' ) ); ?>
		<?php elseif ( $qv_poster ) : ?>
			<img src="<?php echo esc_url( $qv_poster ); ?>" alt="" loading="lazy" width="800" height="500">
		<?php endif; ?>
	</a>
	<div class="work-card__body">
		<p class="work-card__meta"><?php echo esc_html( get_post_meta( $qv_id, 'qv_year', true ) ); ?></p>
		<h3 class="work-card__title"><a href="<?php echo esc_url( get_permalink( $qv_id ) ); ?>"><?php echo esc_html( get_the_title( $qv_id ) ); ?></a></h3>
		<p class="work-card__tagline"><?php echo esc_html( get_post_meta( $qv_id, 'qv_tagline', true ) ); ?></p>
		<?php if ( $qv_terms && ! is_wp_error( $qv_terms ) ) : ?>
			<ul class="work-card__tags">
				<?php foreach ( array_slice( $qv_terms, 0, 5 ) as $qv_term ) : ?>
					<li class="chip"><?php echo esc_html( $qv_term->name ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</article>
