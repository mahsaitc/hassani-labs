<?php
/**
 * Trust bar: key stats + client logos (only when real ones are added).
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

// Client logos: none until real clients agree to be listed. Add them with
// the qv_client_logos filter as array( name, SVG inner markup ).
$qv_logos = apply_filters( 'qv_client_logos', array() );
?>
<section class="trust" aria-label="<?php esc_attr_e( 'Company highlights', 'quantivista' ); ?>">
	<div class="container">
		<ul class="stats" data-reveal>
			<?php for ( $qv_i = 1; $qv_i <= 4; $qv_i++ ) : ?>
				<?php
				$qv_value = (string) qv_mod( "stat_{$qv_i}_value" );
				if ( '' === $qv_value ) {
					continue; // An empty stat in the Customizer is left out.
				}
				$qv_number = (bool) preg_match( '/^[\d.,]/', $qv_value );
				?>
				<li class="stat">
					<span class="stat__value<?php echo $qv_number ? '' : ' stat__value--text'; ?>"<?php echo $qv_number ? ' data-count' : ''; ?>><?php echo esc_html( $qv_value ); ?></span>
					<span class="stat__label"><?php echo esc_html( qv_mod( "stat_{$qv_i}_label" ) ); ?></span>
				</li>
			<?php endfor; ?>
		</ul>

		<?php if ( $qv_logos ) : ?>
		<div class="logos" data-reveal>
			<p class="logos__label"><?php esc_html_e( 'Trusted by product teams at', 'quantivista' ); ?></p>
			<?php foreach ( $qv_logos as $qv_logo ) : ?>
				<span class="logo-mark">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" aria-hidden="true" focusable="false"><?php echo $qv_logo[1]; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></svg>
					<?php echo esc_html( $qv_logo[0] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
