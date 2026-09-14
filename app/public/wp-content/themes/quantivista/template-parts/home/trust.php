<?php
/**
 * Trust bar: key stats + client logos.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_logos = apply_filters(
	'qv_client_logos',
	array(
		array( 'Northwind Labs', '<circle cx="12" cy="12" r="9"/><path d="M12 3v18"/>' ),
		array( 'Helix Health', '<path d="M12 2 21 7v10l-9 5-9-5V7z"/>' ),
		array( 'Orbital', '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/>' ),
		array( 'Cobalt Finance', '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M4 12h16"/>' ),
		array( 'Lumen Retail', '<path d="M12 3 21 20H3z"/>' ),
		array( 'Vertexa', '<path d="M3 5l9 15 9-15"/>' ),
	)
);
?>
<section class="trust" aria-label="<?php esc_attr_e( 'Company highlights', 'quantivista' ); ?>">
	<div class="container">
		<ul class="stats" data-reveal>
			<?php for ( $qv_i = 1; $qv_i <= 4; $qv_i++ ) : ?>
				<li class="stat">
					<span class="stat__value" data-count><?php echo esc_html( qv_mod( "stat_{$qv_i}_value" ) ); ?></span>
					<span class="stat__label"><?php echo esc_html( qv_mod( "stat_{$qv_i}_label" ) ); ?></span>
				</li>
			<?php endfor; ?>
		</ul>

		<div class="logos" data-reveal>
			<p class="logos__label"><?php esc_html_e( 'Trusted by product teams at', 'quantivista' ); ?></p>
			<?php foreach ( $qv_logos as $qv_logo ) : ?>
				<span class="logo-mark">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" aria-hidden="true" focusable="false"><?php echo $qv_logo[1]; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></svg>
					<?php echo esc_html( $qv_logo[0] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
</section>
