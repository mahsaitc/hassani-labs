<?php
/**
 * Seed helpers: idempotent upserts for posts, terms and generated images.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

const QVS_KEY = '_qv_seed_key';

function qvs_find_by_key( $key, $post_type = 'any', $status = 'any' ) {
	$found = get_posts(
		array(
			'post_type'        => $post_type,
			'post_status'      => $status,
			'meta_key'         => QVS_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * Insert or update a post identified by a seed key. Extra meta via $meta.
 */
function qvs_upsert_post( $key, array $postarr, array $meta = array() ) {
	$id = qvs_find_by_key( $key, $postarr['post_type'] ?? 'post' );
	$postarr = array_merge( array( 'post_status' => 'publish' ), $postarr );
	if ( $id ) {
		$postarr['ID'] = $id;
		wp_update_post( wp_slash( $postarr ) );
	} else {
		$id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( "Could not create {$key}: " . $id->get_error_message() );
			return 0;
		}
		update_post_meta( $id, QVS_KEY, $key );
	}
	foreach ( $meta as $meta_key => $value ) {
		update_post_meta( $id, $meta_key, $value );
		// ACF field reference so the field UI binds to seeded values.
		if ( str_starts_with( $meta_key, 'qv_' ) ) {
			update_post_meta( $id, '_' . $meta_key, 'field_' . $meta_key );
		}
	}
	return (int) $id;
}

/**
 * Generate an image with a GD callback and register it as an attachment.
 */
function qvs_image( $key, $filename, callable $draw, $alt ) {
	$existing = qvs_find_by_key( $key, 'attachment', 'inherit' );
	if ( $existing && file_exists( (string) get_attached_file( $existing ) ) ) {
		return $existing;
	}

	$uploads = wp_upload_dir();
	$path    = trailingslashit( $uploads['basedir'] ) . 'qv-seed/' . $filename;
	qvimg_save( $draw(), $path );

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $filename )['type'],
			'post_title'     => $alt,
			'post_status'    => 'inherit',
			'guid'           => trailingslashit( $uploads['baseurl'] ) . 'qv-seed/' . $filename,
		),
		$path
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, QVS_KEY, $key );
	return (int) $id;
}

/**
 * Insert or update a term by slug.
 */
function qvs_term( $name, $taxonomy, $slug, array $args = array(), array $meta = array() ) {
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	$args     = array_merge( array( 'slug' => $slug ), $args );
	if ( $existing ) {
		wp_update_term( $existing->term_id, $taxonomy, array_merge( array( 'name' => $name ), $args ) );
		$term_id = (int) $existing->term_id;
	} else {
		$result = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( "Term {$slug}: " . $result->get_error_message() );
			return 0;
		}
		$term_id = (int) $result['term_id'];
	}
	foreach ( $meta as $meta_key => $value ) {
		update_term_meta( $term_id, $meta_key, $value );
		if ( str_starts_with( $meta_key, 'qv_' ) ) {
			update_term_meta( $term_id, '_' . $meta_key, 'field_' . $meta_key );
		}
	}
	return $term_id;
}

/**
 * Wrap HTML in a Gutenberg "freeform" (classic) block-safe string.
 */
function qvs_html( $html ) {
	return trim( preg_replace( '/^\t+/m', '', $html ) );
}

function qvs_update_option_merge( $option, array $values ) {
	$current = get_option( $option, array() );
	update_option( $option, array_merge( is_array( $current ) ? $current : array(), $values ) );
}

/**
 * Create (or empty) a navigation menu by name.
 */
function qvs_menu( $name ) {
	$menu = wp_get_nav_menu_object( $name );
	$id   = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	foreach ( (array) wp_get_nav_menu_items( $id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	return $id;
}

function qvs_menu_item( $menu_id, array $args, $parent = 0 ) {
	return wp_update_nav_menu_item(
		$menu_id,
		0,
		array_merge(
			array(
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			),
			$args
		)
	);
}

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
