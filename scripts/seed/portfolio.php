<?php
/**
 * Portfolio: projects (case studies), the founder's timeline and skills,
 * from scripts/seed/data/portfolio.json (exported from mahsahassani.dev).
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

$qv_data = json_decode( (string) file_get_contents( __DIR__ . '/data/portfolio.json' ), true );

/* ------------------------------------------------------------------------
 * Projects
 * --------------------------------------------------------------------- */
foreach ( $qv_data['projects'] as $qv_i => $qv_p ) {
	$qv_arch    = $qv_p['architecture'];
	$qv_content = '<h2>Architecture</h2><p>' . esc_html( $qv_arch['overview'] ) . '</p>';
	if ( ! empty( $qv_arch['layers'] ) ) {
		$qv_content .= '<ul>' . implode( '', array_map( fn( $l ) => '<li><strong>' . esc_html( $l['name'] ) . '</strong>: ' . esc_html( $l['detail'] ) . '</li>', $qv_arch['layers'] ) ) . '</ul>';
	}
	if ( ! empty( $qv_p['outcome'] ) ) {
		$qv_content .= '<h2>' . esc_html( $qv_p['outcome']['heading'] ) . '</h2><p>' . esc_html( $qv_p['outcome']['body'] ) . '</p>';
	}

	$qv_id = qvs_upsert_post(
		'project-' . $qv_p['slug'],
		array(
			'post_type'    => 'qv_project',
			'post_title'   => $qv_p['name'],
			'post_name'    => $qv_p['slug'],
			'post_excerpt' => $qv_p['summary'],
			'post_content' => $qv_content,
			'menu_order'   => $qv_i,
		),
		array(
			'qv_tagline'    => $qv_p['tagline'],
			'qv_year'       => $qv_p['year'],
			'qv_repo'       => $qv_p['repo'],
			'qv_demo'       => $qv_p['demo'] ?? '',
			'qv_poster'     => $qv_p['poster'],
			'qv_poster_alt' => $qv_p['alt'],
			'qv_stack'      => implode( "\n", $qv_p['stack'] ),
			'qv_metrics'    => implode( "\n", array_map( fn( $m ) => $m['label'] . ' | ' . $m['value'], $qv_p['metrics'] ?? array() ) ),
			'qv_featured'   => ! empty( $qv_p['featured'] ) ? '1' : '',
		)
	);
	wp_set_object_terms( $qv_id, $qv_p['tech'], 'qv_tech', false );
	WP_CLI::log( "    project: {$qv_p['name']} (#{$qv_id})" );
}

/* ------------------------------------------------------------------------
 * Timeline (replaces the demo milestones)
 * --------------------------------------------------------------------- */
foreach ( $qv_data['timeline'] as $qv_i => $qv_ms ) {
	qvs_upsert_post(
		"milestone-{$qv_i}",
		array( 'post_type' => 'qv_milestone', 'post_title' => $qv_ms['title'], 'post_content' => $qv_ms['org'] ?? '', 'menu_order' => $qv_i ),
		array( 'qv_year' => $qv_ms['period'] )
	);
}
for ( $qv_i = count( $qv_data['timeline'] ); $qv_id = qvs_find_by_key( "milestone-{$qv_i}", 'qv_milestone' ); $qv_i++ ) {
	wp_delete_post( $qv_id, true );
}

/* ------------------------------------------------------------------------
 * Skills on the About page
 * --------------------------------------------------------------------- */
$qv_about = qvs_find_by_key( 'page-about', 'page' );
if ( $qv_about ) {
	$qv_skills = array_map(
		fn( $g ) => array(
			'title'  => $g['title'],
			'blurb'  => $g['blurb'],
			'skills' => array_map( fn( $s ) => array( 'name' => $s['name'], 'note' => $s['note'] ?? '' ), $g['skills'] ),
		),
		$qv_data['skills']
	);
	update_post_meta( $qv_about, 'qv_skills', wp_json_encode( $qv_skills, JSON_UNESCAPED_UNICODE ) );
}
