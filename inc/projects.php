<?php
/**
 * "Minu tööd" (portfolio) custom post type, its fields, admin screen and list columns.
 *
 * Post type key stays `projekt` and the rewrite slug stays `projektid` so URLs don't change.
 *
 * Data model:
 * - post_title      Name.
 * - post_excerpt    Description (plain text).
 * - post_content    Optional free content, shown on the single page below the images.
 * - _thumbnail_id   Homepage image (featured image).
 * - menu_order      Order on the homepage (ASC).
 * - _grl_screen_id  Full-size image (attachment ID).
 * - _grl_gallery    Extra images (array of attachment IDs, ordered).
 * - _grl_tag_list   Tags (array of strings, ordered).
 * - _grl_live_url   Project link URL.
 * - _grl_link_label Project link label override.
 * - _grl_show_home  '1' = shown on the homepage, '0' = hidden. Missing = shown.
 * - _grl_eyebrow    Homepage eyebrow override.
 *
 * Legacy keys (_grl_tags, _grl_extra_id, _grl_hide_home) are migrated with `wp grl migrate`
 * and only read as a fallback.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'projekt',
			array(
				'labels'       => array(
					'name'                  => __( 'Minu tööd', 'grldigital' ),
					'singular_name'         => __( 'Töö', 'grldigital' ),
					'menu_name'             => __( 'Minu tööd', 'grldigital' ),
					'all_items'             => __( 'Kõik tööd', 'grldigital' ),
					'add_new'               => __( 'Lisa töö', 'grldigital' ),
					'add_new_item'          => __( 'Lisa uus töö', 'grldigital' ),
					'edit_item'             => __( 'Muuda tööd', 'grldigital' ),
					'new_item'              => __( 'Uus töö', 'grldigital' ),
					'view_item'             => __( 'Vaata tööd', 'grldigital' ),
					'view_items'            => __( 'Vaata töid', 'grldigital' ),
					'search_items'          => __( 'Otsi töid', 'grldigital' ),
					'not_found'             => __( 'Töid ei leitud', 'grldigital' ),
					'not_found_in_trash'    => __( 'Prügikastis töid ei ole', 'grldigital' ),
					'attributes'            => __( 'Järjestus', 'grldigital' ),
					'featured_image'        => __( 'Avalehe pilt', 'grldigital' ),
					'set_featured_image'    => __( 'Vali avalehe pilt', 'grldigital' ),
					'remove_featured_image' => __( 'Eemalda avalehe pilt', 'grldigital' ),
					'use_featured_image'    => __( 'Kasuta avalehe pildina', 'grldigital' ),
					'item_published'        => __( 'Töö avaldatud.', 'grldigital' ),
					'item_updated'          => __( 'Töö uuendatud.', 'grldigital' ),
				),
				'public'       => true,
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'projektid' ),
				'menu_icon'    => 'dashicons-portfolio',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
				'show_in_rest' => true,
			)
		);

		$auth = function ( $allowed, $meta_key, $post_id ) {
			return current_user_can( 'edit_post', $post_id );
		};
		$meta = array(
			'_grl_screen_id'  => array( 'integer', 'absint' ),
			'_grl_gallery'    => array( 'array', 'grl_sanitize_attachment_ids' ),
			'_grl_tag_list'   => array( 'array', 'grl_sanitize_tag_list' ),
			'_grl_live_url'   => array( 'string', 'esc_url_raw' ),
			'_grl_link_label' => array( 'string', 'sanitize_text_field' ),
			'_grl_show_home'  => array( 'string', 'grl_sanitize_flag' ),
			'_grl_eyebrow'    => array( 'string', 'sanitize_text_field' ),
		);
		foreach ( $meta as $key => $def ) {
			register_post_meta(
				'projekt',
				$key,
				array(
					'type'              => $def[0],
					'single'            => true,
					'show_in_rest'      => false,
					'sanitize_callback' => $def[1],
					'auth_callback'     => $auth,
				)
			);
		}
	}
);

// Classic editor for this post type: one clear form instead of block editor panels.
add_filter(
	'use_block_editor_for_post_type',
	function ( $use_block_editor, $post_type ) {
		return 'projekt' === $post_type ? false : $use_block_editor;
	},
	10,
	2
);

/**
 * Sanitize a list of attachment IDs: positive ints that are real attachments, unique, in order.
 *
 * @param mixed $ids Raw value.
 * @return int[]
 */
