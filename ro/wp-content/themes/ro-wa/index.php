<?php get_header(); ?>

	<?php if (have_posts()) : ?>

		<div class="section-title"><?php echo __('Anunturi'); ?></div>

		<?php if ( is_paged() ) { sm_page_navigation('archive', 'top'); } ?>

		<?php $count = count($posts); $i=0; while (have_posts()) : the_post(); $i++; ?>

			<div class="post" id="post-<?php the_ID(); ?>">
				
				<div class="post-title"><h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Link permanent catre <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2></div>
				
				<div class="post-date">
					<div class="left"><?php the_time('F jS, Y') ?> by <?php the_author(); ?> in <?php the_category(' / ') ?><?php edit_post_link('Editare', ' <span class="text-separator">|</span> ', ''); ?></div>
					<div class="right"><?php comments_popup_link('0 Comentarii &rarr;', '1 Comentariu &#187;', '% Comentarii &#187;', 'icon icon-comment'); ?></div>
					<div class="clearer">&nbsp;</div>
				</div>

				<div class="post-body">
					<?php the_content('Citeste restul articolului &#187;'); ?>

					<div class="clearer">&nbsp;</div>

					<?php wp_link_pages(array('inainte' => '<p><strong>Pagini:</strong> ', 'dupa' => '</p>')); ?>
				</div>

			</div>

			<?php if ( $i != $count ) :?>

			<div class="content-separator"></div>
			<?php endif; ?>

		<?php endwhile; ?>

		<?php sm_page_navigation('archive', 'bottom'); ?>

	<?php else : ?>

		<h2 class="center">Nu am gasit</h2>
		<p class="center">Ne pare rau, nu am gasit ce cautai..</p>
		<?php get_search_form(); ?>

	<?php endif; ?>

		<?php get_sidebar(); ?>

<?php get_footer(); ?>
