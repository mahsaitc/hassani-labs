<?php
/**
 * Blog: the founder's articles (from scripts/seed/data/portfolio.json),
 * one author, categories and cover images. Removes the old demo authors
 * and articles if an earlier seed created them.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

$qv_data = json_decode( (string) file_get_contents( __DIR__ . '/data/portfolio.json' ), true );

/* ------------------------------------------------------------------------
 * Remove demo content from earlier seeds
 * --------------------------------------------------------------------- */
foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => QVS_KEY ) ) as $qv_old ) { // phpcs:ignore WordPress.DB.SlowDBQuery
	$qv_old_key = (string) get_post_meta( $qv_old, QVS_KEY, true );
	if ( ! str_starts_with( $qv_old_key, 'article-' ) ) {
		wp_delete_post( $qv_old, true );
	}
}
require_once ABSPATH . 'wp-admin/includes/user.php';
foreach ( array( 'elena.marsh', 'samuel.ortiz', 'nadia.karimi', 'yuki.sato' ) as $qv_login ) {
	$qv_user = get_user_by( 'login', $qv_login );
	if ( $qv_user ) {
		wp_delete_user( $qv_user->ID );
	}
}

/* ------------------------------------------------------------------------
 * Author
 * --------------------------------------------------------------------- */
$qv_person = $qv_data['person'];
$qv_user   = get_user_by( 'login', 'mahsa.hassani' );
$qv_uid    = $qv_user ? $qv_user->ID : wp_insert_user(
	array(
		'user_login' => 'mahsa.hassani',
		'user_email' => 'author@' . wp_parse_url( home_url(), PHP_URL_HOST ),
		'user_pass'  => wp_generate_password( 24 ),
		'role'       => 'author',
	)
);
if ( is_wp_error( $qv_uid ) ) {
	WP_CLI::warning( 'Author: ' . $qv_uid->get_error_message() );
	$qv_uid = 1;
}
wp_update_user(
	array(
		'ID'           => $qv_uid,
		'first_name'   => 'Mahsa',
		'last_name'    => 'Hassani',
		'display_name' => $qv_person['name'],
		'nickname'     => 'Mahsa',
		'description'  => 'Physician (General Medicine) and computer engineer specialising in software design. I build production systems with ASP.NET Core and React, and software for clinics.',
		'user_url'     => $qv_data['links']['linkedin'],
	)
);
update_user_meta( $qv_uid, 'qv_job_title', 'Founder, Hassani Labs' );

/* ------------------------------------------------------------------------
 * Categories
 * --------------------------------------------------------------------- */
$qv_cat_ids = array(
	'engineering' => qvs_term( 'Engineering', 'category', 'engineering', array( 'description' => 'Architecture, testing and the practical details of shipping reliable software.' ) ),
);
$qv_uncat   = get_term_by( 'slug', 'uncategorized', 'category' );
if ( $qv_uncat ) {
	wp_update_term( $qv_uncat->term_id, 'category', array( 'name' => 'General' ) );
}
foreach ( array( 'product-updates', 'design', 'mobile', 'business' ) as $qv_slug ) {
	$qv_term = get_term_by( 'slug', $qv_slug, 'category' );
	if ( $qv_term && ! $qv_term->count ) {
		wp_delete_term( $qv_term->term_id, 'category' );
	}
}

/* ------------------------------------------------------------------------
 * Articles
 * --------------------------------------------------------------------- */

/**
 * Article body blocks (p, h2, ul, quote, code) to HTML. Inline **bold**
 * and `code` are kept.
 */
function qvs_blocks_html( array $blocks ) {
	$inline = function ( $text ) {
		$text = esc_html( $text );
		$text = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text );
		return preg_replace( '/`([^`]+)`/', '<code>$1</code>', $text );
	};
	$html = '';
	foreach ( $blocks as $b ) {
		switch ( $b['type'] ) {
			case 'h2':
				$html .= '<h2>' . $inline( $b['text'] ) . "</h2>\n";
				break;
			case 'ul':
				$html .= '<ul>' . implode( '', array_map( fn( $i ) => '<li>' . $inline( $i ) . '</li>', $b['items'] ) ) . "</ul>\n";
				break;
			case 'quote':
				$html .= '<blockquote><p>' . $inline( $b['text'] ) . "</p></blockquote>\n";
				break;
			case 'code':
				$html .= '<pre><code class="language-' . esc_attr( $b['lang'] ?? 'text' ) . '">' . esc_html( $b['code'] ) . "</code></pre>\n";
				break;
			default:
				$html .= '<p>' . $inline( $b['text'] ) . "</p>\n";
		}
	}
	return $html;
}

$qv_palettes = array( array( '22D3EE', '5B6CFF' ), array( 'A78BFA', '22D3EE' ), array( '2DD4BF', '3B82F6' ) );
foreach ( $qv_data['posts'] as $qv_i => $qv_p ) {
	$qv_post_id = qvs_upsert_post(
		'article-' . $qv_p['slug'],
		array(
			'post_type'      => 'post',
			'post_title'     => $qv_p['title'],
			'post_name'      => $qv_p['slug'],
			'post_content'   => qvs_blocks_html( $qv_p['body'] ),
			'post_excerpt'   => $qv_p['excerpt'],
			'post_author'    => $qv_uid,
			'post_date'      => $qv_p['date'] . ' 10:00:00',
			'post_category'  => array( $qv_cat_ids['engineering'] ),
			'comment_status' => 'open',
		)
	);
	wp_set_post_tags( $qv_post_id, $qv_p['tags'], false );

	$qv_pal = $qv_palettes[ $qv_i % count( $qv_palettes ) ];
	set_post_thumbnail(
		$qv_post_id,
		qvs_image( 'cover-' . $qv_p['slug'], 'cover-' . $qv_p['slug'] . '.jpg', fn() => qvimg_cover( 1600, 900, '{ }', $qv_pal[0], $qv_pal[1], $qv_p['slug'] ), $qv_p['title'] )
	);
	WP_CLI::log( "    post: {$qv_p['title']} (#{$qv_post_id})" );
}
