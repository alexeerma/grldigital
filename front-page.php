<?php
/**
 * Front page: hero, portfolio, skills.
 *
 * @package grldigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$grl_hero      = grl_hero_images();
$grl_moon_img  = grl_opt( 'skills', 'image' );
$grl_btn_label = grl_opt( 'portfolio', 'button_label' );

$grl_projects = new WP_Query(
	array(
		'post_type'      => 'projekt',
		'posts_per_page' => 50,
		'no_found_rows'  => true,
		// Without Polylang: only default-language projects (empty array = no filter).
		'post__not_in'   => grl_projects_other_language_ids(),
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- small, bounded list.
			'relation' => 'OR',
			array(
				'key'     => '_grl_show_home',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_grl_show_home',
				'value'   => '0',
				'compare' => '!=',
			),
		),
	)
);
?>
<section class="grl-hero" style="--grl-hero-1x: url('<?php echo esc_attr( grl_css_url( $grl_hero['1x'] ) ); ?>'); --grl-hero-2x: url('<?php echo esc_attr( grl_css_url( $grl_hero['2x'] ) ); ?>')">
	<div class="grl-container grl-hero__inner">
		<h1 class="grl-hero__title">
			<span><?php echo esc_html( grl_opt( 'hero', 'line_1' ) ); ?></span>
			<span><?php echo esc_html( grl_opt( 'hero', 'line_2' ) ); ?></span>
		</h1>
		<p class="grl-hero__text"><?php echo esc_html( grl_opt( 'hero', 'text' ) ); ?></p>
		<span class="grl-btn-frame grl-btn-frame--light">
			<a class="grl-btn grl-btn--light" href="#tehtud_tood"><?php echo esc_html( grl_opt( 'hero', 'button_label' ) ); ?></a>
		</span>
	</div>
	<a class="grl-hero__scroll" href="#tehtud_tood" aria-hidden="true" tabindex="-1">
		<span class="grl-hero__scroll-line"></span>
		<span class="grl-hero__scroll-text"><?php echo esc_html( grl_opt( 'hero', 'scroll_label' ) ); ?></span>
	</a>
</section>

<section id="tehtud_tood" class="grl-portfolio">
	<div class="grl-container">
		<?php if ( $grl_projects->have_posts() ) : ?>
			<?php
			while ( $grl_projects->have_posts() ) :
				$grl_projects->the_post();
				$grl_link = get_permalink();
				?>
				<article class="grl-project">
					<div class="grl-project__intro" data-grl-reveal>
						<p class="grl-eyebrow"><?php echo esc_html( grl_project_eyebrow( get_the_ID() ) ); ?></p>
						<h2 class="grl-project__title"><?php the_title(); ?></h2>
						<?php $grl_tags = grl_project_tags( get_the_ID() ); ?>
						<?php if ( $grl_tags ) : ?>
							<ul class="grl-project__tags">
								<?php foreach ( $grl_tags as $grl_tag ) : ?>
									<li class="grl-project__tag"><?php echo esc_html( $grl_tag ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
					<a class="grl-project__media" href="<?php echo esc_url( $grl_link ); ?>" tabindex="-1" aria-hidden="true">
						<?php
						the_post_thumbnail(
							'large',
							array(
								'class'   => 'grl-project__img',
								'loading' => 'lazy',
								'sizes'   => '(max-width: 991px) 100vw, 640px',
							)
						);
						?>
						<img class="grl-project__dots" src="<?php echo esc_url( grl_img( 'dots.png' ) ); ?>" alt="" width="101" height="188" loading="lazy">
						<span class="grl-project__hover">
							<span class="grl-project__hover-text"><?php echo esc_html( $grl_btn_label ); ?></span>
							<span class="grl-project__arrow"><img src="<?php echo esc_url( grl_img( 'arrow.svg' ) ); ?>" alt="" width="35" height="15"></span>
						</span>
					</a>
					<div class="grl-project__body">
						<p class="grl-project__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<span class="grl-btn-frame">
							<a class="grl-btn" href="<?php echo esc_url( $grl_link ); ?>"><?php echo esc_html( $grl_btn_label ); ?><span class="screen-reader-text">: <?php the_title(); ?></span></a>
						</span>
					</div>
				</article>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="grl-portfolio__empty"><?php echo esc_html( grl_opt( 'portfolio', 'empty' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section id="oskused" class="grl-skills" style="--grl-moon-spin: <?php echo esc_attr( absint( grl_opt( 'skills', 'spin' ) ) ); ?>s">
	<div class="grl-container grl-skills__grid">
		<div class="grl-skills__visual" data-grl-reveal>
			<h2 class="grl-display">
				<?php echo esc_html( grl_opt( 'skills', 'title_1' ) ); ?>
				<span class="grl-display__nowrap"><?php echo esc_html( grl_opt( 'skills', 'title_2' ) ); ?></span>
			</h2>
			<?php if ( $grl_moon_img ) : ?>
				<img class="grl-skills__moon" src="<?php echo esc_url( $grl_moon_img ); ?>" alt="" width="660" height="655" loading="lazy" decoding="async">
			<?php else : ?>
				<img class="grl-skills__moon" src="<?php echo esc_url( grl_img( 'moon.webp' ) ); ?>" srcset="<?php echo esc_url( grl_img( 'moon.webp' ) ); ?> 660w, <?php echo esc_url( grl_img( 'moon-2x.webp' ) ); ?> 1320w" sizes="660px" alt="" width="660" height="655" loading="lazy" decoding="async">
			<?php endif; ?>
		</div>
		<div class="grl-skills__list" data-grl-reveal>
			<?php foreach ( grl_opt_lines( 'skills', 'items' ) as $grl_item ) : ?>
				<div class="grl-skills__item">
					<h3 class="grl-skills__heading"><?php echo esc_html( $grl_item[0] ); ?></h3>
					<?php if ( ! empty( $grl_item[1] ) ) : ?>
						<p class="grl-skills__text"><?php echo esc_html( $grl_item[1] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
get_footer();
