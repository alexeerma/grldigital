<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- kept as inc/seed.php (documented entry point, loaded only under WP-CLI).
/**
 * WP-CLI commands:
 * - `wp grl seed` creates/updates all projects (ET + EN) from data/seed-projects.json.
 * - `wp grl migrate` moves legacy project meta to the current structure (see inc/projects.php).
 *
 * Idempotent: projects are matched by slug + language, images are downloaded once
 * and reused (matched by source URL).
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Seed command.
 */
class GRL_Seed_Command {

	/**
	 * Create or update the projects from the bundled design data.
	 *
	 * ## OPTIONS
	 *
	 * [--skip-images]
	 * : Do not download images.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Flags.
	 */
	public function seed( $args, $assoc_args ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$file = get_template_directory() . '/data/seed-projects.json';
		$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.
		if ( ! is_array( $data ) ) {
			WP_CLI::error( 'Cannot read ' . $file );
		}
		$polylang = function_exists( 'pll_set_post_language' );
		$images   = empty( $assoc_args['skip-images'] );
		$langs    = $polylang ? array( 'et', 'en' ) : array( 'et' );

		foreach ( $data as $order => $item ) {
			$translations = array();
			foreach ( $langs as $lang ) {
				if ( empty( $item[ $lang ] ) ) {
					continue;
				}
				$id = $this->upsert( $item[ $lang ], $lang, $order, $polylang, $images );
				if ( $id ) {
					$translations[ $lang ] = $id;
				}
			}
			if ( $polylang && count( $translations ) > 1 ) {
				pll_save_post_translations( $translations );
			}
			WP_CLI::log( sprintf( '%d. %s (%s)', $order + 1, isset( $item['et']['title'] ) ? $item['et']['title'] : '?', implode( ', ', array_keys( $translations ) ) ) );
		}
		flush_rewrite_rules();
		WP_CLI::success( count( $data ) . ' projects seeded.' );
	}

	/**
	 * Create or update one project in one language.
	 *
	 * @param array  $p        Project data.
	 * @param string $lang     Language slug.
	 * @param int    $order    Menu order.
	 * @param bool   $polylang Polylang active.
	 * @param bool   $images   Download images.
	 * @return int Post ID.
	 */
	private function upsert( $p, $lang, $order, $polylang, $images ) {
		$query = array(
			'post_type'      => 'projekt',
			'name'           => $p['slug'],
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		);
		if ( $polylang ) {
			$query['lang'] = $lang;
		}
		$found = get_posts( $query );
		$post  = array(
			'post_type'    => 'projekt',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $p['slug'],
			'post_excerpt' => $p['excerpt'],
			'menu_order'   => $order,
		);
		if ( array_key_exists( 'content', $p ) ) {
			$post['post_content'] = (string) $p['content'];
		}
		if ( $found ) {
			// Update merges with the stored post, so free content edited in the admin is kept.
			$post['ID'] = $found[0];
			$id         = wp_update_post( wp_slash( $post ), true );
		} else {
			$id = wp_insert_post( wp_slash( $post ), true );
		}
		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( $p['slug'] . ': ' . $id->get_error_message() );
			return 0;
		}
		if ( $polylang ) {
			pll_set_post_language( $id, $lang );
		}
		update_post_meta( $id, '_grl_tag_list', grl_sanitize_tag_list( (array) ( $p['tags'] ?? array() ) ) );
		update_post_meta( $id, '_grl_live_url', esc_url_raw( (string) ( $p['live'] ?? '' ) ) );
		$text_fields = array(
			'link_label' => '_grl_link_label',
			'eyebrow'    => '_grl_eyebrow',
		);
		foreach ( $text_fields as $field => $key ) {
			if ( array_key_exists( $field, $p ) ) {
				update_post_meta( $id, $key, sanitize_text_field( (string) $p[ $field ] ) );
			}
		}
		if ( array_key_exists( 'show_home', $p ) ) {
			update_post_meta( $id, '_grl_show_home', $p['show_home'] ? '1' : '0' );
		} elseif ( ! metadata_exists( 'post', $id, '_grl_show_home' ) ) {
			update_post_meta( $id, '_grl_show_home', '1' );
		}

