<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>
<script type="text/javascript"> 
function mailfaraspam(aUser, aDomain) { document.location = "mailto:" + aUser + "@" + aDomain; }
</script>
</head>

<body>
<!-- facebook SDK --> 
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=151031561658454&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!-- end facebook SDK --> 

<div id="site-wrapper">

	<div id="header">

		<div id="top">

			<div class="left" id="logo">
				<a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo2.gif" alt="<?php bloginfo('title'); ?>" /></a>

				<?php // sm_site_description(); ?>

			</div>

			<div class="navigation left" id="main-nav">				

				<ul class="tabbed">
					<?php sm_mainnav(); ?>
				</ul>
				<div class="clearer">&nbsp;</div>
			</div>
			<div class="clearer">&nbsp;</div>

		</div>

		<?php /*  sm_subnav(); */ ?>

	</div>

	<?php sm_splash(); ?>

	<div class="main" id="main-two-columns">

		<div class="left" id="main-content">