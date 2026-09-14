<?php
/**
 * Comments.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments__title">
			<?php
			$qv_count = get_comments_number();
			/* translators: %s: comment count */
			printf( esc_html( _n( '%s comment', '%s comments', $qv_count, 'quantivista' ) ), esc_html( number_format_i18n( $qv_count ) ) );
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation();
	endif;

	if ( ! comments_open() && get_comments_number() ) :
		?>
		<p class="comments__closed"><?php esc_html_e( 'Comments are closed.', 'quantivista' ); ?></p>
		<?php
	endif;

	comment_form(
		array(
			'title_reply'        => __( 'Join the discussion', 'quantivista' ),
			'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">',
			'title_reply_after'  => '</h2>',
			'class_submit'       => 'btn btn--primary',
			'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Your email address will not be published. Comments are moderated.', 'quantivista' ) . '</p>',
		)
	);
	?>
</div>
