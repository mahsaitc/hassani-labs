<?php
/**
 * Customizer: brand copy, trust stats, contact details and social links.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every theme mod.
 */
function qv_mod_defaults() {
	return array(
		'header_cta_label'  => 'Get Started',
		'header_cta_url'    => '/contact/',
		'mega_promo_title'  => 'Acupuncture Clinic Software',
		'mega_promo_text'   => 'Booking, patient records and acupoint charts for clinics.',
		'stat_1_value'      => '20+',
		'stat_1_label'      => 'Years in medicine',
		'stat_2_value'      => '5',
		'stat_2_label'      => 'Open-source case studies',
		'stat_3_value'      => '2',
		'stat_3_label'      => 'Languages: English & Persian',
		'stat_4_value'      => '1:1',
		'stat_4_label'      => 'Founder-led support',
		'cta_title'         => 'Have a project in mind? Let\'s build it together.',
		'cta_text'          => 'Tell me about your idea. I\'ll scope it, recommend the right stack and send a fixed-price proposal.',
		'cta_primary_label' => 'Get a Quote',
		'cta_primary_url'   => '/contact/',
		'footer_about'      => 'Hassani Labs builds web software, clinic software and websites. Founded by Mahsa Hassani, physician and software engineer.',
		'contact_email'     => 'mahsaitc74@gmail.com',
		'contact_phone'     => '',
		'contact_address'   => 'Iran · Remote worldwide',
		'contact_hours'     => '',
		'social_x'          => '',
		'social_linkedin'   => 'https://www.linkedin.com/in/mahsahassani',
		'social_github'     => 'https://github.com/mahsaitc',
		'social_facebook'   => '',
		'social_instagram'  => '',
		'social_youtube'    => '',
		'founder_name'      => 'Mahsa Hassani',
		'founder_initials'  => 'MH',
		'founder_role'      => 'Physician (General Medicine) and Computer Engineer — Software Design',
		'founder_intro'     => 'I spent two decades in medicine before moving into software. Today I build production systems with ASP.NET Core and React, and the products sold here, including software for acupuncture clinics.',
	);
}

/**
 * Get a theme mod with its default.
 */
function qv_mod( $key ) {
	$defaults = qv_mod_defaults();
	$default  = $defaults[ $key ] ?? '';
	return qv_translate_mod( $key, get_theme_mod( 'qv_' . $key, $default ), $default );
}

/**
 * Resolve relative URLs stored in mods (e.g. "/contact/") against home_url.
 */
function qv_mod_url( $key ) {
	return qv_localize_url( (string) qv_mod( $key ) );
}

add_action( 'customize_register', 'qv_customize_register' );
function qv_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel(
		'qv_panel',
		array(
			'title'    => __( 'Hassani Labs Options', 'quantivista' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'qv_header'  => array(
			'title'  => __( 'Header', 'quantivista' ),
			'fields' => array(
				'header_cta_label' => array( __( 'CTA button label', 'quantivista' ), 'text' ),
				'header_cta_url'   => array( __( 'CTA button URL', 'quantivista' ), 'text' ),
				'mega_promo_title' => array( __( 'Mega menu promo title', 'quantivista' ), 'text' ),
				'mega_promo_text'  => array( __( 'Mega menu promo text', 'quantivista' ), 'text' ),
			),
		),
		'qv_stats'   => array(
			'title'  => __( 'Trust Bar Stats', 'quantivista' ),
			'fields' => array(),
		),
		'qv_cta'     => array(
			'title'  => __( 'Final CTA Section', 'quantivista' ),
			'fields' => array(
				'cta_title'         => array( __( 'Title', 'quantivista' ), 'text' ),
				'cta_text'          => array( __( 'Text', 'quantivista' ), 'textarea' ),
				'cta_primary_label' => array( __( 'Button label', 'quantivista' ), 'text' ),
				'cta_primary_url'   => array( __( 'Button URL', 'quantivista' ), 'text' ),
			),
		),
		'qv_contact' => array(
			'title'  => __( 'Contact & Footer', 'quantivista' ),
			'fields' => array(
				'footer_about'    => array( __( 'Footer about text', 'quantivista' ), 'textarea' ),
				'contact_email'   => array( __( 'Email', 'quantivista' ), 'email' ),
				'contact_phone'   => array( __( 'Phone', 'quantivista' ), 'text' ),
				'contact_address' => array( __( 'Address', 'quantivista' ), 'textarea' ),
				'contact_hours'   => array( __( 'Office hours', 'quantivista' ), 'text' ),
			),
		),
		'qv_social'  => array(
			'title'  => __( 'Social Profiles', 'quantivista' ),
			'fields' => array(),
		),
	);

	for ( $i = 1; $i <= 4; $i++ ) {
		/* translators: %d: stat number */
		$sections['qv_stats']['fields'][ "stat_{$i}_value" ] = array( sprintf( __( 'Stat %d value', 'quantivista' ), $i ), 'text' );
		/* translators: %d: stat number */
		$sections['qv_stats']['fields'][ "stat_{$i}_label" ] = array( sprintf( __( 'Stat %d label', 'quantivista' ), $i ), 'text' );
	}
	foreach ( array( 'x', 'linkedin', 'github', 'facebook', 'instagram', 'youtube' ) as $network ) {
		$sections['qv_social']['fields'][ "social_{$network}" ] = array( ucfirst( $network ) . ' URL', 'url' );
	}

	$defaults = qv_mod_defaults();
	foreach ( $sections as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title' => $section['title'],
				'panel' => 'qv_panel',
			)
		);
		foreach ( $section['fields'] as $key => $field ) {
			$sanitize = array(
				'url'      => 'esc_url_raw',
				'email'    => 'sanitize_email',
				'textarea' => 'sanitize_textarea_field',
			)[ $field[1] ] ?? 'sanitize_text_field';

			$wp_customize->add_setting(
				'qv_' . $key,
				array(
					'default'           => $defaults[ $key ] ?? '',
					'sanitize_callback' => $sanitize,
				)
			);
			$wp_customize->add_control(
				'qv_' . $key,
				array(
					'label'   => $field[0],
					'section' => $section_id,
					'type'    => $field[1],
				)
			);
		}
	}
}
