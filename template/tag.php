<?php
/* only for default template */
add_action('gmedia_head', 'gmedia_default_template_styles');

/**
 * @var $gmedia
 */
get_gmedia_header(); ?>

	<header>
		<!-- <div class="site-title"><?php bloginfo('name'); ?></div> -->
		<div class="gmedia-header-title"><?php the_gmedia_title(); ?></div>
	</header>
	<div class="gmedia-main-wrapper">
		<?php
		/**
		 * @var $gmedia_id
		 */
		the_gmedia_content();
		?>
	</div>

<?php get_gmedia_footer(); ?>