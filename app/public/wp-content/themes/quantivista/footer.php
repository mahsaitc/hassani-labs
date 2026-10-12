<?php
/**
 * Site footer.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only status flag.
$qv_newsletter = isset( $_GET['newsletter'] ) ? sanitize_key( wp_unslash( $_GET['newsletter'] ) ) : '';
$qv_messages   = array(
	'success' => __( 'Thanks! Please check your inbox to confirm your subscription.', 'quantivista' ),
	'exists'  => __( 'You are already subscribed — thank you!', 'quantivista' ),
	'invalid' => __( 'Please enter a valid email address.', 'quantivista' ),
);
?>
</main>

<footer class="site-footer">
	<div class="site-footer__glow" aria-hidden="true"></div>
	<div class="container">
		<div class="footer-grid">
			<div class="footer-col footer-col--brand">
				<?php qv_site_logo( 'footer' ); ?>
				<p class="footer-about"><?php echo esc_html( qv_mod( 'footer_about' ) ); ?></p>
				<?php qv_social_links( 'social-links--footer' ); ?>
			</div>

			<nav class="footer-col" aria-labelledby="footer-quick-title">
				<h2 class="footer-col__title" id="footer-quick-title"><?php esc_html_e( 'Company', 'quantivista' ); ?></h2>
				<?php wp_nav_menu( array( 'theme_location' => 'footer_quick', 'container' => false, 'menu_class' => 'footer-menu', 'depth' => 1, 'fallback_cb' => false ) ); ?>
			</nav>

			<nav class="footer-col" aria-labelledby="footer-products-title">
				<h2 class="footer-col__title" id="footer-products-title"><?php esc_html_e( 'Products', 'quantivista' ); ?></h2>
				<?php wp_nav_menu( array( 'theme_location' => 'footer_products', 'container' => false, 'menu_class' => 'footer-menu', 'depth' => 1, 'fallback_cb' => false ) ); ?>
			</nav>

			<nav class="footer-col" aria-labelledby="footer-policies-title">
				<h2 class="footer-col__title" id="footer-policies-title"><?php esc_html_e( 'Policies', 'quantivista' ); ?></h2>
				<?php wp_nav_menu( array( 'theme_location' => 'footer_policies', 'container' => false, 'menu_class' => 'footer-menu', 'depth' => 1, 'fallback_cb' => false ) ); ?>
			</nav>

			<div class="footer-col footer-col--newsletter">
				<h2 class="footer-col__title" id="newsletter-title"><?php esc_html_e( 'Stay in the loop', 'quantivista' ); ?></h2>
				<p><?php esc_html_e( 'Product launches, engineering deep-dives and exclusive discounts. One email a month, no spam.', 'quantivista' ); ?></p>
				<form class="newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" aria-labelledby="newsletter-title">
					<input type="hidden" name="action" value="qv_newsletter">
					<?php wp_nonce_field( 'qv_newsletter', 'qv_newsletter_nonce' ); ?>
					<div class="hp-field" aria-hidden="true">
						<label for="qv-website"><?php esc_html_e( 'Leave this field empty', 'quantivista' ); ?></label>
						<input type="text" id="qv-website" name="website" tabindex="-1" autocomplete="off">
					</div>
					<label class="screen-reader-text" for="qv-newsletter-email"><?php esc_html_e( 'Email address', 'quantivista' ); ?></label>
					<div class="newsletter-form__row">
						<input type="email" id="qv-newsletter-email" name="email" placeholder="<?php esc_attr_e( 'you@company.com', 'quantivista' ); ?>" autocomplete="email" required>
						<button type="submit" class="btn btn--accent"><?php esc_html_e( 'Subscribe', 'quantivista' ); ?></button>
					</div>
					<?php if ( isset( $qv_messages[ $qv_newsletter ] ) ) : ?>
						<p class="newsletter-form__notice newsletter-form__notice--<?php echo esc_attr( $qv_newsletter ); ?>" role="status"><?php echo esc_html( $qv_messages[ $qv_newsletter ] ); ?></p>
					<?php endif; ?>
					<p class="newsletter-form__legal">
						<?php
						$qv_privacy = get_privacy_policy_url();
						printf(
							/* translators: %s: privacy policy link */
							esc_html__( 'By subscribing you agree to our %s.', 'quantivista' ),
							$qv_privacy ? '<a href="' . esc_url( $qv_privacy ) . '">' . esc_html__( 'Privacy Policy', 'quantivista' ) . '</a>' : esc_html__( 'Privacy Policy', 'quantivista' ) // phpcs:ignore WordPress.Security.EscapeOutput
						);
						?>
					</p>
				</form>
			</div>
		</div>

		<?php if ( is_active_sidebar( 'footer-extra' ) ) : ?>
			<div class="footer-extra"><?php dynamic_sidebar( 'footer-extra' ); ?></div>
		<?php endif; ?>

		<div class="footer-bottom">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'quantivista' ); ?></p>
			<p class="footer-bottom__contact">
				<a href="mailto:<?php echo esc_attr( antispambot( qv_mod( 'contact_email' ) ) ); ?>"><?php echo esc_html( antispambot( qv_mod( 'contact_email' ) ) ); ?></a>
				<span aria-hidden="true">&middot;</span>
				<span><?php esc_html_e( 'Secure payments', 'quantivista' ); ?> <?php qv_the_icon( 'lock', array( 'size' => 14 ) ); ?></span>
			</p>
		</div>
	</div>
</footer>

<div class="toast" id="qv-toast" role="status" aria-live="polite" hidden></div>
<?php wp_footer(); ?>
</body>
</html>
