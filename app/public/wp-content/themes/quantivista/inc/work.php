<?php
/**
 * Portfolio helpers (projects live in mu-plugins/quantivista-core/portfolio.php).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/**
 * Poster for a project without a featured image: the qv_poster meta, either
 * a full URL or a file name under assets/img/work/.
 */
function qv_project_poster( $project_id ) {
	$poster = (string) get_post_meta( $project_id, 'qv_poster', true );
	if ( '' === $poster ) {
		return '';
	}
	return preg_match( '#^https?://#', $poster ) ? $poster : QV_URI . '/assets/img/work/' . rawurlencode( $poster );
}
