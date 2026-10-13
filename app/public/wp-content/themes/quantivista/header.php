<?php
/**
 * Site header.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_has_wc     = class_exists( 'WooCommerce' );
$qv_account    = $qv_has_wc ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$qv_cart_count = ( $qv_has_wc && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'quantivista' ); ?></a>

<header class="site-header" id="site-header">
	<div class="container site-header__inner">
		<div class="site-branding">
			<?php qv_site_logo( 'header' ); ?>
		</div>

		<nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'quantivista' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'menu_id'        => 'primary-menu',
					'depth'          => 3,
					'fallback_cb'    => false,
					'walker'         => new QV_Nav_Walker(),
				)
			);
			?>
			<?php qv_language_switcher( 'lang-switch--mobile' ); ?>
			<div class="primary-nav__mobile-cta">
				<a class="btn btn--accent btn--block" href="<?php echo esc_url( qv_mod_url( 'header_cta_url' ) ); ?>"><?php echo esc_html( qv_mod( 'header_cta_label' ) ); ?></a>
			</div>
		</nav>

		<div class="header-actions">
			<?php qv_language_switcher( 'lang-switch--header' ); ?>
			<button type="button" class="icon-btn" data-search-toggle aria-controls="search-panel" aria-expanded="false">
				<?php qv_the_icon( 'search' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'quantivista' ); ?></span>
			</button>

			<a class="icon-btn" href="<?php echo esc_url( $qv_account ); ?>">
				<?php qv_the_icon( 'user' ); ?>
				<span class="screen-reader-text"><?php is_user_logged_in() ? esc_html_e( 'My account', 'quantivista' ) : esc_html_e( 'Log in or register', 'quantivista' ); ?></span>
			</a>

			<?php if ( $qv_has_wc ) : ?>
				<a class="icon-btn icon-btn--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
					<?php qv_the_icon( 'bag' ); ?>
					<span class="cart-count<?php echo $qv_cart_count ? '' : ' is-empty'; ?>" aria-hidden="true"><?php echo (int) $qv_cart_count; ?></span>
					<span class="screen-reader-text cart-count-label">
						<?php
						/* translators: %d: number of items */
						printf( esc_html( _n( 'Cart, %d item', 'Cart, %d items', $qv_cart_count, 'quantivista' ) ), (int) $qv_cart_count );
						?>
					</span>
				</a>
			<?php endif; ?>

			<a class="btn btn--accent btn--sm header-cta" href="<?php echo esc_url( qv_mod_url( 'header_cta_url' ) ); ?>"><?php echo esc_html( qv_mod( 'header_cta_label' ) ); ?></a>

			<button type="button" class="icon-btn nav-toggle" data-nav-toggle aria-controls="primary-nav" aria-expanded="false">
				<span class="nav-toggle__open"><?php qv_the_icon( 'menu' ); ?></span>
				<span class="nav-toggle__close"><?php qv_the_icon( 'close' ); ?></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'quantivista' ); ?></span>
			</button>
		</div>
	</div>

	<div class="search-panel" id="search-panel" hidden>
		<div class="container">
			<?php get_search_form(); ?>
		</div>
	</div>
</header>

<main id="main" class="site-main" tabindex="-1">
