<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<div class="post" id="post-<?php the_ID(); ?>">

				<div class="post-title"><h1><?php the_title(); ?></h1></div>

				<div class="post-date">
					<div class="left"><?php the_time('F jS, Y') ?> by <?php the_author(); ?> in <?php the_category(' / ') ?><?php edit_post_link('Edit this entry', ' <span class="text-separator">|</span>', ''); ?></div>
					<div class="right"><a href="#comments" class="icon icon-comment">Comentarii (<?php echo $post->comment_count; ?>) &darr;</a></div>
					<div class="clearer">&nbsp;</div>
				</div>

				<div class="post-body">
					<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>
					<div class="clearer">&nbsp;</div>

					<?php the_tags( '<p>Tags: ', ', ', '</p>'); ?>

					<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>')); ?>

				</div>

			</div>

		<?php endwhile; ?>

			<?php sm_page_navigation('posts'); ?>

			<?php comments_template(); ?>

	<?php else: ?>

			<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>

		<?php get_sidebar(); ?>

<?php get_footer(); ?>
