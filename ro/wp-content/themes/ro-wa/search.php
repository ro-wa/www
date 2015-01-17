<?php get_header(); ?>

	<?php if (have_posts()) : ?>

				<h1>Search Results</h1>

				<p class="title-description">Posts matching &quot;<?php the_search_query() ?>&quot;.</p>

				<div class="pagination pagination-top">

					<div class="left"><?php next_posts_link('&laquo; Older Entries') ?></div>
					<div class="right"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
					
					<div class="clearer">&nbsp;</div>

				</div>

		<?php while (have_posts()) : the_post(); ?>
				<div class="post">

					<div class="post-title" id="post-<?php the_ID(); ?>">
						<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					</div>

					<div class="post-date">
						<div class="left"><?php the_time('F jS, Y') ?> in <?php the_category(' / ', 'multiple') ?><?php edit_post_link('Edit', ' <span class="text-separator">|</span> ', ''); ?></div>
						<div class="right"><?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></div>
						<div class="clearer">&nbsp;</div>
					</div>

					<div class="post-body">

						<?php the_excerpt(); ?>
						<div class="clearer">&nbsp;</div>

					</div>

				</div>

				<div class="archive-separator"></div>

		<?php endwhile; ?>

				<div class="pagination pagination-bottom">

					<div class="left"><?php next_posts_link('&laquo; Older Entries') ?></div>
					<div class="right"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
					
					<div class="clearer">&nbsp;</div>

				</div>

	<?php else : ?>

			<h1>No posts found. Try a different search?</h1>

	<?php endif; ?>

		<?php get_sidebar(); ?>

<?php get_footer(); ?>

