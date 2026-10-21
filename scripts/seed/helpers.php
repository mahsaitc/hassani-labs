<?php
/**
 * Seed helpers: idempotent upserts for posts, terms and generated images.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

const QVS_KEY = '_qv_seed_key';

function qvs_find_by_key( $key, $post_type = 'any', $status = 'any' ) {
	$found = get_posts(
		array(
			'post_type'        => $post_type,
			'post_status'      => $status,
			'meta_key'         => QVS_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * Insert or update a post identified by a seed key. Extra meta via $meta.
 */
function qvs_upsert_post( $key, array $postarr, array $meta = array() ) {
	$id = qvs_find_by_key( $key, $postarr['post_type'] ?? 'post' );
	$postarr = array_merge( array( 'post_status' => 'publish' ), $postarr );
	if ( $id ) {
		$postarr['ID'] = $id;
		wp_update_post( wp_slash( $postarr ) );
	} else {
		$id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( "Could not create {$key}: " . $id->get_error_message() );
			return 0;
		}
		update_post_meta( $id, QVS_KEY, $key );
	}
	foreach ( $meta as $meta_key => $value ) {
		update_post_meta( $id, $meta_key, $value );
		// ACF field reference so the field UI binds to seeded values.
		if ( str_starts_with( $meta_key, 'qv_' ) ) {
			update_post_meta( $id, '_' . $meta_key, 'field_' . $meta_key );
		}
	}
	return (int) $id;
}

/**
 * Generate an image with a GD callback and register it as an attachment.
 */
function qvs_image( $key, $filename, callable $draw, $alt ) {
	$existing = qvs_find_by_key( $key, 'attachment', 'inherit' );
	if ( $existing && file_exists( (string) get_attached_file( $existing ) ) ) {
		return $existing;
	}

	$uploads = wp_upload_dir();
	$path    = trailingslashit( $uploads['basedir'] ) . 'qv-seed/' . $filename;
	qvimg_save( $draw(), $path );

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $filename )['type'],
			'post_title'     => $alt,
			'post_status'    => 'inherit',
			'guid'           => trailingslashit( $uploads['baseurl'] ) . 'qv-seed/' . $filename,
		),
		$path
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, QVS_KEY, $key );
	return (int) $id;
}

/**
 * Insert or update a term by slug.
 */
function qvs_term( $name, $taxonomy, $slug, array $args = array(), array $meta = array() ) {
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	$args     = array_merge( array( 'slug' => $slug ), $args );
	if ( $existing ) {
		wp_update_term( $existing->term_id, $taxonomy, array_merge( array( 'name' => $name ), $args ) );
		$term_id = (int) $existing->term_id;
	} else {
		$result = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( "Term {$slug}: " . $result->get_error_message() );
			return 0;
		}
		$term_id = (int) $result['term_id'];
	}
	foreach ( $meta as $meta_key => $value ) {
		update_term_meta( $term_id, $meta_key, $value );
		if ( str_starts_with( $meta_key, 'qv_' ) ) {
			update_term_meta( $term_id, '_' . $meta_key, 'field_' . $meta_key );
		}
	}
	return $term_id;
}

/**
 * Wrap HTML in a Gutenberg "freeform" (classic) block-safe string.
 */
function qvs_html( $html ) {
	return trim( preg_replace( '/^\t+/m', '', $html ) );
}

function qvs_update_option_merge( $option, array $values ) {
	$current = get_option( $option, array() );
	update_option( $option, array_merge( is_array( $current ) ? $current : array(), $values ) );
}

/**
 * Create (or empty) a navigation menu by name.
 */
