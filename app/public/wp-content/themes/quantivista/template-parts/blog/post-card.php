<?php
/**
 * Blog post card.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_featured = ! empty( $args['featured'] );
$qv_heading  = $args['heading'] ?? 'h2';
?>
<article <?php post_class( 'post-card' . ( $qv_featured ? ' post-card--featured' : '' ) ); ?> data-reveal>
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( $qv_featured ? 'qv-wide' : 'qv-card', array( 'alt' => '' ) );
		} else {
			echo '<span class="media-placeholder">' . qv_icon( 'file-text', array( 'size' => 32 ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</a>
	<div class="post-card__body">
		<div class="post-card__top">
			<?php qv_primary_category(); ?>
			<?php qv_post_meta(); ?>
		</div>
		<<?php echo tag_escape( $qv_heading ); ?> class="post-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</<?php echo tag_escape( $qv_heading ); ?>>
		<p class="post-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<div class="post-card__author">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 32, '', '', array( 'class' => 'avatar' ) ); ?>
			<span><?php the_author(); ?></span>
		</div>
	</div>
</article>