function grl_sanitize_attachment_ids( $ids ) {
	$out = array();
	foreach ( (array) $ids as $id ) {
		$id = absint( $id );
		if ( $id && 'attachment' === get_post_type( $id ) && ! in_array( $id, $out, true ) ) {
			$out[] = $id;
		}
	}
	return $out;
}

/**
 * Sanitize a tag list: trimmed plain text, empties removed, in order.
 *
 * @param mixed $tags Raw value (array or comma separated string).
 * @return string[]
 */
function grl_sanitize_tag_list( $tags ) {
	if ( is_string( $tags ) ) {
		$tags = explode( ',', $tags );
	}
	$out = array();
	foreach ( (array) $tags as $tag ) {
		$tag = trim( sanitize_text_field( (string) $tag ) );
		if ( '' !== $tag && ! in_array( $tag, $out, true ) ) {
			$out[] = $tag;
		}
	}
	return $out;
}

/**
 * Sanitize a '1' / '0' flag.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function grl_sanitize_flag( $value ) {
	return ( '0' === (string) $value || '' === (string) $value || false === $value ) ? '0' : '1';
}

// -------------------------------------------------------------------------
// Template helpers.
// -------------------------------------------------------------------------

/**
 * Tags of a project.
 *
 * @param int $post_id Post ID.
 * @return string[]
 */
function grl_project_tags( $post_id ) {
	$tags = get_post_meta( $post_id, '_grl_tag_list', true );
	if ( is_array( $tags ) ) {
		return grl_sanitize_tag_list( $tags );
	}
	return grl_sanitize_tag_list( (string) get_post_meta( $post_id, '_grl_tags', true ) );
}

/**
 * Extra image IDs (gallery) of a project, in order.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function grl_project_gallery( $post_id ) {
	$ids = get_post_meta( $post_id, '_grl_gallery', true );
	if ( is_array( $ids ) ) {
		return array_values( array_filter( array_map( 'absint', $ids ) ) );
	}
	$legacy = absint( get_post_meta( $post_id, '_grl_extra_id', true ) );
	return $legacy ? array( $legacy ) : array();
}

/**
 * Whether the project is shown on the homepage. Default: yes.
 *
 * @param int $post_id Post ID.
 */
function grl_project_shows_on_home( $post_id ) {
	if ( metadata_exists( 'post', $post_id, '_grl_show_home' ) ) {
		return '0' !== (string) get_post_meta( $post_id, '_grl_show_home', true );
	}
	return '1' !== (string) get_post_meta( $post_id, '_grl_hide_home', true );
}

/**
 * Language of a project (Polylang), or null.
 *
 * @param int $post_id Post ID.
 * @return string|null
 */
function grl_project_lang( $post_id ) {
	$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post_id, 'slug' ) : '';
	return $lang ? $lang : null;
}

/**
 * Projects that belong to a non-default language while Polylang is inactive.
 *
 * Polylang keeps its language terms in the DB after deactivation, but the `language` taxonomy is
 * then unregistered, so tax_query/get_terms can't see them. Without this the homepage would mix
 * ET and EN projects. Returns [] whenever Polylang is active or no language data exists.
 *
 * @return int[]
 */
function grl_projects_other_language_ids() {
	if ( function_exists( 'pll_current_language' ) || taxonomy_exists( 'language' ) ) {
		return array();
	}
	$cached = wp_cache_get( 'other_lang_projects', 'grl' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$pll     = get_option( 'polylang', array() );
	$default = is_array( $pll ) && ! empty( $pll['default_lang'] ) ? (string) $pll['default_lang'] : 'et';

	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- the taxonomy is unregistered, so no core API can read it; cached above/below.
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT tr.object_id FROM {$wpdb->term_relationships} tr
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
			INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
			WHERE tt.taxonomy = 'language' AND t.slug <> %s AND p.post_type = 'projekt'",
			$default
		)
	);
	$ids = array_map( 'absint', (array) $ids );
	wp_cache_set( 'other_lang_projects', $ids, 'grl', HOUR_IN_SECONDS );
	return $ids;
}