function qvs_menu( $name ) {
	$menu = wp_get_nav_menu_object( $name );
	$id   = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	foreach ( (array) wp_get_nav_menu_items( $id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	return $id;
}

function qvs_menu_item( $menu_id, array $args, $parent = 0 ) {
	return wp_update_nav_menu_item(
		$menu_id,
		0,
		array_merge(
			array(
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			),
			$args
		)
	);
}

/**
 * Article body blocks (p, h2, ul, quote, code) to HTML. Inline **bold**
 * and `code` are kept.
 */
function qvs_blocks_html( array $blocks ) {
	$inline = function ( $text ) {
		$text = esc_html( $text );
		$text = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text );
		return preg_replace( '/`([^`]+)`/', '<code>$1</code>', $text );
	};
	$html = '';
	foreach ( $blocks as $b ) {
		switch ( $b['type'] ) {
			case 'h2':
				$html .= '<h2>' . $inline( $b['text'] ) . "</h2>\n";
				break;
			case 'ul':
				$html .= '<ul>' . implode( '', array_map( fn( $i ) => '<li>' . $inline( $i ) . '</li>', $b['items'] ) ) . "</ul>\n";
				break;
			case 'quote':
				$html .= '<blockquote><p>' . $inline( $b['text'] ) . "</p></blockquote>\n";
				break;
			case 'code':
				$html .= '<pre><code class="language-' . esc_attr( $b['lang'] ?? 'text' ) . '">' . esc_html( $b['code'] ) . "</code></pre>\n";
				break;
			default:
				$html .= '<p>' . $inline( $b['text'] ) . "</p>\n";
		}
	}
	return $html;
}

/* ------------------------------------------------------------------------
 * Product catalogue (data/catalog.php): categories, Products pages, menus
 * --------------------------------------------------------------------- */

/**
 * Create or update the product categories; returns slug => term id.
 * Removes the sample categories of the original Quantivista store.
 */
function qvs_catalog_terms() {
	foreach ( array( 'web-software', 'crm-tools', 'automation-tools', 'analytics-bi', 'ecommerce-tools', 'business-productivity', 'health-fitness', 'app-starter-kits', 'website-design', 'landing-pages', 'corporate-websites', 'ecommerce-stores' ) as $slug ) {
		$old = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $old ) {
			wp_delete_term( $old->term_id, 'product_cat' );
		}
	}

	$ids   = array();
	$order = 0;
	foreach ( require __DIR__ . '/data/catalog.php' as $group_slug => $group ) {
		$ids[ $group_slug ] = qvs_term(
			$group['name'],
			'product_cat',
			$group_slug,
			array( 'description' => $group['desc'], 'parent' => 0 ),
			array(
				'order'       => $order++,
				'qv_cat_icon' => $group['icon'],
				'qv_name_fa'  => $group['name_fa'],
				'qv_desc_fa'  => $group['desc_fa'],
			)
		);
		$i = 0;
		foreach ( $group['cats'] as $slug => $cat ) {
			$ids[ $slug ] = qvs_term(
				$cat[0],
				'product_cat',
				$slug,
				array( 'description' => $cat[3], 'parent' => $ids[ $group_slug ] ),
				array(
					'order'        => $i++,
					'qv_cat_icon'  => $cat[2],
					'qv_name_fa'   => $cat[1],
					'qv_desc_fa'   => $cat[4],
					'qv_cat_pages' => $cat[5] ?? '',
				)
			);
		}
	}
	return $ids;
}

/**
 * Category ids for a product: the given categories plus their groups.
 */
