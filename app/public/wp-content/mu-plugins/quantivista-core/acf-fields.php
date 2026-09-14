<?php
/**
 * ACF field groups, registered in code so they are version-controlled.
 * (ACF free: no repeaters — repeatable content uses custom post types.)
 *
 * @package QuantivistaCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Compact field factory.
 */
function qvc_acf_field( $name, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => 'field_' . $name,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

add_action( 'acf/init', 'qvc_register_acf_fields' );
function qvc_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_slide',
			'title'    => 'Slide Content',
			'fields'   => array(
				qvc_acf_field( 'qv_slide_eyebrow', 'Badge text', 'text', array( 'instructions' => 'Short label shown above the headline, e.g. "New · FlowPilot 3.0".' ) ),
				qvc_acf_field( 'qv_slide_subheadline', 'Subheadline', 'textarea', array( 'rows' => 3 ) ),
				qvc_acf_field( 'qv_slide_video', 'Background video (MP4 URL)', 'url', array( 'instructions' => 'Muted, looping background video. Leave empty to use the featured image.' ) ),
				qvc_acf_field( 'qv_slide_cta_label', 'Primary button label', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				qvc_acf_field( 'qv_slide_cta_url', 'Primary button URL', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				qvc_acf_field( 'qv_slide_cta2_label', 'Secondary button label', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				qvc_acf_field( 'qv_slide_cta2_url', 'Secondary button URL', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
			),
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'qv_slide' ) ) ),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_testimonial',
			'title'    => 'Testimonial Details',
			'fields'   => array(
				qvc_acf_field( 'qv_role', 'Job title', 'text', array( 'wrapper' => array( 'width' => 40 ) ) ),
				qvc_acf_field( 'qv_company', 'Company', 'text', array( 'wrapper' => array( 'width' => 40 ) ) ),
				qvc_acf_field( 'qv_rating', 'Rating', 'number', array( 'min' => 1, 'max' => 5, 'default_value' => 5, 'wrapper' => array( 'width' => 20 ) ) ),
			),
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'qv_testimonial' ) ) ),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_team',
			'title'    => 'Team Member Details',
			'fields'   => array(
				qvc_acf_field( 'qv_role', 'Role', 'text' ),
				qvc_acf_field( 'qv_linkedin', 'LinkedIn URL', 'url' ),
				qvc_acf_field( 'qv_bio', 'Short bio', 'textarea', array( 'rows' => 3 ) ),
			),
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'qv_team' ) ) ),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_milestone',
			'title'    => 'Milestone',
			'fields'   => array(
				qvc_acf_field( 'qv_year', 'Year', 'text' ),
			),
			'position' => 'side',
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'qv_milestone' ) ) ),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_product',
			'title'    => 'Product Details (Quantivista)',
			'fields'   => array(
				qvc_acf_field(
					'qv_delivery',
					'Delivery type',
					'select',
					array(
						'choices'       => array(
							'download' => 'Digital download + license key',
							'service'  => 'Service package (delivered by our team)',
						),
						'default_value' => 'download',
						'wrapper'       => array( 'width' => 34 ),
					)
				),
				qvc_acf_field( 'qv_version', 'Current version', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
				qvc_acf_field( 'qv_last_updated', 'Last updated', 'date_picker', array( 'display_format' => 'F j, Y', 'return_format' => 'F j, Y', 'wrapper' => array( 'width' => 33 ) ) ),
				qvc_acf_field( 'qv_demo_url', 'Live demo URL', 'url' ),
				qvc_acf_field( 'qv_highlights', 'Highlights', 'textarea', array( 'rows' => 4, 'instructions' => 'One per line. Shown next to the price.' ) ),
				qvc_acf_field( 'qv_features', 'Features tab', 'textarea', array( 'rows' => 8, 'instructions' => 'One feature per line: <code>Title | Description</code>' ) ),
				qvc_acf_field( 'qv_requirements', 'Requirements / Specs tab', 'textarea', array( 'rows' => 8, 'instructions' => 'One spec per line: <code>Label: Value</code>' ) ),
			),
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ) ),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_product_cat',
			'title'    => 'Category Display',
			'fields'   => array(
				qvc_acf_field(
					'qv_cat_icon',
					'Icon',
					'select',
					array(
						'choices'       => array(
							'code'       => 'Code',
							'smartphone' => 'Smartphone',
							'layout'     => 'Layout',
							'zap'        => 'Automation',
							'trending'   => 'Analytics',
							'users'      => 'CRM',
							'bag'        => 'Commerce',
						),
						'default_value' => 'code',
					)
				),
				qvc_acf_field( 'qv_cat_tagline', 'Short tagline', 'text' ),
			),
			'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'product_cat' ) ) ),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_about',
			'title'    => 'About Page Sections',
			'fields'   => array(
				qvc_acf_field( 'qv_story_title', 'Story heading', 'text' ),
				qvc_acf_field( 'qv_story_image', 'Story image', 'image', array( 'return_format' => 'id', 'preview_size' => 'medium' ) ),
				qvc_acf_field( 'qv_mission', 'Mission statement', 'textarea', array( 'rows' => 3 ) ),
				qvc_acf_field( 'qv_vision', 'Vision statement', 'textarea', array( 'rows' => 3 ) ),
				qvc_acf_field( 'qv_values', 'Values', 'textarea', array( 'rows' => 4, 'instructions' => 'One per line: <code>Title | Description</code>' ) ),
			),
			'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/about.php' ) ) ),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_qv_contact',
			'title'    => 'Contact Page',
			'fields'   => array(
				qvc_acf_field( 'qv_form_shortcode', 'Form shortcode', 'text', array( 'instructions' => 'Contact Form 7 shortcode, e.g. [contact-form-7 id="123" title="Contact"]' ) ),
				qvc_acf_field( 'qv_faq', 'FAQ', 'textarea', array( 'rows' => 6, 'instructions' => 'One per line: <code>Question | Answer</code>' ) ),
			),
			'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/contact.php' ) ) ),
		)
	);
}
