<?php
/**
 * Portfolio: projects (case studies) under /work/, with a Technology
 * taxonomy. Content comes from the founder's portfolio (mahsahassani.dev),
 * seeded by scripts/seed/portfolio.php.
 *
 * Project meta (all optional): qv_tagline, qv_year, qv_repo, qv_demo,
 * qv_poster (image URL when no featured image), qv_stack (one per line),
 * qv_metrics ("label | value" per line), qv_featured ("1").
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'qv_project',
			array(
				'labels'        => array(
					'name'          => __( 'Projects', 'quantivista' ),
					'singular_name' => __( 'Project', 'quantivista' ),
					'add_new_item'  => __( 'Add project', 'quantivista' ),
					'edit_item'     => __( 'Edit project', 'quantivista' ),
					'all_items'     => __( 'All projects', 'quantivista' ),
				),
				'public'        => true,
				'has_archive'   => 'work',
				'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
				'menu_icon'     => 'dashicons-portfolio',
				'menu_position' => 21,
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields' ),
			)
		);

		register_taxonomy(
			'qv_tech',
			'qv_project',
			array(
				'labels'            => array(
					'name'          => __( 'Technologies', 'quantivista' ),
					'singular_name' => __( 'Technology', 'quantivista' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'work/tech', 'with_front' => false ),
			)
		);

		foreach ( array( 'qv_tagline', 'qv_year', 'qv_repo', 'qv_demo', 'qv_poster', 'qv_stack', 'qv_metrics', 'qv_featured' ) as $key ) {
			register_post_meta(
				'qv_project',
				$key,
				array(
					'type'          => 'string',
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
);

/**
 * Projects in menu order, featured first when asked.
 */
function qvc_projects( $count = -1, $featured_only = false ) {
	$args = array(
		'post_type'      => 'qv_project',
		'posts_per_page' => $count,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
	);
	if ( $featured_only ) {
		$args['meta_key']   = 'qv_featured'; // phpcs:ignore WordPress.DB.SlowDBQuery
		$args['meta_value'] = '1'; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	return get_posts( $args );
}

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() && $query->is_main_query() && ( $query->is_post_type_archive( 'qv_project' ) || $query->is_tax( 'qv_tech' ) ) ) {
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
			$query->set( 'posts_per_page', 24 );
		}
	}
);
