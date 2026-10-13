<?php
/**
 * Search engine basics without an SEO plugin: meta description, canonical
 * for archives, Open Graph / X cards, JSON-LD (organisation, founder,
 * website, breadcrumbs, articles, the clinic software) and robots rules.
 *
 * When Yoast SEO, Rank Math, AIOSEO or SEOPress is active, only the robots
 * and sitemap rules here stay on; the plugin prints the head tags.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

function qv_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Plain text, one line, cut at about 160 characters on a word boundary.
 */
function qv_seo_clip( $text, $max = 160 ) {
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( (string) $text ) ) ) );
	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}
	$cut   = mb_substr( $text, 0, $max );
	$space = mb_strrpos( $cut, ' ' );
	return rtrim( $space > 80 ? mb_substr( $cut, 0, $space ) : $cut, " ,.;:،؛" ) . '…';
}

/**
 * Description of the current page.
 */
function qv_seo_description() {
	$text = '';
	if ( is_front_page() || is_home() ) {
		$page = (int) get_option( is_front_page() ? 'page_on_front' : 'page_for_posts' );
		$page = $page && function_exists( 'pll_get_post' ) ? (int) ( pll_get_post( $page ) ?: $page ) : $page;
		$text = $page ? get_post_field( 'post_excerpt', $page ) : '';
		if ( ! $text ) {
			$text = qv_mod( 'founder_intro' );
		}
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$text = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
		if ( 'product' === $post->post_type && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post->ID );
			$text    = $product && $product->get_short_description() ? $product->get_short_description() : $text;
		}
	} elseif ( is_tax( 'product_cat' ) && function_exists( 'qv_cat_text' ) ) {
		$text = qv_cat_text( get_queried_object(), 'description' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	} elseif ( is_post_type_archive( 'qv_project' ) ) {
		$text = __( 'Case studies: software for clinics and small businesses, from the problem to the result.', 'quantivista' );
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$text = __( 'Clinic software, web applications and website packages — built, documented and supported by Hassani Labs.', 'quantivista' );
	}
	return qv_seo_clip( $text );
}

/**
 * Share image: the featured image, else the site's default picture.
 */
function qv_seo_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( $src ) {
			return array( $src[0], (int) $src[1], (int) $src[2] );
		}
	}
	if ( is_tax( 'product_cat' ) ) {
		$thumb = (int) get_term_meta( get_queried_object_id(), 'thumbnail_id', true );
		$src   = $thumb ? wp_get_attachment_image_src( $thumb, 'large' ) : false;
		if ( $src ) {
			return array( $src[0], (int) $src[1], (int) $src[2] );
		}
	}
	return array( QV_URI . '/assets/img/og-default.jpg', 1200, 630 );
}

/**
 * Canonical URL of the current page (archives included), or ''.
 */
function qv_seo_url() {
	if ( is_singular() ) {
		return (string) wp_get_canonical_url();
	}
	$url = '';
	if ( is_front_page() ) {
		$url = qv_home_url();
	} elseif ( is_home() ) {
		$url = get_permalink( (int) get_option( 'page_for_posts' ) );
		$url = function_exists( 'pll_get_post' ) && pll_get_post( (int) get_option( 'page_for_posts' ) ) ? get_permalink( pll_get_post( (int) get_option( 'page_for_posts' ) ) ) : $url;
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		$url  = is_wp_error( $link ) ? '' : $link;
	} elseif ( is_post_type_archive() ) {
		$type = get_query_var( 'post_type' );
		$type = is_array( $type ) ? (string) reset( $type ) : (string) $type;
		$url  = $type ? (string) get_post_type_archive_link( $type ) : '';
	}
	$paged = (int) get_query_var( 'paged' );
	if ( $url && $paged > 1 ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
	}
	return (string) $url;
}

/**
 * Profiles set in the Customizer (Hassani Labs Options > Social Profiles).
 */
function qv_seo_same_as() {
	$urls = array();
	foreach ( array_diff( array_keys( qv_social_networks() ), array( 'whatsapp' ) ) as $network ) {
		$url = (string) qv_mod( 'social_' . $network );
		if ( $url && str_starts_with( $url, 'http' ) ) {
			$urls[] = $url;
		}
	}
	return $urls;
}

add_action(
	'wp_head',
	function () {
		if ( qv_seo_plugin_active() || is_404() ) {
			return;
		}
		$description = qv_seo_description();
		$url         = qv_seo_url();
		$image       = qv_seo_image();
		$fa          = is_rtl();

		if ( $description ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		}
		if ( $url && ! is_singular() ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		}

		$type = is_singular( 'post' ) ? 'article' : ( is_singular( 'product' ) ? 'product' : 'website' );
		$tags = array(
			'og:type'        => $type,
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:title'       => wp_get_document_title(),
			'og:description' => $description,
			'og:url'         => $url,
			'og:locale'      => $fa ? 'fa_IR' : 'en_US',
			'og:image'       => $image[0],
			'og:image:width' => $image[1] ? (string) $image[1] : '',
			'og:image:height' => $image[2] ? (string) $image[2] : '',
		);
		if ( 'article' === $type ) {
			$tags['article:published_time'] = get_the_date( 'c' );
			$tags['article:modified_time']  = get_the_modified_date( 'c' );
		}
		foreach ( $tags as $property => $content ) {
			if ( '' !== (string) $content ) {
				printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), 'og:url' === $property || 'og:image' === $property ? esc_url( $content ) : esc_attr( $content ) );
			}
		}
		printf( '<meta property="og:locale:alternate" content="%s">' . "\n", $fa ? 'en_US' : 'fa_IR' );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

		$graph = qv_seo_graph( $description, $url, $image );
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	},
	1
);

