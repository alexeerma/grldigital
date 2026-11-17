<?php
/**
 * GRL Digital theme setup.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

define( 'GRL_VERSION', '1.5.2' );

require get_template_directory() . '/inc/theme-options.php';
require get_template_directory() . '/inc/projects.php';
require get_template_directory() . '/inc/seed.php';
require get_template_directory() . '/inc/seo.php';

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'grldigital', get_template_directory() . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'custom-logo' );
	}
);

// The `projekt` post type is registered on init (inc/projects.php); refresh its rewrite rules once when the theme is activated.
// after_switch_theme runs on init priority 99 (check_theme_switched), after the post type exists.
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

add_action(
	'wp_enqueue_scripts',
	function () {
		// Version must stay null: Google Fonts rejects or mis-caches unknown query args, and the CSS is versioned by its URL.
		wp_enqueue_style( 'grl-fonts', 'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Mulish:wght@400;700&family=Playfair+Display:wght@900&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- remote Google Fonts CSS, see above.
		wp_enqueue_style( 'grl-main', get_template_directory_uri() . '/assets/css/main.css', array( 'grl-fonts' ), GRL_VERSION );
		// `strategy` needs WP 6.3+ (the theme's minimum). Older cores read the array as a truthy $in_footer: footer, no defer.
		wp_enqueue_script(
			'grl-main',
			get_template_directory_uri() . '/assets/js/main.js',
			array(),
			GRL_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_enqueue_style( 'grl-contact', get_template_directory_uri() . '/assets/css/contact.css', array( 'grl-main' ), GRL_VERSION );
		wp_enqueue_script(
			'grl-contact',
			get_template_directory_uri() . '/assets/js/contact.js',
			array(),
			GRL_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_enqueue_style( 'grl-legal', get_template_directory_uri() . '/assets/css/legal.css', array( 'grl-main' ), GRL_VERSION );
		if ( is_page() ) {
			wp_enqueue_script(
				'grl-legal',
				get_template_directory_uri() . '/assets/js/legal.js',
				array(),
				GRL_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}
);

add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = array(
				'href' => 'https://fonts.gstatic.com',
				'crossorigin',
			);
		}
		return $urls;
	},
	10,
	2
);

/**
 * URL of a bundled theme image.
 *
 * @param string $file File name in assets/img.
 */
function grl_img( $file ) {
	return get_template_directory_uri() . '/assets/img/' . $file;
}

/**
 * A URL made safe for a CSS url('...') value inside a style attribute.
 *
 * The esc_url() output keeps ' ( ) which the HTML parser hands back to CSS as-is, so a crafted URL could
 * close the url() and inject declarations. Percent-encode them; the result still needs esc_attr().
 *
 * @param string $url URL.
 * @return string
 */
function grl_css_url( $url ) {
	return str_replace(
		array( "'", '"', '(', ')', '\\', ' ' ),
		array( '%27', '%22', '%28', '%29', '%5C', '%20' ),
		esc_url_raw( (string) $url )
	);
}

/**
 * Hero background: custom URL from options, or the bundled 1x/2x WebP files.
 *
 * @return array{1x:string,2x:string,custom:bool}
 */
function grl_hero_images() {
	$custom = grl_opt( 'hero', 'image' );
	if ( $custom ) {
		return array(
			'1x'     => $custom,
			'2x'     => $custom,
			'custom' => true,
		);
	}
	return array(
		'1x'     => grl_img( 'hero.webp' ),
		'2x'     => grl_img( 'hero-2x.webp' ),
		'custom' => false,
	);
}

// Preload the hero image (largest element on the front page).
add_action(
	'wp_head',
	function () {
		if ( ! is_front_page() ) {
			return;
		}
		$hero = grl_hero_images();
		if ( $hero['custom'] ) {
			echo '<link rel="preload" as="image" href="' . esc_url( $hero['1x'] ) . '" fetchpriority="high">' . "\n";
			return;
		}
		echo '<link rel="preload" as="image" href="' . esc_url( $hero['1x'] ) . '" imagesrcset="' . esc_url( $hero['1x'] ) . ' 1x, ' . esc_url( $hero['2x'] ) . ' 2x" type="image/webp" fetchpriority="high">' . "\n";
	},
	2
);

/**
 * Anchor link to a front page section, works from any page.
 *
 * @param string $id Section id.
 */
function grl_section_url( $id ) {
	return ( is_front_page() ? '' : grl_home_url() ) . '#' . $id;
}

