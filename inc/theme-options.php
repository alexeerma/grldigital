<?php
/**
 * Schema-driven theme options ("Teema seaded"), stored per language.
 *
 * Each field has an Estonian `default` and optionally `default_en`.
 * Values are saved in the option `grl_options` as [lang][group][key].
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every editable group and field. Adding a field here is enough.
 */
function grl_option_schema() {
	return array(
		'header'    => array(
			'title'  => __( 'Päis', 'grldigital' ),
			'fields' => array(
				'nav_work'   => array(
					'label'      => __( 'Menüü: tööd', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Minu tööd',
					'default_en' => 'My work',
				),
				'nav_skills' => array(
					'label'      => __( 'Menüü: oskused', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Oskused',
					'default_en' => 'Skills',
				),
				'cta_label'  => array(
					'label'      => __( 'Nupu tekst', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Võta ühendust!',
					'default_en' => 'Email Now!',
				),
				'email'      => array(
					'label'   => __( 'E-post', 'grldigital' ),
					'type'    => 'email',
					'default' => 'info@grldigital.ee',
				),
				'menu_label' => array(
					'label'      => __( 'Mobiilimenüü nupu nimi (ekraanilugejale)', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Menüü',
					'default_en' => 'Menu',
				),
				'lang_label' => array(
					'label'      => __( 'Keelevaliku nimi (ekraanilugejale)', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Keel',
					'default_en' => 'Language',
				),
			),
		),
		'hero'      => array(
			'title'  => __( 'Avalehe hero', 'grldigital' ),
			'fields' => array(
				'line_1'       => array(
					'label'      => __( 'Pealkiri, rida 1', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Hei, olen Gerli,',
					'default_en' => 'Hi, I’m Gerli,',
				),
				'line_2'       => array(
					'label'      => __( 'Pealkiri, rida 2', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'veebidisainer.',
					'default_en' => 'Web Designer.',
				),
				'text'         => array(
					'label'      => __( 'Sissejuhatus', 'grldigital' ),
					'type'       => 'textarea',
					'default'    => 'Kujundan ja koostan ettevõtetele kauneid veebilehti ning muud vajalikku. Kui vajad kaasaegset ja võimsat kodulehte või muud kujundust, siis saada mulle e-mail.',
					'default_en' => 'I design and build beautiful websites and other related solutions for businesses. If you need a modern and powerful website or other design, send me an email.',
				),
				'button_label' => array(
					'label'      => __( 'Nupu tekst', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Vaata mu töid',
					'default_en' => 'See my work',
				),
				'scroll_label' => array(
					'label'      => __( 'Skrolli tekst', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Skrolli',
					'default_en' => 'Scroll',
				),
				'image'        => array(
					'label'   => __( 'Taustapilt (URL, tühi = vaikepilt)', 'grldigital' ),
					'type'    => 'url',
					'default' => '',
				),
			),
		),
		'portfolio' => array(
			'title'  => __( 'Portfoolio', 'grldigital' ),
			'fields' => array(
				'eyebrow'      => array(
					'label'      => __( 'Väike pealkiri', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Hiljutine töö',
					'default_en' => 'Latest work',
				),
				'button_label' => array(
					'label'      => __( 'Nupu tekst', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Vaata seda projekti',
					'default_en' => 'See this project',
				),
				'empty'        => array(
					'label'      => __( 'Tekst, kui projekte pole', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Projekte veel pole.',
					'default_en' => 'No projects yet.',
				),
			),
		),
		'skills'    => array(
			'title'  => __( 'Mida ma teen', 'grldigital' ),
			'fields' => array(
				'title_1' => array(
					'label'      => __( 'Pealkiri, osa 1', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Mida',
					'default_en' => 'What do',
				),
				'title_2' => array(
					'label'      => __( 'Pealkiri, osa 2', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'ma teen?',
					'default_en' => 'I do?',
				),
				'items'   => array(
					'label'      => __( 'Plokid (üks rida = Pealkiri | Tekst)', 'grldigital' ),
					'type'       => 'textarea',
					'default'    => "Disain | Kujundan ilusaid ja võimsaid veebilehti ettevõtetele. Iga ettevõte vajab tänapäeval veebilehte, mis võidab klientide usalduse ja aitab oma äril hästi müüa. Lisaks veebilehtedele disainin erinevaid sotsiaalmeedia postitusi, bännereid ja muud vajalikku. Canva ja Figma on minu põhilised tööriistad.\nArendus | Ehitan vajadusel Webflow's veebilehti. Võrreldes Wordpressiga, ei pea Webflow kasutamiseks teadma programmeerimisest ega HTML-ist, sest kõik käib graafilise liidesega ja tulemus on kohe näha.",
					'default_en' => "Design | Any business today needs a website that wins customers’ trust and helps you do your business well. I make sure your website is up to that standard. I also design different social media posts, banners and other visuals relevant for marketing your business. For designing I use mainly Figma but also Canva.\nDevelopment | I build websites in Webflow if necessary. Compared to Wordpress, you don't need to know programming or HTML to use Webflow because everything is done with a graphical interface and the result is immediately visible.",
				),
				'image'   => array(
					'label'   => __( 'Pilt (URL, tühi = kuu)', 'grldigital' ),
					'type'    => 'url',
					'default' => '',
				),
				'spin'    => array(
					'label'   => __( 'Kuu pöörlemine: sekundit ühe ringi kohta (0 = ei pöörle)', 'grldigital' ),
					'type'    => 'text',
					'default' => '40',
				),
			),
		),
		'footer'    => array(
			'title'  => __( 'Jalus', 'grldigital' ),
			'fields' => array(
				'title_1'   => array(
					'label'      => __( 'Pealkiri, osa 1', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Soovid',
					'default_en' => 'Want to work',
				),
				'title_2'   => array(
					'label'      => __( 'Pealkiri, osa 2', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'koostööd teha?',
					'default_en' => 'together?',
				),
				'text'      => array(
					'label'      => __( 'Tekst (e-post lisatakse lõppu)', 'grldigital' ),
					'type'       => 'textarea',
					'default'    => "Kui vajad oma ettevõttele, startup'ile või endale kaasaegset ja võimsat veebilehte, olen tööks valmis. Võite saata mulle otse meili aadressil",
					'default_en' => 'If you need a modern and powerful website for your business, startup or yourself, I am available for work. You can email me directly at',
				),
				'copyright' => array(
					'label'      => __( 'Autoriõigus', 'grldigital' ),
					'type'       => 'text',
					'default'    => '© Kõik õigused kaitstud – GRL Digital OÜ',
					'default_en' => '© All rights reserved – GRL Digital OÜ',
				),
			),
		),
		'single'    => array(
			'title'  => __( 'Projekti leht', 'grldigital' ),
			'fields' => array(
				'eyebrow'    => array(
					'label'      => __( 'Väike pealkiri', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Projekt',
					'default_en' => 'Project',
				),
				'live_label' => array(
					'label'      => __( 'Lingi tekst', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Külasta kodulehte →',
					'default_en' => 'Visit website →',
				),
			),
		),
		'seo'       => array(
			'title'  => __( 'SEO: avaleht', 'grldigital' ),
			'fields' => array(
				'home_title'       => array(
					'label'      => __( 'Avalehe SEO pealkiri (kuni ~60 märki)', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Veebidisainer Gerli: kodulehe tegemine | GRL Digital',
					'default_en' => 'Gerli, Web Designer in Estonia | GRL Digital',
				),
				'home_description' => array(
					'label'      => __( 'Avalehe meta kirjeldus (kuni ~155 märki)', 'grldigital' ),
					'type'       => 'textarea',
					'default'    => 'Olen Gerli, veebidisainer. Kujundan ja teen ettevõtetele kaasaegseid kodulehti, Webflow veebilehti ning sotsiaalmeedia visuaale. Vaata mu töid.',
					'default_en' => 'I’m Gerli, a web designer in Estonia. I design and build modern websites for businesses, Webflow sites and social media visuals. See my work.',
				),
			),
		),
		'ui'        => array(
			'title'  => __( 'Muud tekstid', 'grldigital' ),
			'fields' => array(
				'not_found' => array(
					'label'      => __( '404 pealkiri', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Lehte ei leitud',
					'default_en' => 'Page not found',
				),
				'back_home' => array(
					'label'      => __( '404 link avalehele', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Tagasi avalehele',
					'default_en' => 'Back to homepage',
				),
			),
		),
		'contact'   => array(
			'title'  => __( 'Kontakti hüpikaken', 'grldigital' ),
			'fields' => array(
				'shortcode'   => array(
					'label'      => __( 'Contact Form 7 lühikood', 'grldigital' ),
					'type'       => 'text',
					'default'    => '',
					'default_en' => '',
				),
				'heading'     => array(
					'label'      => __( 'Pealkiri', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Võta ühendust',
					'default_en' => 'Get in touch',
				),
				'intro'       => array(
					'label'      => __( 'Sissejuhatus', 'grldigital' ),
					'type'       => 'textarea',
					'default'    => 'Räägi mulle oma projektist või küsi julgelt. Vastan esimesel võimalusel.',
					'default_en' => 'Tell me about your project or just ask. I will get back to you as soon as possible.',
				),
				'close_label' => array(
					'label'      => __( 'Sulgemisnupu nimi (ekraanilugejale)', 'grldigital' ),
					'type'       => 'text',
					'default'    => 'Sulge',
					'default_en' => 'Close',
				),
				'fallback'    => array(
					'label'      => __( 'Tekst, kui lühikood puudub (e-post lisatakse lõppu)', 'grldigital' ),
					'type'       => 'textarea',
					'default'    => 'Kirjuta mulle otse aadressil',
					'default_en' => 'Email me directly at',
				),
			),
		),
	);
}

/**
 * Language slugs the options can be edited in. Polylang languages, or just "et".
 *
 * @return array slug => name
 */
function grl_option_languages() {
	if ( function_exists( 'pll_languages_list' ) ) {
		$slugs = (array) pll_languages_list( array( 'fields' => 'slug' ) );
		$names = (array) pll_languages_list( array( 'fields' => 'name' ) );
		// array_combine() throws a ValueError on PHP 8 when the lists differ in length.
		if ( $slugs && count( $slugs ) === count( $names ) ) {
			return array_combine( $slugs, $names );
		}
	}
	return array( 'et' => 'Eesti' );
}

/**
 * Current front-end language slug.
 */
function grl_current_lang() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	return $lang ? $lang : 'et';
}

/**
 * Read one option for the current (or given) language, falling back to schema defaults.
 *
 * @param string      $group Group key.
 * @param string      $key   Field key.
 * @param string|null $lang  Language slug.
 */
function grl_opt( $group, $key, $lang = null ) {
	$lang  = $lang ? $lang : grl_current_lang();
	$saved = get_option( 'grl_options', array() );
	if ( is_array( $saved ) && isset( $saved[ $lang ][ $group ][ $key ] ) && is_scalar( $saved[ $lang ][ $group ][ $key ] ) && '' !== (string) $saved[ $lang ][ $group ][ $key ] ) {
		return (string) $saved[ $lang ][ $group ][ $key ];
	}
	// Built only when a default is needed: the schema runs dozens of translation lookups.
	$schema = grl_option_schema();
	$field  = isset( $schema[ $group ]['fields'][ $key ] ) ? $schema[ $group ]['fields'][ $key ] : array();
	if ( isset( $field[ 'default_' . $lang ] ) ) {
		return $field[ 'default_' . $lang ];
	}
	return isset( $field['default'] ) ? $field['default'] : '';
}

/**
 * Read a repeatable textarea: one row per line, columns split by " | ".
 *
 * @param string $group Group key.
 * @param string $key   Field key.
 */
function grl_opt_lines( $group, $key ) {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) grl_opt( $group, $key ) ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$rows[] = array_map( 'trim', explode( ' | ', $line ) );
		}
	}
	return $rows;
}

add_action(
	'admin_menu',
	function () {
		add_menu_page( __( 'Teema seaded', 'grldigital' ), __( 'Teema seaded', 'grldigital' ), 'manage_options', 'grl-options', 'grl_render_options', 'dashicons-admin-customizer', 61 );
	}
);

/**
 * Render the options screen, one tab per language.
 */
function grl_render_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Puuduvad õigused.', 'grldigital' ) );
	}
	$langs = grl_option_languages();
	$lang  = isset( $_GET['lang_tab'] ) ? sanitize_key( wp_unslash( $_GET['lang_tab'] ) ) : key( $langs ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $langs[ $lang ] ) ) {
		$lang = key( $langs );
	}

	echo '<div class="wrap"><h1>' . esc_html__( 'Teema seaded', 'grldigital' ) . '</h1>';
	if ( isset( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Salvestatud.', 'grldigital' ) . '</p></div>';
	}
	if ( count( $langs ) > 1 ) {
		echo '<nav class="nav-tab-wrapper">';
		foreach ( $langs as $slug => $name ) {
			$url = add_query_arg(
				array(
					'page'     => 'grl-options',
					'lang_tab' => $slug,
				),
				admin_url( 'admin.php' )
			);
			echo '<a class="nav-tab' . ( $slug === $lang ? ' nav-tab-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
		}
		echo '</nav>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="grl_save_options">';
	echo '<input type="hidden" name="grl_lang" value="' . esc_attr( $lang ) . '">';
	wp_nonce_field( 'grl_save_options', 'grl_nonce' );
	foreach ( grl_option_schema() as $group => $def ) {
		echo '<h2>' . esc_html( $def['title'] ) . '</h2><table class="form-table" role="presentation">';
		foreach ( $def['fields'] as $key => $field ) {
			$id    = 'grl-' . $group . '-' . $key;
			$name  = 'grl[' . $group . '][' . $key . ']';
			$value = grl_opt( $group, $key, $lang );
			echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
			if ( 'textarea' === $field['type'] ) {
				echo '<textarea class="large-text" rows="5" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
			} else {
				$type = in_array( $field['type'], array( 'email', 'url' ), true ) ? $field['type'] : 'text';
				echo '<input class="regular-text" type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
	submit_button();
	echo '</form></div>';
}

add_action(
	'admin_post_grl_save_options',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['grl_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['grl_nonce'] ) ), 'grl_save_options' ) ) {
			wp_die( esc_html__( 'Kontroll ebaõnnestus.', 'grldigital' ), '', array( 'response' => 403 ) );
		}
		$langs = grl_option_languages();
		$lang  = isset( $_POST['grl_lang'] ) ? sanitize_key( wp_unslash( $_POST['grl_lang'] ) ) : '';
		if ( ! isset( $langs[ $lang ] ) ) {
			wp_die( esc_html__( 'Tundmatu keel.', 'grldigital' ), '', array( 'response' => 400 ) );
		}
		$input = isset( $_POST['grl'] ) && is_array( $_POST['grl'] ) ? wp_unslash( $_POST['grl'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
		$clean = array();
		foreach ( grl_option_schema() as $group => $def ) {
			foreach ( $def['fields'] as $key => $field ) {
				$raw = isset( $input[ $group ][ $key ] ) && is_scalar( $input[ $group ][ $key ] ) ? (string) $input[ $group ][ $key ] : '';
				switch ( $field['type'] ) {
					case 'textarea':
						$clean[ $group ][ $key ] = sanitize_textarea_field( $raw );
						break;
					case 'email':
						$clean[ $group ][ $key ] = sanitize_email( $raw );
						break;
					case 'url':
						$clean[ $group ][ $key ] = esc_url_raw( $raw );
						break;
					default:
						$clean[ $group ][ $key ] = sanitize_text_field( $raw );
				}
			}
		}
		$all          = get_option( 'grl_options', array() );
		$all          = is_array( $all ) ? $all : array();
		$all[ $lang ] = $clean;
		update_option( 'grl_options', $all );
		wp_safe_redirect( admin_url( 'admin.php?page=grl-options&updated=1&lang_tab=' . rawurlencode( $lang ) ) );
		exit;
	}
);
