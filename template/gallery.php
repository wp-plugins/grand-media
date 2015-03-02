<?php get_gmedia_header();

/**
 * @var $gmedia
 */
?>

<header>
	<!-- <div class="site-title"><?php bloginfo('name'); ?></div> -->
	<div class="gmedia-header-title"><?php the_gmedia_title(); ?></div>
	<?php if($gmedia->description){ ?>
		<div class="gmedia-header-description"><?php echo $gmedia->description; ?></div>
		<span class="gmedia-header-description-button" onclick="jQuery('.gmedia-header-description').toggle()"></span>
	<?php }
	gmediacloud_social_sharing();
	?>
</header>

<div class="gmedia-main-wrapper">
	<?php the_gmedia_content(); ?>
</div>

<?php get_gmedia_footer(); ?>