		if ( $images ) {
			$thumb = $this->image( $p['thumb'] ?? '', $id, $p['title'] );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
			$screen = $this->image( $p['screen'] ?? '', $id, $p['title'] );
			update_post_meta( $id, '_grl_screen_id', $screen ? $screen : '' );

			// "gallery" (list of URLs); the old single "extra" URL is still accepted.
			$urls    = isset( $p['gallery'] ) ? (array) $p['gallery'] : ( empty( $p['extra'] ) ? array() : array( $p['extra'] ) );
			$gallery = array();
			foreach ( $urls as $url ) {
				$gallery[] = $this->image( (string) $url, $id, $p['title'] );
			}
			update_post_meta( $id, '_grl_gallery', grl_sanitize_attachment_ids( $gallery ) );
		}
		delete_post_meta( $id, '_grl_tags' );
		delete_post_meta( $id, '_grl_extra_id' );
		delete_post_meta( $id, '_grl_hide_home' );
		return $id;
	}

	/**
	 * Migrate legacy project meta to the current structure.
	 *
	 * _grl_tags (comma list) -> _grl_tag_list (array)
	 * _grl_extra_id (one ID) -> _grl_gallery (array of IDs)
	 * _grl_hide_home ('1')   -> _grl_show_home ('0'), otherwise '1'
	 *
	 * Values already in the new keys are never overwritten. Legacy keys are deleted
	 * after their value is copied, unless --keep-legacy is given. Safe to re-run.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Report what would change without writing.
	 *
	 * [--keep-legacy]
	 * : Do not delete the legacy keys.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Flags.
	 */
	public function migrate( $args, $assoc_args ) {
		$dry  = ! empty( $assoc_args['dry-run'] );
		$keep = ! empty( $assoc_args['keep-legacy'] );
		$ids  = get_posts(
			array(
				'post_type'        => 'projekt',
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'lang'             => '',
				'suppress_filters' => false,
				'orderby'          => 'ID',
				'order'            => 'ASC',
			)
		);
		$rows = array();
		foreach ( $ids as $id ) {
			$changes = array();

			if ( ! is_array( get_post_meta( $id, '_grl_tag_list', true ) ) ) {
				$tags      = grl_sanitize_tag_list( (string) get_post_meta( $id, '_grl_tags', true ) );
				$changes[] = 'tags:' . count( $tags );
				if ( ! $dry ) {
					update_post_meta( $id, '_grl_tag_list', $tags );
				}
			}
			if ( ! is_array( get_post_meta( $id, '_grl_gallery', true ) ) ) {
				$extra     = absint( get_post_meta( $id, '_grl_extra_id', true ) );
				$gallery   = $extra ? grl_sanitize_attachment_ids( array( $extra ) ) : array();
				$changes[] = 'gallery:' . ( $gallery ? implode( ',', $gallery ) : '-' );
				if ( ! $dry ) {
					update_post_meta( $id, '_grl_gallery', $gallery );
				}
			}
			if ( ! metadata_exists( 'post', $id, '_grl_show_home' ) ) {
				$show      = '1' === (string) get_post_meta( $id, '_grl_hide_home', true ) ? '0' : '1';
				$changes[] = 'show:' . $show;
				if ( ! $dry ) {
					update_post_meta( $id, '_grl_show_home', $show );
				}
			}
			if ( ! $keep ) {
				foreach ( array( '_grl_tags', '_grl_extra_id', '_grl_hide_home' ) as $legacy ) {
					if ( metadata_exists( 'post', $id, $legacy ) ) {
						$changes[] = 'del' . $legacy;
						if ( ! $dry ) {
							delete_post_meta( $id, $legacy );
						}
					}
				}
			}

			$lang   = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : '';
			$rows[] = array(
				'ID'      => $id,
				'lang'    => $lang,
				'slug'    => get_post_field( 'post_name', $id ),
				'changes' => $changes ? implode( ' ', $changes ) : 'up to date',
			);
		}
		WP_CLI\Utils\format_items( 'table', $rows, array( 'ID', 'lang', 'slug', 'changes' ) );

		// Verify: every project has all new keys and no legacy keys left.
		$bad = 0;
		foreach ( $ids as $id ) {
			$ok = is_array( get_post_meta( $id, '_grl_tag_list', true ) )
				&& is_array( get_post_meta( $id, '_grl_gallery', true ) )
				&& metadata_exists( 'post', $id, '_grl_show_home' );
			if ( ! $keep ) {
				$ok = $ok && ! metadata_exists( 'post', $id, '_grl_tags' ) && ! metadata_exists( 'post', $id, '_grl_extra_id' ) && ! metadata_exists( 'post', $id, '_grl_hide_home' );
			}
			$bad += $ok ? 0 : 1;
		}
		$msg = sprintf( '%d projects checked, %d not on the new structure.', count( $ids ), $bad );
		if ( $dry ) {
			WP_CLI::log( 'Dry run. ' . $msg );
		} elseif ( $bad ) {
			WP_CLI::error( $msg );
		} else {
			WP_CLI::success( $msg );
		}
	}

	/**
	 * Download an image once; reuse the attachment if the URL was imported before.
	 *
	 * @param string $url     Source URL.
	 * @param int    $post_id Parent post.
	 * @param string $title   Alt/title.
	 * @return int Attachment ID or 0.
	 */
	private function image( $url, $post_id, $title ) {
		if ( ! $url || false === strpos( $url, '.' ) || '/' === substr( $url, -1 ) ) {
			return 0;
		}
		// Seed data is bundled, but only fetch over HTTPS (download_url() already rejects local/unsafe hosts).
		if ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
			WP_CLI::warning( $title . ': skipped non-HTTPS image URL ' . $url );
			return 0;
		}
		$existing = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_grl_source_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- CLI only.
				'meta_value'     => $url, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- CLI only.
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}
		$id = media_sideload_image( $url, $post_id, $title, 'id' );
		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( $title . ': ' . $id->get_error_message() );
			return 0;
		}
		update_post_meta( $id, '_grl_source_url', $url );
		update_post_meta( $id, '_wp_attachment_image_alt', $title );
		return (int) $id;
	}
}

WP_CLI::add_command( 'grl', 'GRL_Seed_Command' );
