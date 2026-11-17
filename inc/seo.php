<?php
/**
 * SEO glue on top of Rank Math + Polylang. Rank Math stays the only SEO output.
 *
 * - Per-language homepage title/description from theme options ("seo" group),
 *   because Rank Math free has a single homepage setting.
 * - Projects: Rank Math's Article node becomes a CreativeWork made by the Organization.
 * - hreflang x-default pointing to the default language.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add hreflang x-default (default language URL) to Polylang's alternates.
 *
 * @param array $hreflangs hreflang => URL.
 */
function grl_seo_hreflang_x_default( $hreflangs ) {
	if ( ! is_array( $hreflangs ) || isset( $hreflangs['x-default'] ) || ! function_exists( 'pll_default_language' ) ) {
		return $hreflangs;
	}
	$default = pll_default_language( 'slug' );
	if ( $default && isset( $hreflangs[ $default ] ) && count( $hreflangs ) > 1 ) {
		$hreflangs['x-default'] = $hreflangs[ $default ];
	}
	return $hreflangs;
}
add_filter( 'pll_rel_hreflang_attributes', 'grl_seo_hreflang_x_default' );

// Everything below hooks into Rank Math only. Without it this file adds nothing and core's title-tag is used.
if ( ! class_exists( 'RankMath' ) ) {
	return;
}

/**
 * Per-language homepage SEO text, or '' when not on the front page / empty.
 *
 * @param string $key Field key in the "seo" group.
 */
function grl_seo_home_text( $key ) {
	if ( ! is_front_page() ) {
		return '';
	}
	return trim( wp_strip_all_tags( (string) grl_opt( 'seo', $key ) ) );
}

// Title: Rank Math escapes the filtered value itself; it is reused for og:title, twitter:title and WebPage name.
add_filter(
	'rank_math/frontend/title',
	function ( $title ) {
		$custom = grl_seo_home_text( 'home_title' );
		return '' !== $custom ? $custom : $title;
	}
);

// Description: also the og:/twitter:description fallback while the Rank Math social fields are empty.
add_filter(
	'rank_math/frontend/description',
	function ( $description ) {
		$custom = grl_seo_home_text( 'home_description' );
		return '' !== $custom ? $custom : $description;
	}
);

/**
 * Projects: a portfolio item is a design work, not an article. Rank Math free has no
 * CreativeWork type, so its Article node (keeps the full graph) is rewritten here.
 * The anonymous WP user "author" node is dropped; the Organization is the creator.
 *
 * @param array $data JSON-LD graph entities keyed by Rank Math.
 */
function grl_seo_project_schema( $data ) {
	if ( ! is_singular( 'projekt' ) || ! is_array( $data ) || empty( $data['richSnippet'] ) ) {
		return $data;
	}
	$post_id = get_queried_object_id();
	$url     = get_permalink( $post_id );
	$node    = $data['richSnippet'];
	$org     = isset( $data['publisher']['@id'] ) ? array( '@id' => $data['publisher']['@id'] ) : null;
	if ( ! is_array( $node ) || ! $url ) {
		return $data;
	}

	foreach ( $data as $key => $entity ) {
		if ( is_array( $entity ) && isset( $entity['@type'], $entity['@id'] ) && 'Person' === $entity['@type'] && is_string( $entity['@id'] ) && '#author' === substr( $entity['@id'], -7 ) ) {
			unset( $data[ $key ] );
		}
	}
	unset( $node['headline'], $node['author'], $node['articleSection'], $node['wordCount'], $node['speakable'] );

	$node['@type'] = 'CreativeWork';
	$node['@id']   = $url . '#creativework';
	$node['name']  = wp_strip_all_tags( get_the_title( $post_id ) );
	$node['url']   = $url;
	if ( $org ) {
		$node['creator'] = $org;
		$node['author']  = $org;
	}
	if ( function_exists( 'grl_project_tags' ) ) {
		$tags = array_filter( array_map( 'wp_strip_all_tags', (array) grl_project_tags( $post_id ) ) );
		if ( $tags ) {
			$node['keywords'] = implode( ', ', $tags );
		}
	}
	if ( isset( $data['WebPage'] ) && is_array( $data['WebPage'] ) ) {
		$data['WebPage']['mainEntity'] = array( '@id' => $node['@id'] );
	}

	$data['richSnippet'] = $node;
	return $data;
}
add_filter( 'rank_math/json_ld', 'grl_seo_project_schema', 120 ); // After Rank Math connects entities (99).

/**
 * Whether Rank Math listed post-sitemap.xml in the sitemap index during this request.
 *
 * @param bool|null $set Pass true to mark it as listed.
 */
function grl_seo_post_sitemap_listed( $set = null ) {
	static $listed = false;
	if ( null !== $set ) {
		$listed = (bool) $set;
	}
	return $listed;
}

add_filter(
	'rank_math/sitemap/index/entry',
	function ( $item, $type = '', $object_type = '' ) {
		if ( $item && 'post' === $type && 'post' === $object_type ) {
			grl_seo_post_sitemap_listed( true );
		}
		return $item;
	},
	99,
	3
);

/**
 * The front page lists posts (show_on_front=posts), so Rank Math puts the language homepages
 * into post-sitemap.xml, but skips that sitemap in the index while there are no indexable posts.
 * List it anyway so the ET and EN homepages are always in the sitemap.
 *
 * @param string $xml Extra index XML.
 */
function grl_seo_sitemap_index_home( $xml ) {
	if ( 'posts' !== get_option( 'show_on_front' ) || grl_seo_post_sitemap_listed() || ! is_callable( array( '\RankMath\Sitemap\Router', 'get_base_url' ) ) ) {
		return $xml;
	}
	$loc = \RankMath\Sitemap\Router::get_base_url( 'post-sitemap.xml' );
	return $xml . "\t<sitemap>\n\t\t<loc>" . esc_url( $loc ) . "</loc>\n\t</sitemap>\n";
}
add_filter( 'rank_math/sitemap/index', 'grl_seo_sitemap_index_home' );