/**
 * JSON-LD graph for the current page.
 */
function qv_seo_graph( $description, $url, $image ) {
	$home     = qv_home_url();
	$lang     = is_rtl() ? 'fa-IR' : 'en';
	$about    = qv_page_by_path( 'about' );
	$org_id   = home_url( '/#organization' );
	$person   = home_url( '/#founder' );
	$logo     = has_custom_logo() ? wp_get_attachment_image_url( (int) get_theme_mod( 'custom_logo' ), 'full' ) : QV_URI . '/assets/img/logo-mark-192.png';
	$same_as  = qv_seo_same_as();
	$graph    = array(
		array(
			'@type'    => 'Organization',
			'@id'      => $org_id,
			'name'     => get_bloginfo( 'name' ),
			'url'      => $home,
			'logo'     => $logo,
			'founder'  => array( '@id' => $person ),
			'sameAs'   => $same_as,
		),
		array(
			'@type'    => 'Person',
			'@id'      => $person,
			'name'     => qv_mod( 'founder_name' ),
			'jobTitle' => qv_mod( 'founder_role' ),
			'url'      => $about ? get_permalink( $about ) : $home,
			'image'    => QV_URI . '/assets/img/founder-portrait.webp',
			'worksFor' => array( '@id' => $org_id ),
			'sameAs'   => $same_as,
		),
		array(
			'@type'      => 'WebSite',
			'@id'        => $home . '#website',
			'name'       => get_bloginfo( 'name' ),
			'url'        => $home,
			'inLanguage' => $lang,
			'publisher'  => array( '@id' => $org_id ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'            => 'BlogPosting',
			'headline'         => wp_strip_all_tags( get_the_title() ),
			'description'      => $description,
			'image'            => $image[0],
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'inLanguage'       => $lang,
			'author'           => array( '@id' => $person ),
			'publisher'        => array( '@id' => $org_id ),
			'mainEntityOfPage' => $url,
		);
	}

	if ( is_page_template( array( 'page-templates/clinic-software.php', 'page-templates/clinic-software-en.php' ) ) ) {
		$graph[] = array(
			'@type'               => 'SoftwareApplication',
			'name'                => wp_strip_all_tags( get_the_title() ),
			'description'         => $description,
			'applicationCategory' => 'HealthApplication',
			'operatingSystem'     => 'Web',
			'inLanguage'          => array( 'fa-IR', 'en' ),
			'url'                 => $url,
			'image'               => $image[0],
			'author'              => array( '@id' => $person ),
			'publisher'           => array( '@id' => $org_id ),
		);
	}

	$crumbs = qv_seo_breadcrumbs();
	if ( count( $crumbs ) > 1 ) {
		$items = array();
		foreach ( array_values( $crumbs ) as $i => $crumb ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $crumb[0],
				'item'     => $crumb[1],
			);
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}
	return $graph;
}

/**
 * Breadcrumb trail as array( name, url ) pairs, home first.
 */
function qv_seo_breadcrumbs() {
	if ( is_front_page() ) {
		return array();
	}
	$trail = array( array( __( 'Home', 'quantivista' ), qv_home_url() ) );
	if ( is_singular( 'post' ) ) {
		$blog = (int) get_option( 'page_for_posts' );
		$blog = $blog && function_exists( 'pll_get_post' ) ? (int) ( pll_get_post( $blog ) ?: $blog ) : $blog;
		if ( $blog ) {
			$trail[] = array( get_the_title( $blog ), get_permalink( $blog ) );
		}
	} elseif ( is_singular( 'qv_project' ) ) {
		$trail[] = array( __( 'Work', 'quantivista' ), get_post_type_archive_link( 'qv_project' ) );
	} elseif ( is_singular( 'product' ) || is_tax( 'product_cat' ) ) {
		$trail[] = array( __( 'Products', 'quantivista' ), function_exists( 'qv_products_page_url' ) ? qv_products_page_url() : qv_home_url() );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$trail[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
	}
	if ( is_singular() ) {
		$trail[] = array( wp_strip_all_tags( get_the_title() ), (string) wp_get_canonical_url() );
	} elseif ( is_tax( 'product_cat' ) ) {
		$trail[] = array( qv_cat_text( get_queried_object() ), qv_seo_url() );
	} else {
		$url = qv_seo_url();
		if ( $url ) {
			$trail[] = array( wp_strip_all_tags( wp_get_document_title() ), $url );
		}
	}
	return $trail;
}

/**
 * Robots: keep test copies (Local, staging), search results, empty store
 * categories and copies of pages (hidden clinic plans) out of search engines.
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		$noindex = 'production' !== wp_get_environment_type()
			|| is_search()
			|| ( is_tax( 'product_cat' ) && function_exists( 'qv_cat_product_count' ) && ! qv_cat_product_count( get_queried_object() ) && ! qv_cat_pages( get_queried_object() ) )
			|| ( is_singular( 'product' ) && has_term( 'exclude-from-catalog', 'product_visibility', get_queried_object_id() ) )
			|| is_page( array( 'wishlist' ) );
		if ( $noindex ) {
			unset( $robots['index'], $robots['max-image-preview'] );
			$robots['noindex'] = true;
			$robots['follow']  = true;
		} else {
			$robots['max-image-preview'] = 'large';
		}
		return $robots;
	},
	20
);

/**
 * WordPress sitemap (wp-sitemap.xml): no user list (it reveals login
 * names), no hidden products.
 */
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'product' === $post_type && taxonomy_exists( 'product_visibility' ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'slug',
					'terms'    => array( 'exclude-from-catalog' ),
					'operator' => 'NOT IN',
				),
			);
		}
		return $args;
	},
	10,
	2
);