/**
 * Homepage eyebrow: per-project override, else theme option portfolio/eyebrow.
 *
 * @param int $post_id Post ID.
 */
function grl_project_eyebrow( $post_id ) {
	$value = (string) get_post_meta( $post_id, '_grl_eyebrow', true );
	return '' !== $value ? $value : grl_opt( 'portfolio', 'eyebrow' );
}

/**
 * Project link label: per-project override, else theme option single/live_label.
 *
 * @param int $post_id Post ID.
 */
function grl_project_link_label( $post_id ) {
	$value = (string) get_post_meta( $post_id, '_grl_link_label', true );
	return '' !== $value ? $value : grl_opt( 'single', 'live_label' );
}

// -------------------------------------------------------------------------
// Edit screen.
// -------------------------------------------------------------------------

add_action(
	'add_meta_boxes_projekt',
	function () {
		// Core boxes replaced by fields in our own boxes.
		remove_meta_box( 'postexcerpt', 'projekt', 'normal' );
		remove_meta_box( 'postimagediv', 'projekt', 'side' );

		add_meta_box( 'grl_project_details', __( 'Töö andmed', 'grldigital' ), 'grl_render_project_details', 'projekt', 'grl_after_title', 'high' );
		add_meta_box( 'grl_project_images', __( 'Pildid', 'grldigital' ), 'grl_render_project_images', 'projekt', 'grl_after_title', 'default' );
		add_meta_box( 'grl_project_home', __( 'Avaleht', 'grldigital' ), 'grl_render_project_home', 'projekt', 'side', 'high' );
	}
);

// Render our boxes right under the title, then label the editor as optional extra content.
add_action(
	'edit_form_after_title',
	function ( $post ) {
		if ( 'projekt' !== $post->post_type ) {
			return;
		}
		wp_nonce_field( 'grl_project_meta', 'grl_project_nonce' );
		do_meta_boxes( get_current_screen(), 'grl_after_title', $post );
		echo '<div class="grl-pm-editor-head"><h2 class="grl-pm-editor-title">' . esc_html__( 'Lisasisu (valikuline)', 'grldigital' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'Vabas vormis tekst, pildid või video. Näidatakse töö lehel piltide all. Tühi = ei näidata midagi.', 'grldigital' ) . '</p></div>';
	}
);

/**
 * One field wrapper: label, control, help text.
 *
 * @param string $id      Control ID.
 * @param string $label   Label.
 * @param string $control Control HTML (already escaped).
 * @param string $help    Help text (plain).
 */
function grl_pm_field( $id, $label, $control, $help = '' ) {
	echo '<div class="grl-pm-field">';
	echo '<label class="grl-pm-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	echo $control; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the caller.
	if ( $help ) {
		echo '<p class="description grl-pm-help">' . esc_html( $help ) . '</p>';
	}
	echo '</div>';
}

/**
 * "Töö andmed" box: description, tags, link.
 *
 * @param WP_Post $post Current post.
 */
