<?php
/**
 * Site footer.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

$grl_email = grl_opt( 'header', 'email' );
?>
</main>
<footer class="grl-footer">
	<div class="grl-container">
		<div class="grl-footer__row">
			<h2 class="grl-display grl-display--light" data-grl-reveal>
				<?php echo esc_html( grl_opt( 'footer', 'title_1' ) ); ?>
				<span class="grl-display__nowrap"><?php echo esc_html( grl_opt( 'footer', 'title_2' ) ); ?></span>
			</h2>
			<p class="grl-footer__text">
				<?php echo esc_html( grl_opt( 'footer', 'text' ) ); ?>
				<?php if ( $grl_email ) : ?>
					<a class="grl-underline" href="<?php echo esc_url( 'mailto:' . $grl_email ); ?>"><?php echo esc_html( $grl_email ); ?></a>
				<?php endif; ?>
			</p>
		</div>
		<div class="grl-footer__bottom">
			<p class="grl-footer__copyright"><?php echo esc_html( grl_opt( 'footer', 'copyright' ) ); ?></p>
			<?php $grl_policies = grl_policy_pages(); ?>
			<?php if ( $grl_policies ) : ?>
				<ul class="grl-footer__links">
					<?php foreach ( $grl_policies as $grl_pid => $grl_ptitle ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $grl_pid ) ); ?>"><?php echo esc_html( $grl_ptitle ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</footer>
<?php get_template_part( 'template-parts/contact-modal' ); ?>
<?php wp_footer(); ?>
</body>
</html>
