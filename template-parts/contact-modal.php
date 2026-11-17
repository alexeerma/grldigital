<?php
/**
 * Contact popup (native <dialog>), opened by [data-grl-contact] triggers and #kontakt links.
 * Shows the Contact Form 7 form from theme options, or the e-mail link as a fallback.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

$grl_contact_email     = grl_opt( 'header', 'email' );
$grl_contact_shortcode = grl_contact_shortcode(); // '' when the shortcode's plugin (CF7) is inactive.
$grl_contact_heading   = grl_opt( 'contact', 'heading' );
$grl_contact_intro     = grl_opt( 'contact', 'intro' );

if ( ! $grl_contact_shortcode && ! $grl_contact_email ) {
	return;
}

$grl_contact_form = '';
if ( $grl_contact_shortcode ) {
	// do_shortcode() on an admin-supplied value is intended: only manage_options can save theme options.
	// Give the CF7 <form> an accessible name in the current language (CF7 defaults to English "Contact form").
	$grl_contact_title_attr = function ( $out ) use ( $grl_contact_heading ) {
		if ( is_array( $out ) && empty( $out['html_title'] ) ) {
			$out['html_title'] = $grl_contact_heading;
		}
		return $out;
	};
	add_filter( 'shortcode_atts_wpcf7', $grl_contact_title_attr );
	$grl_contact_form = do_shortcode( $grl_contact_shortcode );
	remove_filter( 'shortcode_atts_wpcf7', $grl_contact_title_attr );
}
?>
<dialog id="grl-contact" class="grl-contact" aria-labelledby="grl-contact-title">
	<div class="grl-contact__panel">
		<button type="button" class="grl-contact__close" data-grl-contact-close>
			<span class="screen-reader-text"><?php echo esc_html( grl_opt( 'contact', 'close_label' ) ); ?></span>
			<span class="grl-contact__close-icon" aria-hidden="true"></span>
		</button>
		<h2 id="grl-contact-title" class="grl-contact__title"><?php echo esc_html( $grl_contact_heading ); ?></h2>
		<?php if ( $grl_contact_intro ) : ?>
			<p class="grl-contact__intro"><?php echo nl2br( esc_html( $grl_contact_intro ) ); ?></p>
		<?php endif; ?>
		<?php if ( $grl_contact_form && false === strpos( $grl_contact_form, '[contact-form-7' ) ) : ?>
			<div class="grl-contact__form">
				<?php echo $grl_contact_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contact Form 7 form markup, escaped by the plugin; wp_kses_post() would strip <form>/<input>. ?>
			</div>
		<?php elseif ( $grl_contact_email ) : ?>
			<p class="grl-contact__fallback">
				<?php echo esc_html( grl_opt( 'contact', 'fallback' ) ); ?>
				<a class="grl-contact__mail" href="<?php echo esc_url( 'mailto:' . $grl_contact_email ); ?>"><?php echo esc_html( $grl_contact_email ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</dialog>
