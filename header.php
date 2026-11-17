<?php
/**
 * Site header.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

$grl_email = grl_opt( 'header', 'email' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="grl-header">
	<div class="grl-header__inner">
		<a class="grl-header__brand" href="<?php echo esc_url( grl_home_url() ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'medium', false, array( 'class' => 'grl-header__logo' ) ); ?>
			<?php else : ?>
				<img class="grl-header__logo" src="<?php echo esc_url( grl_img( 'logo.svg' ) ); ?>" width="114" height="61" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php endif; ?>
		</a>
		<button type="button" class="grl-header__toggle" aria-expanded="false" aria-controls="grl-nav">
			<span class="screen-reader-text"><?php echo esc_html( grl_opt( 'header', 'menu_label' ) ); ?></span>
			<span class="grl-header__burger" aria-hidden="true"></span>
		</button>
		<nav id="grl-nav" class="grl-nav" aria-label="<?php echo esc_attr( grl_opt( 'header', 'menu_label' ) ); ?>">
			<a class="grl-nav__link" href="<?php echo esc_url( grl_section_url( 'tehtud_tood' ) ); ?>"><?php echo esc_html( grl_opt( 'header', 'nav_work' ) ); ?></a>
			<a class="grl-nav__link" href="<?php echo esc_url( grl_section_url( 'oskused' ) ); ?>"><?php echo esc_html( grl_opt( 'header', 'nav_skills' ) ); ?></a>
			<?php if ( $grl_email || grl_contact_shortcode() ) : ?>
				<?php // No-JS fallback: the mailto link works on its own; contact.js opens the popup instead. ?>
				<a class="grl-nav__cta" href="<?php echo esc_url( $grl_email ? 'mailto:' . $grl_email : '#kontakt' ); ?>" data-grl-contact aria-haspopup="dialog" aria-controls="grl-contact"><?php echo esc_html( grl_opt( 'header', 'cta_label' ) ); ?></a>
			<?php endif; ?>
			<?php grl_language_switcher(); ?>
		</nav>
	</div>
</header>
<main id="main" class="grl-main">
