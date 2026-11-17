<?php
/**
 * Single project: header, full-size image, gallery, optional free content.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$grl_live   = get_post_meta( get_the_ID(), '_grl_live_url', true );
	$grl_images = array_values( array_unique( array_filter( array_merge( array( absint( get_post_meta( get_the_ID(), '_grl_screen_id', true ) ) ), grl_project_gallery( get_the_ID() ) ) ) ) );
	?>
	<section class="grl-single__header">
		<div class="grl-container grl-single__intro">
			<p class="grl-eyebrow grl-eyebrow--light"><?php echo esc_html( grl_opt( 'single', 'eyebrow' ) ); ?></p>
			<h1 class="grl-single__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="grl-single__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<?php if ( $grl_live ) : ?>
				<a class="grl-underline" href="<?php echo esc_url( $grl_live ); ?>" target="_blank" rel="noopener"><?php echo esc_html( grl_project_link_label( get_the_ID() ) ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php if ( $grl_images ) : ?>
		<section class="grl-single__screens">
			<div class="grl-container grl-single__screens-inner">
				<?php
				foreach ( $grl_images as $grl_img_id ) {
					echo wp_get_attachment_image(
						$grl_img_id,
						'full',
						false,
						array(
							'class' => 'grl-single__screen',
							'sizes' => '(max-width: 991px) 100vw, 940px',
						)
					);
				}
				?>
			</div>
		</section>
	<?php endif; ?>
	<?php if ( '' !== trim( (string) get_post_field( 'post_content', get_the_ID() ) ) ) : ?>
		<section class="grl-single__content">
			<div class="grl-container grl-single__content-inner">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
