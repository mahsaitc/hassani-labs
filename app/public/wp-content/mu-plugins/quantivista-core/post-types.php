<?php
/**
 * Structured content types: hero slides, testimonials, team, milestones,
 * newsletter subscribers. Grouped under a "Site Content" admin menu.
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

function qvc_labels( $singular, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'add_new'            => __( 'Add New', 'quantivista' ),
		/* translators: %s: singular label */
		'add_new_item'       => sprintf( __( 'Add New %s', 'quantivista' ), $singular ),
		/* translators: %s: singular label */
		'edit_item'          => sprintf( __( 'Edit %s', 'quantivista' ), $singular ),
		/* translators: %s: singular label */
		'new_item'           => sprintf( __( 'New %s', 'quantivista' ), $singular ),
		/* translators: %s: plural label */
		'search_items'       => sprintf( __( 'Search %s', 'quantivista' ), $plural ),
		/* translators: %s: plural label */
		'not_found'          => sprintf( __( 'No %s found', 'quantivista' ), strtolower( $plural ) ),
		'all_items'          => $plural,
		'menu_name'          => $plural,
	);
}

add_action( 'init', 'qvc_register_post_types' );
function qvc_register_post_types() {
	$common = array(
		'public'              => false,
		'show_ui'             => true,
		'show_in_rest'        => true,
		'show_in_menu'        => 'qv-content',
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'capability_type'     => 'page',
		'map_meta_cap'        => true,
	);

	register_post_type(
		'qv_slide',
		array_merge(
			$common,
			array(
				'labels'   => qvc_labels( __( 'Hero Slide', 'quantivista' ), __( 'Hero Slides', 'quantivista' ) ),
				'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'qv_testimonial',
		array_merge(
			$common,
			array(
				'labels'   => qvc_labels( __( 'Testimonial', 'quantivista' ), __( 'Testimonials', 'quantivista' ) ),
				'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'qv_team',
		array_merge(
			$common,
			array(
				'labels'   => qvc_labels( __( 'Team Member', 'quantivista' ), __( 'Team Members', 'quantivista' ) ),
				'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'qv_milestone',
		array_merge(
			$common,
			array(
				'labels'   => qvc_labels( __( 'Milestone', 'quantivista' ), __( 'Milestones', 'quantivista' ) ),
				'supports' => array( 'title', 'editor', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'qv_subscriber',
		array_merge(
			$common,
			array(
				'labels'       => qvc_labels( __( 'Subscriber', 'quantivista' ), __( 'Newsletter Subscribers', 'quantivista' ) ),
				'supports'     => array( 'title' ),
				'show_in_rest' => false,
				'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			)
		)
	);

	register_post_status(
		'qv_pending',
		array(
			'label'                     => _x( 'Unconfirmed', 'subscriber status', 'quantivista' ),
			'public'                    => false,
			'internal'                  => false,
			'protected'                 => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: count */
			'label_count'               => _n_noop( 'Unconfirmed <span class="count">(%s)</span>', 'Unconfirmed <span class="count">(%s)</span>', 'quantivista' ),
		)
	);
}

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'Site Content', 'quantivista' ),
			__( 'Site Content', 'quantivista' ),
			'edit_pages',
			'qv-content',
			'',
			'dashicons-screenoptions',
			21
		);
	},
	9
);

/**
 * Helpful list-table columns.
 */
add_filter(
	'manage_qv_team_posts_columns',
	fn( $cols ) => array_slice( $cols, 0, 2, true ) + array( 'qv_role' => __( 'Role', 'quantivista' ) ) + array_slice( $cols, 2, null, true )
);
add_filter(
	'manage_qv_testimonial_posts_columns',
	fn( $cols ) => array_slice( $cols, 0, 2, true ) + array( 'qv_role' => __( 'Role / Company', 'quantivista' ) ) + array_slice( $cols, 2, null, true )
);
add_filter(
	'manage_qv_milestone_posts_columns',
	fn( $cols ) => array_slice( $cols, 0, 2, true ) + array( 'qv_year' => __( 'Year', 'quantivista' ) ) + array_slice( $cols, 2, null, true )
);

foreach ( array( 'qv_team', 'qv_testimonial', 'qv_milestone' ) as $qvc_type ) {
	add_action(
		"manage_{$qvc_type}_posts_custom_column",
		function ( $column, $post_id ) {
			if ( 'qv_role' === $column ) {
				$company = get_post_meta( $post_id, 'qv_company', true );
				echo esc_html( trim( get_post_meta( $post_id, 'qv_role', true ) . ( $company ? ', ' . $company : '' ) ) );
			} elseif ( 'qv_year' === $column ) {
				echo esc_html( get_post_meta( $post_id, 'qv_year', true ) );
			}
		},
		10,
		2
	);
}
unset( $qvc_type );
