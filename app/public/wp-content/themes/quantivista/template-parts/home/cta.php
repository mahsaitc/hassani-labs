<?php
/**
 * Final call-to-action band (also used on About).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_phone = qv_mod( 'contact_phone' );
$qv_email = qv_mod( 'contact_email' );
?>
<section class="section" aria-labelledby="cta-title">
	<div class="container">
		<div class="cta-band" data-reveal>
			<div class="cta-band__grid">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Free consultation', 'quantivista' ); ?></p>
					<h2 class="cta-band__title" id="cta-title"><?php echo esc_html( qv_mod( 'cta_title' ) ); ?></h2>
					<p class="cta-band__text"><?php echo esc_html( qv_mod( 'cta_text' ) ); ?></p>
					<div class="btn-row">
						<a class="btn btn--accent btn--lg" href="<?php echo esc_url( qv_mod_url( 'cta_primary_url' ) ); ?>"><?php echo esc_html( qv_mod( 'cta_primary_label' ) ); ?> <?php qv_the_icon( 'arrow-right', array( 'size' => 18 ) ); ?></a>
						<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
							<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse products', 'quantivista' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<ul class="cta-band__contact">
					<?php if ( $qv_email ) : ?>
						<li><?php qv_the_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( antispambot( $qv_email ) ); ?>"><?php echo esc_html( antispambot( $qv_email ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( $qv_phone ) : ?>
						<li><?php qv_the_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $qv_phone ) ); ?>"><?php echo esc_html( $qv_phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( qv_mod( 'contact_hours' ) ) : ?>
						<li><?php qv_the_icon( 'clock' ); ?><span><?php echo esc_html( qv_mod( 'contact_hours' ) ); ?></span></li>
					<?php endif; ?>
					<li><?php qv_the_icon( 'map-pin' ); ?><span><?php echo esc_html( qv_mod( 'contact_address' ) ); ?></span></li>
				</ul>
			</div>
		</div>
	</div>
</section>