/**
 * Home URL in the current language.
 */
function grl_home_url() {
	$url = function_exists( 'pll_home_url' ) ? pll_home_url() : '';
	return $url ? $url : home_url( '/' );
}

/**
 * Contact Form 7 (or other) shortcode from theme options, but only when its shortcode tag is registered.
 * Returns '' when the plugin is inactive, so templates fall back to the e-mail link instead of printing "[...]".
 *
 * @return string
 */
function grl_contact_shortcode() {
	$shortcode = trim( (string) grl_opt( 'contact', 'shortcode' ) );
	if ( '' === $shortcode || ! preg_match( '/^\[([A-Za-z0-9_-]+)/', $shortcode, $m ) || ! shortcode_exists( $m[1] ) ) {
		return '';
	}
	return $shortcode;
}

/**
 * Language switcher, rendered only when Polylang is active.
 * Links to the translation of the current project when one exists, otherwise to that language's home.
 */
function grl_language_switcher() {
	// Only Polylang's documented public API (pll_* functions); pll_the_languages() is avoided on purpose.
	foreach ( array( 'pll_languages_list', 'pll_current_language', 'pll_home_url', 'pll_get_post' ) as $fn ) {
		if ( ! function_exists( $fn ) ) {
			return;
		}
	}
	$slugs   = (array) pll_languages_list( array( 'fields' => 'slug' ) );
	$locales = (array) pll_languages_list( array( 'fields' => 'locale' ) );
	$current = pll_current_language( 'slug' );
	if ( count( $slugs ) < 2 || ! $current ) {
		return;
	}
	// Inline "ET | EN": the current language is plain text, the others are links.
	echo '<ul class="grl-lang" aria-label="' . esc_attr( grl_opt( 'header', 'lang_label' ) ) . '">';
	foreach ( $slugs as $i => $slug ) {
		$locale = isset( $locales[ $i ] ) ? str_replace( '_', '-', (string) $locales[ $i ] ) : $slug;
		if ( $slug === $current ) {
			echo '<li class="grl-lang__item"><span class="grl-lang__current" lang="' . esc_attr( $locale ) . '" aria-current="true">' . esc_html( strtoupper( $slug ) ) . '</span></li>';
			continue;
		}
		$url = pll_home_url( $slug );
		if ( is_singular() ) {
			$tr = pll_get_post( get_queried_object_id(), $slug );
			if ( $tr && 'publish' === get_post_status( $tr ) ) {
				$url = get_permalink( $tr );
			}
		}
		echo '<li class="grl-lang__item"><a class="grl-lang__link" href="' . esc_url( $url ) . '" hreflang="' . esc_attr( $slug ) . '" lang="' . esc_attr( $locale ) . '">' . esc_html( strtoupper( $slug ) ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Translate a page id to the current language (Polylang), when a published translation exists.
 *
 * @param int $id Page id.
 */
function grl_translated_page_id( $id ) {
	$id = (int) $id;
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$tr = pll_get_post( $id );
		if ( $tr && 'publish' === get_post_status( $tr ) ) {
			return (int) $tr;
		}
	}
	return $id;
}

/**
 * Privacy and cookie policy pages for the current language: [ id => title ].
 * Ids come from Settings > Privacy and the cookie consent plugin settings.
 */
function grl_policy_pages() {
	$ncc   = get_option( 'ncc_settings', array() );
	$ncc   = is_array( $ncc ) ? $ncc : array();
	$ids   = array( (int) get_option( 'wp_page_for_privacy_policy' ), isset( $ncc['cookie_policy_page'] ) ? (int) $ncc['cookie_policy_page'] : 0 );
	$pages = array();
	foreach ( $ids as $id ) {
		$id = grl_translated_page_id( $id );
		if ( $id && 'publish' === get_post_status( $id ) ) {
			$pages[ $id ] = get_the_title( $id );
		}
	}
	return $pages;
}

// Cookie banner policy links follow the visitor's language (the plugin stores one page id per link).
add_filter(
	'option_ncc_settings',
	function ( $settings ) {
		if ( is_admin() || ! is_array( $settings ) ) {
			return $settings;
		}
		foreach ( array( 'privacy_policy_page', 'cookie_policy_page' ) as $key ) {
			if ( ! empty( $settings[ $key ] ) ) {
				$settings[ $key ] = grl_translated_page_id( $settings[ $key ] );
			}
		}
		return $settings;
	}
);
