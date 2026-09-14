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
		'mega_promo_title'  => 'FlowPilot 3.0',
		'mega_promo_text'   => 'Visual workflow automation with 120+ integrations.',
		'stat_1_value'      => '480+',
		'stat_1_label'      => 'Projects delivered',
		'stat_2_value'      => '12',
		'stat_2_label'      => 'Years of experience',
		'stat_3_value'      => '310+',
		'stat_3_label'      => 'Clients worldwide',
		'stat_4_value'      => '4.9/5',
		'stat_4_label'      => 'Average rating',
		'cta_title'         => 'Have a project in mind? Let\'s build it together.',
		'cta_text'          => 'Book a free 30-minute consultation with our product team. We\'ll scope your idea, recommend the right stack and send a fixed-price proposal within 48 hours.',
		'cta_primary_label' => 'Get a Quote',
		'cta_primary_url'   => '/contact/',
		'footer_about'      => 'Quantivista builds web software, mobile apps and high-converting websites for ambitious teams. Designed with care, engineered to scale.',
		'contact_email'     => 'hello@quantivista.com',
		'contact_phone'     => '+1 (415) 555-0142',
		'contact_address'   => '548 Market Street, Suite 300, San Francisco, CA 94104',
		'contact_hours'     => 'Mon–Fri, 9:00–18:00 PT',
		'social_x'          => 'https://x.com/quantivista',
		'social_linkedin'   => 'https://www.linkedin.com/company/quantivista',
		'social_github'     => 'https://github.com/quantivista',
		'social_facebook'   => '',
		'social_instagram'  => 'https://www.instagram.com/quantivista',
		'social_youtube'    => 'https://www.youtube.com/@quantivista',
	);
}

/**
 * Get a theme mod with its default.
 */
function qv_mod( $key ) {
	$defaults = qv_mod_defaults();
	return get_theme_mod( 'qv_' . $key, $defaults[ $key ] ?? '' );
}

/**
 * Resolve relative URLs stored in mods (e.g. "/contact/") against home_url.
 */
function qv_mod_url( $key ) {
	$url = (string) qv_mod( $key );
	return str_starts_with( $url, '/' ) ? home_url( $url ) : $url;
}

add_action( 'customize_register', 'qv_customize_register' );
function qv_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel(
		'qv_panel',
		array(
			'title'    => __( 'Quantivista Options', 'quantivista' ),
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