function grl_render_project_details( $post ) {
	$lang = grl_project_lang( $post->ID );

	grl_pm_field(
		'excerpt',
		__( 'Kirjeldus', 'grldigital' ),
		'<textarea class="large-text" id="excerpt" name="excerpt" rows="5">' . esc_textarea( $post->post_excerpt ) . '</textarea>',
		__( 'Näidatakse avalehel töö juures ja töö lehe päises. Ainult tavatekst.', 'grldigital' )
	);

	$tags = grl_project_tags( $post->ID );
	$rows = '';
	foreach ( array_pad( $tags, 3, '' ) as $i => $tag ) {
		$rows .= '<li class="grl-pm-tags__item"><span class="dashicons dashicons-menu grl-pm-tags__handle" aria-hidden="true"></span>'
			. '<input type="text" class="regular-text grl-pm-tags__input" name="grl_tags[]" value="' . esc_attr( $tag ) . '"' . ( 0 === $i ? ' id="grl_tags_0"' : '' ) . ' aria-label="' . esc_attr__( 'Silt', 'grldigital' ) . '">'
			. '<button type="button" class="button-link grl-pm-tags__remove" aria-label="' . esc_attr__( 'Eemalda silt', 'grldigital' ) . '"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button></li>';
	}
	grl_pm_field(
		'grl_tags_0',
		__( 'Sildid', 'grldigital' ),
		'<ul class="grl-pm-tags">' . $rows . '</ul><button type="button" class="button grl-pm-tags__add">' . esc_html__( 'Lisa silt', 'grldigital' ) . '</button>',
		__( 'Nt „Veebilehe disain“, „Logo“. Näidatakse avalehel selles järjekorras (lohista järjekorra muutmiseks). Tühjad read jäetakse vahele.', 'grldigital' )
	);

	echo '<div class="grl-pm-row">';
	grl_pm_field(
		'grl_live_url',
		__( 'Projekti link', 'grldigital' ),
		'<input type="url" class="large-text" id="grl_live_url" name="grl_live_url" placeholder="https://" value="' . esc_attr( (string) get_post_meta( $post->ID, '_grl_live_url', true ) ) . '">',
		__( 'Valikuline. Valmis veebileht või Figma prototüüp. Tühi = linki töö lehel ei näidata.', 'grldigital' )
	);
	grl_pm_field(
		'grl_link_label',
		__( 'Lingi tekst', 'grldigital' ),
		'<input type="text" class="large-text" id="grl_link_label" name="grl_link_label" value="' . esc_attr( (string) get_post_meta( $post->ID, '_grl_link_label', true ) ) . '" placeholder="' . esc_attr( grl_opt( 'single', 'live_label', $lang ) ) . '">',
		/* translators: %s: default link label from theme options. */
		sprintf( __( 'Valikuline. Tühi = teema seadetest („%s“).', 'grldigital' ), grl_opt( 'single', 'live_label', $lang ) )
	);
	echo '</div>';
}

/**
 * Single image picker control.
 *
 * @param string $name  Input name / ID.
 * @param int    $id    Attachment ID.
 * @param string $size  Preview size.
 */
function grl_pm_image_control( $name, $id, $size = 'medium' ) {
	$img  = $id ? wp_get_attachment_image( $id, $size, false, array( 'class' => 'grl-pm-image__img' ) ) : '';
	$out  = '<div class="grl-pm-image' . ( $img ? ' has-image' : '' ) . '" data-grl-image>';
	$out .= '<input type="hidden" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $img ? (string) $id : '' ) . '" class="grl-pm-image__id">';
	$out .= '<div class="grl-pm-image__preview">' . $img . '</div>';
	$out .= '<div class="grl-pm-image__actions"><button type="button" class="button grl-pm-image__pick" data-label-empty="' . esc_attr__( 'Vali pilt', 'grldigital' ) . '" data-label-change="' . esc_attr__( 'Vaheta pilti', 'grldigital' ) . '">' . ( $img ? esc_html__( 'Vaheta pilti', 'grldigital' ) : esc_html__( 'Vali pilt', 'grldigital' ) ) . '</button>';
	$out .= '<button type="button" class="button-link button-link-delete grl-pm-image__remove">' . esc_html__( 'Eemalda', 'grldigital' ) . '</button></div>';
	$out .= '</div>';
	return $out;
}

/**
 * "Pildid" box: homepage image, full-size image, gallery.
 *
 * @param WP_Post $post Current post.
 */
