<?php
/**
 * Template helpers shared across templates.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a custom field through ACF when available, raw post meta otherwise.
 * ACF stores simple fields under the same meta key, so both paths agree.
 */
function qv_field( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $key, $post_id );
		if ( null !== $value && '' !== $value && false !== $value ) {
			return $value;
		}
	}
	return get_post_meta( $post_id, $key, true );
}

/**
 * Split a textarea field into trimmed, non-empty lines.
 */
function qv_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) ) );
}

/**
 * Estimated reading time in minutes.
 */
function qv_reading_time( $post_id = null ) {
	$content = get_post_field( 'post_content', $post_id ? $post_id : get_the_ID() );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( (string) $content ) ) );
	$wpm     = (int) apply_filters( 'qv_reading_wpm', 225 );
	return max( 1, (int) ceil( $words / max( 1, $wpm ) ) );
}

/**
 * Section heading block: eyebrow, title, lead.
 */
function qv_section_head( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow' => '',
			'title'   => '',
			'lead'    => '',
			'align'   => 'center',
			'tag'     => 'h2',
			'id'      => '',
		)
	);
	$tag  = in_array( $args['tag'], array( 'h1', 'h2', 'h3' ), true ) ? $args['tag'] : 'h2';
	?>
	<header class="section-head section-head--<?php echo esc_attr( $args['align'] ); ?>" data-reveal>
		<?php if ( $args['eyebrow'] ) : ?>
			<p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
		<?php endif; ?>
		<<?php echo $tag; ?> class="section-head__title"<?php echo $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?>><?php echo wp_kses_post( $args['title'] ); ?></<?php echo $tag; ?>>
		<?php if ( $args['lead'] ) : ?>
			<p class="section-head__lead"><?php echo wp_kses_post( $args['lead'] ); ?></p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Brand logo (custom logo if set, otherwise SVG mark + wordmark).
 */
function qv_site_logo( $context = 'header' ) {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="site-logo" href="%1$s" rel="home">%2$s<span class="site-logo__text">%3$s</span></a>',
		esc_url( home_url( '/' ) ),
		qv_logo_mark( 'qv-mark-' . $context ), // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Post meta line: date · reading time.
 */
function qv_post_meta( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	?>
	<span class="post-meta">
		<time datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time>
		<span class="post-meta__sep" aria-hidden="true">&middot;</span>
		<span class="post-meta__read">
			<?php
			/* translators: %d: minutes */
			printf( esc_html( _n( '%d min read', '%d min read', qv_reading_time( $post_id ), 'quantivista' ) ), (int) qv_reading_time( $post_id ) );
			?>
		</span>
	</span>
	<?php
}

/**
 * First category as a pill link.
 */
function qv_primary_category( $post_id = null ) {
	$cats = get_the_category( $post_id ? $post_id : get_the_ID() );
	if ( empty( $cats ) ) {
		return;
	}
	printf( '<a class="pill" href="%s">%s</a>', esc_url( get_category_link( $cats[0] ) ), esc_html( $cats[0]->name ) );
}

/**
 * Breadcrumbs: Yoast when enabled, else WooCommerce on shop pages, else a simple trail.
 */
function qv_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'quantivista' ) . '">', '</nav>' );
		return;
	}
	if ( function_exists( 'woocommerce_breadcrumb' ) && function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		woocommerce_breadcrumb(
			array(
				'wrap_before' => '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'quantivista' ) . '">',
				'wrap_after'  => '</nav>',
				'delimiter'   => '<span class="breadcrumbs__sep" aria-hidden="true">/</span>',
			)
		);
		return;
	}
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'quantivista' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'quantivista' ) . '</a><span class="breadcrumbs__sep" aria-hidden="true">/</span><span>' . esc_html( wp_get_document_title() ) . '</span></nav>';
}

/**
 * Social share links (plain URLs — no third-party scripts).
 */