function qvs_catalog_ids( array $ids, array $slugs ) {
	$out = array();
	foreach ( $slugs as $slug ) {
		if ( empty( $ids[ $slug ] ) ) {
			continue;
		}
		$out[] = $ids[ $slug ];
		$term  = get_term( $ids[ $slug ], 'product_cat' );
		if ( $term && $term->parent ) {
			$out[] = (int) $term->parent;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * The "Products" catalogue pages: English /products/ and Persian
 * /fa/products-fa/ (linked when Polylang has Persian). Returns array( en, fa ).
 */
function qvs_catalog_pages() {
	$en = qvs_upsert_post(
		'page-products',
		array(
			'post_type'    => 'page',
			'post_title'   => 'Products',
			'post_name'    => 'products',
			'post_content' => '',
			'post_excerpt' => 'Software and websites by platform and by field. Pick a category, or ask me for custom software.',
			'menu_order'   => 2,
		)
	);
	$fa = qvs_upsert_post(
		'page-products-fa',
		array(
			'post_type'    => 'page',
			'post_title'   => 'محصولات',
			'post_name'    => 'products-fa',
			'post_content' => '',
			'post_excerpt' => 'نرم‌افزار و وب‌سایت بر اساس پلتفرم و حوزه کاری. دسته مورد نظرتان را انتخاب کنید یا نرم‌افزار اختصاصی سفارش دهید.',
			'menu_order'   => 2,
		)
	);
	foreach ( array( $en, $fa ) as $id ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/products.php' );
	}
	if ( $en && $fa && function_exists( 'pll_set_post_language' ) && in_array( 'fa', (array) pll_languages_list(), true ) ) {
		pll_set_post_language( $en, 'en' );
		pll_set_post_language( $fa, 'fa' );
		pll_save_post_translations( array( 'en' => $en, 'fa' => $fa ) );
	}
	return array( $en, $fa );
}

/**
 * The product sales pages, English and Persian, linked as translations:
 * SafeJot (Android app, under Mobile apps) and Aesthetic Clinic Suite
 * (website, under Websites and Medical). Returns the SafeJot pair; all pairs
 * are in qvs_all_app_pages().
 */
function qvs_app_pages() {
	return qvs_all_app_pages()['safejot'];
}

/**
 * Creates or updates every product sales page pair; returns slug => array( en id, fa id ).
 */
function qvs_all_app_pages() {
	$pages = array(
		'safejot'          => array(
			'en' => array( 'page-safejot', 'SafeJot for Android', 'safejot', 'page-templates/app-safejot-en.php', 'Lock private notes and messages with your own password: AES-256 encryption, English and Persian, fully offline, nothing stored.' ),
			'fa' => array( 'page-safejot-fa', 'اپلیکیشن SafeJot برای اندروید', 'safejot-fa', 'page-templates/app-safejot.php', 'یادداشت‌ها و پیام‌های خصوصی را با رمز عبور خودتان قفل کنید: رمزنگاری AES-256، فارسی و انگلیسی، کاملاً آفلاین و بدون ذخیره هیچ متنی.' ),
		),
		'aesthetic-clinic' => array(
			'en' => array( 'page-aesthetic-clinic', 'Aesthetic Clinic Suite: website and panel for aesthetic clinics', 'aesthetic-clinic-suite', 'page-templates/app-aesthetic-clinic-en.php', 'A bilingual website, online booking with deposits, treatment previews on a photo, patient records, SMS reminders and reports for aesthetic, injection and laser clinics, installed on the clinic\'s own server.' ),
			'fa' => array( 'page-aesthetic-clinic-fa', 'وب‌سایت و پنل مدیریت کلینیک زیبایی', 'aesthetic-clinic-suite-fa', 'page-templates/app-aesthetic-clinic.php', 'سایت دوزبانه، نوبت‌دهی آنلاین با بیعانه، پیش‌نمایش درمان روی عکس، پرونده الکترونیک، پیامک یادآوری و گزارش‌های مدیریتی برای کلینیک‌های زیبایی، تزریق و لیزر؛ نصب روی سرور خود کلینیک.' ),
		),
	);
	$out = array();
	foreach ( $pages as $slug => $langs ) {
		$ids = array();
		foreach ( $langs as $lang => $p ) {
			$ids[ $lang ] = qvs_upsert_post(
				$p[0],
				array(
					'post_type'    => 'page',
					'post_title'   => $p[1],
					'post_name'    => $p[2],
					'post_content' => '',
					'post_excerpt' => $p[4],
				)
			);
			update_post_meta( $ids[ $lang ], '_wp_page_template', $p[3] );
		}
		if ( $ids['en'] && $ids['fa'] && function_exists( 'pll_set_post_language' ) && in_array( 'fa', (array) pll_languages_list(), true ) ) {
			pll_set_post_language( $ids['en'], 'en' );
			pll_set_post_language( $ids['fa'], 'fa' );
			pll_save_post_translations( $ids );
		}
		$out[ $slug ] = array( $ids['en'], $ids['fa'] );
	}
	return $out;
}

/**
 * Append the "Products" mega menu (groups as columns, categories under
 * them) to a menu. English links go to the store categories, Persian links
 * to the category cards on the Persian Products page ("#…" links with the
 * class qv-catalog-link, completed by the theme in the current language).
 */
function qvs_catalog_menu( $menu_id, $lang, $page_id ) {
	$fa  = 'fa' === $lang;
	$top = qvs_menu_item(
		$menu_id,
		array(
			'menu-item-type'      => $page_id ? 'post_type' : 'custom',
			'menu-item-object'    => $page_id ? 'page' : 'custom',
			'menu-item-object-id' => (int) $page_id,
			'menu-item-url'       => $page_id ? '' : home_url( '/shop/' ),
			'menu-item-title'     => $fa ? 'محصولات' : 'Products',
			'menu-item-classes'   => 'mega-menu mega-menu--catalog',
		)
	);
	foreach ( require __DIR__ . '/data/catalog.php' as $group_slug => $group ) {
		$col = qvs_menu_item(
			$menu_id,
			array(
				'menu-item-type'        => 'custom',
				'menu-item-url'         => '#' . $group_slug,
				'menu-item-classes'     => 'qv-catalog-link',
				'menu-item-title'       => $fa ? $group['name_fa'] : $group['name'],
				'menu-item-description' => $fa ? $group['desc_fa'] : $group['desc'],
			),
			$top
		);
		foreach ( $group['cats'] as $slug => $cat ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			$item = array(
				'menu-item-title'   => $fa ? $cat[1] : $cat[0],
				'menu-item-classes' => 'qv-icon-' . $cat[2],
			);
			if ( $fa || ! $term ) {
				$item = array_merge(
					$item,
					array(
						'menu-item-type'    => 'custom',
						'menu-item-url'     => '#cat-' . $slug,
						'menu-item-classes' => 'qv-icon-' . $cat[2] . ' qv-catalog-link',
					)
				);
			} else {
				$item += array(
					'menu-item-type'      => 'taxonomy',
					'menu-item-object'    => 'product_cat',
					'menu-item-object-id' => $term->term_id,
				);
			}
			qvs_menu_item( $menu_id, $item, $col );
		}
	}
	return $top;
}

/**
 * Replace the "Products" item (and everything under it) of an existing menu
 * with the catalogue mega menu, keeping its place in the menu.
 */
function qvs_catalog_replace_menu( $menu_id, $lang, $page_id ) {
	$items    = (array) wp_get_nav_menu_items( $menu_id );
	$children = array();
	foreach ( $items as $item ) {
		$children[ (int) $item->menu_item_parent ][] = (int) $item->ID;
	}
	$before = array();
	$after  = array();
	$found  = false;
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent ) {
			continue;
		}
		if ( ! $found && in_array( $item->title, array( 'Products', 'محصولات' ), true ) ) {
			$found = true;
			$drop  = array( (int) $item->ID );
			while ( $drop ) {
				$id = array_pop( $drop );
				array_push( $drop, ...( $children[ $id ] ?? array() ) );
				wp_delete_post( $id, true );
			}
			continue;
		}
		$found ? ( $after[] = $item ) : ( $before[] = $item );
	}

	// New items get rising ids in tree order.
	$top   = qvs_catalog_menu( $menu_id, $lang, $page_id );
	$added = array_map( 'intval', wp_list_pluck( (array) wp_get_nav_menu_items( $menu_id ), 'ID' ) );
	$added = array_filter( $added, fn( $id ) => $id >= $top );
	sort( $added );

	// Re-number: items before, the new Products tree, items after (each with its sub-items).
	$tree = function ( $item ) use ( &$tree, $children ) {
		$out = array( (int) $item->ID );
		foreach ( $children[ (int) $item->ID ] ?? array() as $child ) {
			$out = array_merge( $out, $tree( (object) array( 'ID' => $child ) ) );
		}
		return $out;
	};
	$order = array();
	foreach ( $before as $item ) {
		$order = array_merge( $order, $tree( $item ) );
	}
	$order = array_merge( $order, $added );
	foreach ( $after as $item ) {
		$order = array_merge( $order, $tree( $item ) );
	}
	foreach ( $order as $position => $id ) {
		wp_update_post( array( 'ID' => (int) $id, 'menu_order' => $position + 1 ) );
	}
}
