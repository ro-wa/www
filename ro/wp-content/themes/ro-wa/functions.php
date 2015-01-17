<?php

if ( function_exists('register_sidebar') )
{
	register_sidebar(array(
		'before_widget' => '<div class="section widget %2$s" id="%1$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="section-title">',
		'after_title' => '</div>',
	));
}

function sm_site_description()
{
	if ( $descr = get_bloginfo('description') )
	{
		echo '<div id="site-description">' . $descr . '</div>' . "\n";
	}
}

function sm_loadsplash()
{
	if ( file_exists( TEMPLATEPATH . '/splash.php') ){
		load_template( TEMPLATEPATH . '/splash.php');
	}
}

function sm_splash()
{
	if ( !is_paged() )
	{
		if ( function_exists('is_front_page') && is_front_page() ){
			sm_loadsplash();
		}
		else if ( function_exists('is_home') && is_home() ){
			sm_loadsplash();
		}
	}
}

function sm_footerpages($args='depth=0', $separator = ' <span class="text-separator">|</span> ')
{
	$pages = get_pages($args);

	if ( $pages )
	{
		$page_links = array();

		foreach ( $pages as $page )
		{
			$page_links[] = '<a href="' . get_page_link($page->ID) . '">' . $page->post_title . '</a>';
		}

		echo join($separator, $page_links) . $separator;
	}
}



function sm_mainnav($args=array())
{
	$defaults = array(
		'depth'        => 1,
		'title_li'     => '',
		'sort_column'  => 'menu_order, post_title',
	);

	$args = array_merge($defaults, $args);

	if ( is_home() ){
		$home_link = '<li class="current_page_item"><a href="' . get_bloginfo('url') . '">' . __('Acasa') . '</a></li>';
	}
	else {
		$home_link = '<li><a href="' . get_bloginfo('url') . '">' . __('Acasa') . '</a></li>';
	}

	echo $home_link;
	wp_list_pages($args);
}

function sm_subnav()
{
	global $post, $wpdb;
	
	if ( is_page() )
	{
		$child_of = null;

		if ( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='page' && post_parent = ".$post->ID) > 0 ){
			$child_of = $post->ID;
		}
		else if ( $post->post_parent != 0 ){
			$child_of = $post->post_parent;
		}

		if ( !is_null($child_of) )
		{
			echo '<div class="navigation" id="sub-nav">' . "\n";
				echo '<ul class="tabbed">' . "\n";
					wp_list_pages('title_li=&child_of='.$child_of);
				echo '</ul>' . "\n";
				echo '<div class="clearer">&nbsp;</div>' . "\n";
			echo '</div>' . "\n";	
		}
	}
}




/*
	Comment author name fix
----------------------------------------------------------------- */

function sm_str_cut($str, $maxlen, $after='..'){
	return ( strlen($str) > $maxlen ) ? substr($str, 0, $maxlen-strlen($after)) . $after : $str;
}

function sm_comment_author_link($str){
	return sm_str_cut($str, 18);
}
add_filter('get_comment_author', 'sm_comment_author_link');


/*
	List comments
----------------------------------------------------------------- */
function sm_comment($comment, $args, $depth)
{
	$GLOBALS['comment'] = $comment;
	
	if ( $depth == 1 ) : ?>
	<li class="comment <?php if ( $args['has_children'] ) : ?>comment-parent<?php else : ?>comment-single<?php endif; ?>" id="comment-<?php comment_ID(); ?>">

		<div class="comment-profile-wrapper left">

			<div class="comment-profile">				
				<div class="comment-gravatar"><?php echo get_avatar($comment,40); ?></div>
				<div class="comment-author"><?php comment_author_link() ?></div>
			</div>

		</div>

		<div class="comment-content-wrapper right">
			<div class="comment-content-wrapper-2">

				<div class="comment-body">
				
					<div class="comment-arrow"></div>
					
					<div class="post-date">
						<div class="left"><?php comment_date('F jS, Y') ?> at <?php comment_time() ?><?php if ( $depth < $args['max_depth'] ) : ?> <span class="text-separator">|</span> <?php comment_reply_link(array_merge($args, array('reply_text' => 'Reply &#187;', 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?><?php endif; ?></div>
						<div class="right"><?php edit_comment_link('Edit', '', ' <span class="text-separator">|</span> '); ?><a href="#comment-<?php comment_ID() ?>" title="Permanent link to this reply">#<?php comment_ID() ?></a></div>
						<div class="clearer">&nbsp;</div>
					</div>

					<div class="comment-text">
<?php if ( $comment->comment_approved == '0' ) : ?>
					<p><em>Your comment is awaiting moderation.</em></p>
<?php endif; ?>
						<?php comment_text(); ?>			
						<div class="clearer">&nbsp;</div>
					</div>					

					<div class="clearer">&nbsp;</div>

				</div>
			
			</div>
		</div>

		<div class="clearer">&nbsp;</div>

	<?php else : ?>

	<li class="comment" id="comment-<?php comment_ID(); ?>">
		
		<div class="comment-content">

			<div class="comment-body">
			
				<div class="post-date">
					<div class="left"><?php echo get_avatar($comment, 14); ?> <span class="loud"><?php comment_author_link() ?></span> - <?php comment_date('F jS, Y') ?> at <?php comment_time() ?><?php if ( $depth < $args['max_depth'] ) : ?> <span class="text-separator">|</span> <?php comment_reply_link(array_merge($args, array('reply_text' => 'Reply &#187;', 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?><?php endif; ?></div>
					<div class="right"><?php edit_comment_link('Edit', '', ' <span class="text-separator">|</span> '); ?><a href="#comment-<?php comment_ID() ?>" title="Permanent link to this reply">#<?php comment_ID() ?></a></div>
					<div class="clearer">&nbsp;</div>
				</div>

	<?php if ( $comment->comment_approved == '0' ) : ?>
				<p><em>Your comment is awaiting moderation.</em></p>
	<?php endif; ?>

				<div class="comment-text">
					<?php comment_text(); ?>					
					<div class="clearer">&nbsp;</div>
				</div>

			</div>				

		</div>
		
	<?php endif; ?>
<?php
}


/*
	Page navigation
----------------------------------------------------------------- */

function sm_page_navigation($object_type, $position=false)
{
	if ( $object_type == 'comments' )
	{
		$next = get_next_comments_link();
		$prev = get_previous_comments_link();
	}
	else if ( $object_type == 'archive' )
	{
		$next = get_next_posts_link();
		$prev = get_previous_posts_link();
	}

	if ( $next || $prev ) {
		if ( !$next ) $next = '&nbsp;';
		if ( !$prev ) $prev = '&nbsp;';
	?>
	<div class="pagination <?php echo $object_type; ?>-pagination <?php if ( $position ) : ?>pagination-<?php echo $position; endif; ?>">

		<div class="left"><?php echo $prev; ?>&nbsp;</div>
		<div class="right">&nbsp;<?php echo $next; ?></div>

		<div class="clearer">&nbsp;</div>

	</div>
	<?php
	}
	else {
		echo '<div class="archive-separator"></div>' . "\n";
	}
}


?>