function qv_share_links( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = rawurlencode( get_permalink( $post_id ) );
	$title   = rawurlencode( wp_strip_all_tags( get_the_title( $post_id ) ) );
	$targets = array(
		'x'        => array( __( 'Share on X', 'quantivista' ), "https://twitter.com/intent/tweet?url={$url}&text={$title}" ),
		'linkedin' => array( __( 'Share on LinkedIn', 'quantivista' ), "https://www.linkedin.com/sharing/share-offsite/?url={$url}" ),
		'facebook' => array( __( 'Share on Facebook', 'quantivista' ), "https://www.facebook.com/sharer/sharer.php?u={$url}" ),
		'mail'     => array( __( 'Share by email', 'quantivista' ), "mailto:?subject={$title}&body={$url}" ),
	);
	?>
	<div class="share">
		<span class="share__label"><?php esc_html_e( 'Share', 'quantivista' ); ?></span>
		<ul class="share__list">
			<?php foreach ( $targets as $icon => $target ) : ?>
				<li>
					<a class="share__btn" href="<?php echo esc_url( $target[1] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php qv_the_icon( $icon, array( 'size' => 16 ) ); ?>
						<span class="screen-reader-text"><?php echo esc_html( $target[0] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
			<li>
				<button type="button" class="share__btn" data-copy-link="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
					<?php qv_the_icon( 'link', array( 'size' => 16 ) ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Copy link', 'quantivista' ); ?></span>
				</button>
			</li>
		</ul>
	</div>
	<?php
}

/**
 * Social profile links from the Customizer.
 */
function qv_social_links( $class = '' ) {
	$networks = array(
		'x'         => 'X',
		'linkedin'  => 'LinkedIn',
		'github'    => 'GitHub',
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
	);
	$items    = '';
	foreach ( $networks as $key => $label ) {
		$url = qv_mod( 'social_' . $key );
		if ( ! $url ) {
			continue;
		}
		$items .= sprintf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer me">%2$s<span class="screen-reader-text">%3$s</span></a></li>',
			esc_url( $url ),
			qv_icon( $key, array( 'size' => 18 ) ),
			/* translators: %s: network name */
			esc_html( sprintf( __( 'Hassani Labs on %s', 'quantivista' ), $label ) )
		);
	}
	if ( $items ) {
		echo '<ul class="social-links ' . esc_attr( $class ) . '">' . $items . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Star rating (0–5) rendered as icons with an accessible label.
 */
function qv_stars( $rating ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	$out    = '<span class="stars" role="img" aria-label="' . esc_attr( sprintf( /* translators: %s rating */ __( 'Rated %s out of 5', 'quantivista' ), $rating ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= qv_icon( 'star', array( 'size' => 16, 'class' => $i <= round( $rating ) ? 'is-on' : 'is-off' ) );
	}
	return $out . '</span>';
}

/**
 * Themed pagination.
 */
function qv_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => qv_icon( 'chevron-left', array( 'size' => 16 ) ) . '<span class="screen-reader-text">' . __( 'Previous page', 'quantivista' ) . '</span>',
			'next_text' => '<span class="screen-reader-text">' . __( 'Next page', 'quantivista' ) . '</span>' . qv_icon( 'chevron-right', array( 'size' => 16 ) ),
		)
	);
}

/**
 * Page hero used by inner pages.
 */
function qv_page_hero( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow'     => '',
			'title'       => get_the_title(),
			'lead'        => '',
			'breadcrumbs' => true,
			'class'       => '',
		)
	);
	?>
	<section class="page-hero <?php echo esc_attr( $args['class'] ); ?>">
		<div class="page-hero__glow" aria-hidden="true"></div>
		<div class="container page-hero__inner">
			<?php
			if ( $args['breadcrumbs'] ) {
				qv_breadcrumbs();
			}
			?>
			<?php if ( $args['eyebrow'] ) : ?>
				<p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h1 class="page-hero__title"><?php echo wp_kses_post( $args['title'] ); ?></h1>
			<?php if ( $args['lead'] ) : ?>
				<p class="page-hero__lead"><?php echo wp_kses_post( $args['lead'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Related posts by shared category.
 */
function qv_related_posts_query( $post_id, $count = 3 ) {
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'post__not_in'        => array( $post_id ),
			'category__in'        => wp_get_post_categories( $post_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}
