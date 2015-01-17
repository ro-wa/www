<?php get_header(); ?>

		<?php if (have_posts()) : ?>

 	  <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
 	  <?php /* If this is a category archive */ if (is_category()) { ?>
			<h1><?php single_cat_title(); ?></h1>
			<div class="title-description"><?php echo category_description(); ?></div>
 	  <?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
			<h1><?php single_tag_title(); ?></h1>
			<div class="title-description">Articole cu eticheta &#8216;<?php single_tag_title(); ?>&#8217;</div>
 	  <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
			<h1>Arhiva pentru<?php the_time('F jS, Y'); ?></h1>
 	  <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
			<h1>Arhiva pentru<?php the_time('F, Y'); ?></h1>
 	  <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
			<h1>Archiva pentru<?php the_time('Y'); ?></h1>
	  <?php /* If this is an author archive */ } elseif (is_author()) { ?>
			<h1>Arhiva autor</h1>
			<div class="title-description">Articole scrise de <?php wp_title(''); ?></div>
 	  <?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
			<h1>Arhive</h1>
 	  <?php } ?>

			<?php sm_page_navigation('archive', 'top'); ?>

		<?php $count = count($posts); $i=0; while (have_posts()) : the_post(); $i++; ?>
			<div class="post">

				<div class="post-title" id="post-<?php the_ID(); ?>">
					<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Link permanent la <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
				</div>

				<div class="post-date">
					<div class="left"><?php the_time('F jS, Y') ?> in <?php the_category(' / ') ?><?php edit_post_link('Editare', ' <span class="text-separator">|</span> ', ''); ?></div>
					<div class="right"><?php comments_popup_link('0 Comentarii &#187;', '1 Comentariu &#187;', '% Commentarii &#187;', 'icon icon-comment'); ?></div>
					<div class="clearer">&nbsp;</div>
				</div>

			</div>

			<?php if ( $i != $count ) : ?>

			<div class="archive-separator"></div>
			<?php endif; ?>

		<?php endwhile; ?>

			<?php sm_page_navigation('archive', 'bottom'); ?>

	<?php else :

		if ( is_category() ) { // If this is a category archive
			printf("<h2 class='center'>Ne pare rau, nu exista nici un articol in categoria %s.</h2>", single_cat_title('',false));
		} else if ( is_date() ) { // If this is a date archive
			echo("<h2>Ne pare rau, nu exista nici un articol cu aceasta data.</h2>");
		} else if ( is_author() ) { // If this is a category archive
			$userdata = get_userdatabylogin(get_query_var('author_name'));
			printf("<h2 class='center'>Ne pare rau, nu exista nici un articol scris de %s.</h2>", $userdata->display_name);
		} else {
			echo("<h2 class='center'>Ne pare rau, nu am gasit nici un articol.</h2>");
		}
		// get_search_form();

	endif;
?>
		<?php get_sidebar(); ?>

<?php get_footer(); ?>