function grl_render_project_images( $post ) {
	echo '<div class="grl-pm-row">';
	grl_pm_field(
		'grl_thumbnail_id',
		__( 'Avalehe pilt', 'grldigital' ),
		grl_pm_image_control( 'grl_thumbnail_id', (int) get_post_thumbnail_id( $post ) ),
		__( 'Mockup, mida näidatakse avalehe portfoolios. Seda kasutatakse ka jagamispildina.', 'grldigital' )
	);
	grl_pm_field(
		'grl_screen_id',
		__( 'Täissuuruses pilt', 'grldigital' ),
		grl_pm_image_control( 'grl_screen_id', absint( get_post_meta( $post->ID, '_grl_screen_id', true ) ) ),
		__( 'Kogu lehe kujundus. Näidatakse töö lehel esimese pildina.', 'grldigital' )
	);
	echo '</div>';

	$items = '';
	foreach ( grl_project_gallery( $post->ID ) as $id ) {
		$img = wp_get_attachment_image( $id, 'thumbnail', false, array( 'class' => 'grl-pm-gallery__img' ) );
		if ( ! $img ) {
			continue;
		}
		$items .= '<li class="grl-pm-gallery__item">' . $img
			. '<input type="hidden" name="grl_gallery[]" value="' . esc_attr( (string) $id ) . '">'
			. '<button type="button" class="grl-pm-gallery__remove" aria-label="' . esc_attr__( 'Eemalda pilt', 'grldigital' ) . '"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button></li>';
	}
	grl_pm_field(
		'grl_gallery_add',
		__( 'Lisapildid', 'grldigital' ),
		'<div class="grl-pm-gallery" data-grl-gallery><ul class="grl-pm-gallery__list">' . $items . '</ul>'
			. '<button type="button" id="grl_gallery_add" class="button grl-pm-gallery__add">' . esc_html__( 'Lisa pilte', 'grldigital' ) . '</button></div>',
		__( 'Nt visiitkaardid või turundusvisuaalid. Näidatakse töö lehel täissuuruses pildi järel. Lohista järjekorra muutmiseks, × eemaldab pildi (fail jääb meediateeki).', 'grldigital' )
	);
}

/**
 * "Avaleht" side box: show flag, eyebrow override.
 *
 * @param WP_Post $post Current post.
 */
function grl_render_project_home( $post ) {
	$lang = grl_project_lang( $post->ID );
	echo '<div class="grl-pm-field"><label class="grl-pm-check"><input type="checkbox" name="grl_show_home" value="1"' . checked( grl_project_shows_on_home( $post->ID ), true, false ) . '> ' . esc_html__( 'Näita avalehel', 'grldigital' ) . '</label>';
	echo '<p class="description grl-pm-help">' . esc_html__( 'Kui märge on maas, on töö nähtav ainult oma lehel. Järjekorra määrab „Järjestus“ (väiksem number eespool).', 'grldigital' ) . '</p></div>';

	grl_pm_field(
		'grl_eyebrow',
		__( 'Väike pealkiri', 'grldigital' ),
		'<input type="text" class="widefat" id="grl_eyebrow" name="grl_eyebrow" value="' . esc_attr( (string) get_post_meta( $post->ID, '_grl_eyebrow', true ) ) . '" placeholder="' . esc_attr( grl_opt( 'portfolio', 'eyebrow', $lang ) ) . '">',
		/* translators: %s: default eyebrow from theme options. */
		sprintf( __( 'Valikuline tekst pealkirja kohal avalehel. Tühi = teema seadetest („%s“).', 'grldigital' ), grl_opt( 'portfolio', 'eyebrow', $lang ) )
	);
}

