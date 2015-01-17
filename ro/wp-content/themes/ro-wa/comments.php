<?php

// Do not delete these lines
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');

if ( function_exists('post_password_required') && post_password_required() ) { ?>
	<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
<?php
	return;
}

?>

<div id="comments">

<?php if ( have_comments() ) : ?>

	<div class="left">
		<h2>Comentarii: <?php echo $post->comment_count; ?></h2>
	</div>
	<h3 class="right">
	<?php if ($post->comment_status == 'open') : ?>
		<a href="#respond">Comenteaza &#187;</a>
	<?php else : ?>
		<span class="large quiet">(comments are closed)</span>
	<?php endif; ?>
	</h3>

	<div class="clearer">&nbsp;</div>

	<?php sm_page_navigation('comments', 'top'); ?>

	<div class="comment-list-wrapper">

		<ul class="comment-list">
			<?php wp_list_comments('callback=sm_comment&'); ?>
		</ul>

	</div>

	<?php sm_page_navigation('comments', 'bottom'); ?>

<?php elseif ( $post->comment_status != 'open' ) : ?>

	<p class="large quiet">&nbsp;</p>

<?php else : ?>

	<h3>Comentarii</h3>
	<p class="large quiet">Nici un comentariu deocamdata.</p>

<?php endif; ?>

</div>

<?php if ($post->comment_status == 'open') : ?>

<div id="respond">

	<ul>
		<li>

			<div class="legend" id="comment-form-title">
				<div class="left"><?php comment_form_title('Comenteaza', 'Raspunde la %s' ); ?></div>
				<div class="right"><?php cancel_comment_reply_link('anuleaza raspunsul'); ?></div>
				<div class="clearer">&nbsp;</div>
			</div>

			<div class="comment-profile-wrapper left">

				<div class="comment-profile">				
					<div class="comment-gravatar"><img src="<?php bloginfo('stylesheet_directory'); ?>/img/comment-gravatar.gif" width="40" height="40" alt="Your gravatar" /></div>
					<div class="comment-author">Numele tau</div>
				</div>

			</div>

			<div class="comment-content-wrapper">
					
				<div class="comment-body">
				
					<div class="comment-arrow"></div>
						
			<?php if ( get_option('comment_registration') && !$user_ID ) : ?>

					<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">logged in</a> to post a comment.</p>

			<?php else : ?>

					<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

						<fieldset>
							
							<div class="form-row comment-input-text"><textarea name="comment" id="comment" cols="10" rows="10" tabindex="4"></textarea></div>

							<div class="form-row comment-input-name">

				<?php if ( $user_ID ) : ?>
								Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a>
				<?php else : ?>				
								<div class="form-property required"><label for="author">Name *</label></div>
								<div class="form-value"><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="28" tabindex="1" class="text" /></div>

								<div class="clearer">&nbsp;</div>

				<?php endif; ?>
							</div>

				<?php if ( !$user_ID ) : ?>

							<div class="form-row comment-input-email">

								<div class="form-property required"><label for="email">Email *</label></div>
								<div class="form-value"><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="28" tabindex="2" class="text" /></div>
								
								<div class="clearer">&nbsp;</div>

							</div>

							<div class="form-row comment-input-website">

								<div class="form-property"><label for="url">Website</label></div>					
								<div class="form-value"><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="28" tabindex="3" class="text" /></div>

								<div class="clearer">&nbsp;</div>

							</div>

				<?php endif; ?>

							<div class="form-row form-row-submit">														
								<input type="submit" class="button" value="Trimite Comentariu" />
							</div>

								<!-- <p><strong>XHTML:</strong> You can use these tags: <code><?php echo allowed_tags(); ?></code></p> -->

							<div><?php comment_id_fields(); ?></div>

						</fieldset>

					</form>

				</div>

				<?php do_action('comment_form', $post->ID); ?>

			<?php endif; // If registration required and not logged in ?>

				<div class="clearer">&nbsp;</div>

			</div>

			<div class="clearer">&nbsp;</div>
			
		</li>
	</ul>
</div>

<?php endif; // if you delete this the sky will fall on your head ?>
