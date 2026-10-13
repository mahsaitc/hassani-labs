<?php
/**
 * Home hero slider. Slides come from Site Content → Hero Slides; a built-in
 * set is used until slides are created.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_shop    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$qv_contact = qv_mod_url( 'cta_primary_url' );
$qv_slides  = array();

foreach ( get_posts( array( 'post_type' => 'qv_slide', 'posts_per_page' => 5, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $qv_post ) {
	$qv_slides[] = array(
		'title'      => get_the_title( $qv_post ),
		'eyebrow'    => qv_field( 'qv_slide_eyebrow', $qv_post->ID ),
		'lead'       => qv_field( 'qv_slide_subheadline', $qv_post->ID ),
		'video'      => qv_field( 'qv_slide_video', $qv_post->ID ),
		'image_id'   => get_post_thumbnail_id( $qv_post ),
		'cta_label'  => qv_field( 'qv_slide_cta_label', $qv_post->ID ),
		'cta_url'    => qv_localize_url( qv_field( 'qv_slide_cta_url', $qv_post->ID ) ),
		'cta2_label' => qv_field( 'qv_slide_cta2_label', $qv_post->ID ),
		'cta2_url'   => qv_localize_url( qv_field( 'qv_slide_cta2_url', $qv_post->ID ) ),
	);
}

if ( ! $qv_slides ) {
	$qv_slides = array(
		array(
			'title'      => 'Software that moves your business forward',
			'eyebrow'    => 'Web software · Mobile apps · Design',
			'lead'       => 'Production-ready products and custom websites, engineered by one senior team.',
			'video'      => QV_URI . '/assets/video/hero-loop.mp4',
			'image_id'   => 0,
			'cta_label'  => 'Explore Products',
			'cta_url'    => $qv_shop,
			'cta2_label' => 'Get a Quote',
			'cta2_url'   => $qv_contact,
		),
	);
}

$qv_count = count( $qv_slides );

/**
 * Resolve relative URLs ("/contact/") stored in slide fields.
 */
$qv_url = static fn( $url ) => ( $url && str_starts_with( $url, '/' ) ) ? home_url( $url ) : $url;
?>
<section class="hero" data-slider data-interval="7000" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Highlights', 'quantivista' ); ?>">
	<div class="hero__slides">
		<?php foreach ( $qv_slides as $qv_i => $qv_slide ) : ?>
			<div class="hero__slide<?php echo 0 === $qv_i ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number 2: total */ __( '%1$d of %2$d', 'quantivista' ), $qv_i + 1, $qv_count ) ); ?>">
				<div class="hero__media">
					<?php if ( $qv_slide['video'] ) : ?>
						<video autoplay muted loop playsinline preload="<?php echo 0 === $qv_i ? 'auto' : 'none'; ?>" poster="<?php echo esc_url( $qv_slide['image_id'] ? wp_get_attachment_image_url( $qv_slide['image_id'], 'qv-wide' ) : QV_URI . '/assets/img/hero-poster.jpg' ); ?>" aria-hidden="true">
							<source src="<?php echo esc_url( $qv_url( $qv_slide['video'] ) ); ?>" type="video/mp4">
						</video>
					<?php elseif ( $qv_slide['image_id'] ) : ?>
						<?php
						echo wp_get_attachment_image(
							$qv_slide['image_id'],
							'qv-wide',
							false,
							array(
								'alt'           => '',
								'sizes'         => '100vw',
								'loading'       => 0 === $qv_i ? 'eager' : 'lazy',
								'fetchpriority' => 0 === $qv_i ? 'high' : 'low',
							)
						);
						?>
					<?php endif; ?>
				</div>

				<div class="container hero__content">
					<?php if ( $qv_slide['eyebrow'] ) : ?>
						<p class="hero__badge"><strong><?php esc_html_e( 'New', 'quantivista' ); ?></strong> <?php echo esc_html( $qv_slide['eyebrow'] ); ?></p>
					<?php endif; ?>

					<?php $qv_tag = 0 === $qv_i ? 'h1' : 'h2'; ?>
					<<?php echo esc_attr( $qv_tag ); ?> class="hero__title"><?php echo esc_html( $qv_slide['title'] ); ?></<?php echo esc_attr( $qv_tag ); ?>>

					<?php if ( $qv_slide['lead'] ) : ?>
						<p class="hero__lead"><?php echo esc_html( $qv_slide['lead'] ); ?></p>
					<?php endif; ?>

					<div class="btn-row">
						<?php if ( $qv_slide['cta_label'] ) : ?>
							<a class="btn btn--accent btn--lg" href="<?php echo esc_url( $qv_url( $qv_slide['cta_url'] ) ); ?>"><?php echo esc_html( $qv_slide['cta_label'] ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 18 ) ); ?></a>
						<?php endif; ?>
						<?php if ( $qv_slide['cta2_label'] ) : ?>
							<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( $qv_url( $qv_slide['cta2_url'] ) ); ?>"><?php echo esc_html( $qv_slide['cta2_label'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $qv_count > 1 ) : ?>
		<div class="hero__controls">
			<div class="container hero__controls-inner">
				<div class="hero__dots">
					<?php foreach ( $qv_slides as $qv_i => $qv_slide ) : ?>
						<button type="button" class="hero__dot" data-slide-to="<?php echo (int) $qv_i; ?>" aria-current="<?php echo 0 === $qv_i ? 'true' : 'false'; ?>">
							<span class="hero__dot-label" aria-hidden="true"><?php echo esc_html( wp_trim_words( $qv_slide['eyebrow'] ? $qv_slide['eyebrow'] : $qv_slide['title'], 4, '…' ) ); ?></span>
							<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: 1: number 2: title */ __( 'Show slide %1$d: %2$s', 'quantivista' ), $qv_i + 1, $qv_slide['title'] ) ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
				<div class="hero__arrows">
					<button type="button" class="icon-btn" data-slider-pause aria-pressed="false">
						<span data-icon-pause><?php qv_the_icon( 'pause', array( 'size' => 16 ) ); ?></span>
						<span data-icon-play hidden><?php qv_the_icon( 'play', array( 'size' => 16 ) ); ?></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Pause slideshow', 'quantivista' ); ?></span>
					</button>
					<button type="button" class="icon-btn" data-slider-prev>
						<?php qv_the_icon( 'chevron-left' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Previous slide', 'quantivista' ); ?></span>
					</button>
					<button type="button" class="icon-btn" data-slider-next>
						<?php qv_the_icon( 'chevron-right' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Next slide', 'quantivista' ); ?></span>
					</button>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