add_action(
	'save_post_projekt',
	function ( $post_id ) {
		if ( ! isset( $_POST['grl_project_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['grl_project_nonce'] ) ), 'grl_project_meta' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized by the grl_sanitize_* helpers.
		$tags    = isset( $_POST['grl_tags'] ) ? grl_sanitize_tag_list( array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['grl_tags'] ) ) ) : array();
		$gallery = isset( $_POST['grl_gallery'] ) ? grl_sanitize_attachment_ids( array_map( 'absint', (array) wp_unslash( $_POST['grl_gallery'] ) ) ) : array();
		// phpcs:enable

		update_post_meta( $post_id, '_grl_tag_list', $tags );
		update_post_meta( $post_id, '_grl_gallery', $gallery );
		update_post_meta( $post_id, '_grl_live_url', isset( $_POST['grl_live_url'] ) ? esc_url_raw( wp_unslash( $_POST['grl_live_url'] ) ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- esc_url_raw.
		update_post_meta( $post_id, '_grl_link_label', isset( $_POST['grl_link_label'] ) ? sanitize_text_field( wp_unslash( $_POST['grl_link_label'] ) ) : '' );
		update_post_meta( $post_id, '_grl_eyebrow', isset( $_POST['grl_eyebrow'] ) ? sanitize_text_field( wp_unslash( $_POST['grl_eyebrow'] ) ) : '' );
		update_post_meta( $post_id, '_grl_show_home', empty( $_POST['grl_show_home'] ) ? '0' : '1' );

		$screen = isset( $_POST['grl_screen_id'] ) ? absint( $_POST['grl_screen_id'] ) : 0;
		update_post_meta( $post_id, '_grl_screen_id', $screen && wp_attachment_is_image( $screen ) ? $screen : '' );

		$thumb = isset( $_POST['grl_thumbnail_id'] ) ? absint( $_POST['grl_thumbnail_id'] ) : 0;
		if ( $thumb && wp_attachment_is_image( $thumb ) ) {
			set_post_thumbnail( $post_id, $thumb );
		} else {
			delete_post_thumbnail( $post_id );
		}

		// New structure saved: drop legacy keys so they can't shadow anything.
		delete_post_meta( $post_id, '_grl_tags' );
		delete_post_meta( $post_id, '_grl_extra_id' );
		delete_post_meta( $post_id, '_grl_hide_home' );
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || 'projekt' !== $screen->post_type ) {
			return;
		}
		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			wp_enqueue_style( 'grl-admin-projects', get_template_directory_uri() . '/assets/css/admin-projects.css', array(), GRL_VERSION );
		}
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'grl-admin', get_template_directory_uri() . '/assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), GRL_VERSION, true );
			wp_localize_script(
				'grl-admin',
				'grlProjectAdmin',
				array(
					'imageTitle'    => __( 'Vali pilt', 'grldigital' ),
					'imageButton'   => __( 'Kasuta seda pilti', 'grldigital' ),
					'galleryTitle'  => __( 'Lisa pilte', 'grldigital' ),
					'galleryButton' => __( 'Lisa valitud pildid', 'grldigital' ),
					'removeImage'   => __( 'Eemalda pilt', 'grldigital' ),
					'tag'           => __( 'Silt', 'grldigital' ),
					'removeTag'     => __( 'Eemalda silt', 'grldigital' ),
				)
			);
		}
	}
);

// -------------------------------------------------------------------------
// List table.
// -------------------------------------------------------------------------

add_filter(
	'manage_projekt_posts_columns',
	function ( $columns ) {
		$out = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$out['grl_thumb'] = '<span class="screen-reader-text">' . esc_html__( 'Avalehe pilt', 'grldigital' ) . '</span>';
			}
			$out[ $key ] = $label;
			if ( 'title' === $key ) {
				$out['grl_tags']  = esc_html__( 'Sildid', 'grldigital' );
				$out['grl_home']  = esc_html__( 'Avalehel', 'grldigital' );
				$out['grl_order'] = esc_html__( 'Järjestus', 'grldigital' );
			}
		}
		return $out;
	}
);

add_action(
	'manage_projekt_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'grl_thumb':
				$thumb = get_the_post_thumbnail( $post_id, 'thumbnail', array( 'class' => 'grl-pm-col-thumb' ) );
				echo $thumb ? $thumb : '<span class="grl-pm-col-thumb grl-pm-col-thumb--empty" aria-hidden="true"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup.
				break;
			case 'grl_tags':
				$tags = grl_project_tags( $post_id );
				echo $tags ? esc_html( implode( ', ', $tags ) ) : '<span aria-hidden="true">—</span>';
				break;
			case 'grl_home':
				if ( grl_project_shows_on_home( $post_id ) ) {
					echo '<span class="dashicons dashicons-yes grl-pm-col-yes" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Jah', 'grldigital' ) . '</span>';
				} else {
					echo '<span class="grl-pm-col-no">' . esc_html__( 'Peidetud', 'grldigital' ) . '</span>';
				}
				break;
			case 'grl_order':
				echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
				break;
		}
	},
	10,
	2
);

add_filter(
	'manage_edit-projekt_sortable_columns',
	function ( $columns ) {
		$columns['grl_order'] = 'menu_order';
		return $columns;
	}
);

// Admin list defaults to the homepage order.
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'projekt' !== $query->get( 'post_type' ) ) {
			return;
		}
		if ( ! $query->get( 'orderby' ) ) {
			$query->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				)
			);
		}
	}
);
