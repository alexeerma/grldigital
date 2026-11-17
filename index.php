<?php
/**
 * Fallback template for pages, posts and 404.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="grl-page">
	<div class="grl-container grl-page__inner">
		<?php if ( is_404() ) : ?>
			<h1 class="grl-page__title"><?php echo esc_html( grl_opt( 'ui', 'not_found' ) ); ?></h1>
			<p><a class="grl-underline" href="<?php echo esc_url( grl_home_url() ); ?>"><?php echo esc_html( grl_opt( 'ui', 'back_home' ) ); ?></a></p>
		<?php elseif ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'grl-page__article' ); ?>>
					<?php if ( is_singular() ) : ?>
						<h1 class="grl-page__title"><?php the_title(); ?></h1>
						<div class="grl-page__content"><?php the_content(); ?></div>
					<?php else : ?>
						<h2 class="grl-page__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
					<?php endif; ?>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
