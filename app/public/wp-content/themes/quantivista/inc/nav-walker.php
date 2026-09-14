<?php
/**
 * Primary navigation walker with accessible dropdowns and a mega menu.
 *
 * Usage in the menu editor: add the CSS class "mega-menu" to a top-level
 * item. Its children become columns; grandchildren become column links.
 * Menu item descriptions are shown under column headings.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

class QV_Nav_Walker extends Walker_Nav_Menu {

	/** Whether the top-level item currently being walked is a mega menu. */
	private $in_mega = false;

	/** Top-level item being walked (for the mega promo link). */
	private $top_item = null;

	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( 0 === $depth ) {
			$this->in_mega  = in_array( 'mega-menu', (array) $element->classes, true );
			$this->top_item = $element;
		}
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth && $this->in_mega ) {
			$output .= '<div class="mega-panel"><div class="mega-panel__inner"><ul class="sub-menu mega-panel__cols">';
			return;
		}
		$output .= '<ul class="sub-menu">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth && $this->in_mega ) {
			$output .= '</ul>' . $this->mega_promo() . '</div></div>';
			return;
		}
		$output .= '</ul>';
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item         = $data_object;
		$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[]    = 'menu-item-' . $item->ID;
		$has_children = in_array( 'menu-item-has-children', $classes, true );

		if ( $this->in_mega && 1 === $depth ) {
			$classes[] = 'mega-col';
		}

		$class_names = implode( ' ', array_filter( apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) ) );
		$output     .= '<li class="' . esc_attr( $class_names ) . '">';

		$atts = array(
			'href'         => ! empty( $item->url ) ? $item->url : '',
			'target'       => ! empty( $item->target ) ? $item->target : '',
			'rel'          => ! empty( $item->xfn ) ? $item->xfn : ( '_blank' === $item->target ? 'noopener' : '' ),
			'title'        => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'aria-current' => $item->current ? 'page' : '',
			'class'        => 0 === $depth ? 'menu-link menu-link--top' : 'menu-link',
		);
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attr_html = '';
		foreach ( $atts as $name => $value ) {
			if ( '' !== $value && false !== $value ) {
				$value      = 'href' === $name ? esc_url( $value ) : esc_attr( $value );
				$attr_html .= ' ' . $name . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$item_output  = $args->before ?? '';
		$item_output .= '<a' . $attr_html . '>';
		$item_output .= '<span class="menu-link__label">' . $title . '</span>';
		if ( $this->in_mega && 1 === $depth && ! empty( $item->description ) ) {
			$item_output .= '<span class="menu-link__desc">' . esc_html( $item->description ) . '</span>';
		}
		$item_output .= '</a>';

		// Disclosure button: keyboard/touch users open submenus without hover.
		if ( $has_children && 0 === $depth ) {
			$item_output .= sprintf(
				'<button type="button" class="submenu-toggle" aria-expanded="false" aria-label="%s">%s</button>',
				/* translators: %s: menu item label */
				esc_attr( sprintf( __( 'Show %s submenu', 'quantivista' ), wp_strip_all_tags( $title ) ) ),
				qv_icon( 'chevron-down', array( 'size' => 16 ) )
			);
		}
		$item_output .= $args->after ?? '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Promo card at the end of the mega panel.
	 */
	private function mega_promo() {
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		return '<aside class="mega-promo">'
			. '<p class="eyebrow">' . esc_html__( 'New release', 'quantivista' ) . '</p>'
			. '<p class="mega-promo__title">' . esc_html( qv_mod( 'mega_promo_title' ) ) . '</p>'
			. '<p class="mega-promo__text">' . esc_html( qv_mod( 'mega_promo_text' ) ) . '</p>'
			. '<a class="btn btn--accent btn--sm" href="' . esc_url( $shop_url ) . '">' . esc_html__( 'Browse all products', 'quantivista' ) . qv_icon( 'arrow-right', array( 'size' => 16 ) ) . '</a>'
			. '</aside>';
	}
}
