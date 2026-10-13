<?php
/**
 * Home page contact form (the same built-in form as the Contact page).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qvc_contact_form' ) ) {
	return;
}
?>
<section class="section section--muted home-contact" aria-labelledby="home-contact-title">
	<div class="container home-contact__grid">
		<div>
			<?php
			qv_section_head(
				array(
					'eyebrow' => __( 'Contact', 'quantivista' ),
					'title'   => __( 'Tell me about your project', 'quantivista' ),
					'lead'    => __( 'I reply within two business days. Fields marked * are required.', 'quantivista' ),
					'id'      => 'home-contact-title',
					'align'   => 'start',
				)
			);
			?>
			<?php qv_social_links( 'social-links--named', true ); ?>
		</div>
		<div class="form-card" id="contact-form">
			<?php qvc_contact_form(); ?>
		</div>
	</div>
</section>
