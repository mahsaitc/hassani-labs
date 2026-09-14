<?php
/**
 * Author bio box.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_author_id = (int) get_the_author_meta( 'ID' );
$qv_bio       = get_the_author_meta( 'description', $qv_author_id );
$qv_role      = get_user_meta( $qv_author_id, 'qv_job_title', true );
?>
<section class="author-bio" aria-label="<?php esc_attr_e( 'About the author', 'quantivista' ); ?>">
	<?php echo get_avatar( $qv_author_id, 88, '', '', array( 'class' => 'author-bio__avatar' ) ); ?>
	<div class="author-bio__body">
		<p class="eyebrow"><?php esc_html_e( 'Written by', 'quantivista' ); ?></p>
		<h2 class="author-bio__name">
			<a href="<?php echo esc_url( get_author_posts_url( $qv_author_id ) ); ?>"><?php the_author(); ?></a>
			<?php if ( $qv_role ) : ?>
				<span class="author-bio__role"><?php echo esc_html( $qv_role ); ?></span>
			<?php endif; ?>
		</h2>
		<?php if ( $qv_bio ) : ?>
			<p class="author-bio__text"><?php echo esc_html( $qv_bio ); ?></p>
		<?php endif; ?>
		<a class="link-arrow" href="<?php echo esc_url( get_author_posts_url( $qv_author_id ) ); ?>">
			<?php
			/* translators: %s: author name */
			printf( esc_html__( 'More articles by %s', 'quantivista' ), esc_html( get_the_author() ) );
			qv_the_icon( 'arrow-right', array( 'size' => 16 ) );
			?>
		</a>
	</div>
</section>
