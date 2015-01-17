<?php
/* 
Plugin Name: Flickr Photostream
Plugin URI: http://miromannino.it/projects/flickr-photostream/
Description: Shows the flickr photostream, sets and galleries, with an high quality justified gallery.
Version: 3.1.6
Author: Miro Mannino
Author URI: http://miromannino.it/about-me/

Copyright 2012 Miro Mannino (miro.mannino@gmail.com)
thanks to Dan Coulter for phpFlickr Class (dan@dancoulter.com)

This file is part of Flickr Photostream.

Flickr Photostream is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later version.

Flickr Photostream is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Flickr 
Photostream Wordpress Plugin. If not, see <http://www.gnu.org/licenses/>.
*/

//Defaults
$flickr_photostream_imagesHeight_default = '120';
$flickr_photostream_maxPhotosPP_default = '20';
$flickr_photostream_lastRow_default = 'justify';
$flickr_photostream_fixedHeight_default = '0';
$flickr_photostream_pagination_default = 'none';
$flickr_photostream_lightbox_default = 'none';
$flickr_photostream_captions_default = '1';
$flickr_photostream_randomize_default = '0';
$flickr_photostream_margins_default = '1';
$flickr_photostream_openOriginals_default = '0';
$flickr_photostream_bcontextmenu_default = '0';

//Add the link to the plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flickrps_plugin_settings_link' );
function flickrps_plugin_settings_link($links) { 
	$settings_link = '<a href="options-general.php?page=flickrps.php">Settings</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}

//Activation hook, we check that the upload dir is writable
register_activation_hook( __FILE__ , 'flickrps_plugin_activate');
if (!function_exists( 'flickrps_plugin_uninstall')) {
	function flickrps_plugin_activate() {
		$upload_dir = wp_upload_dir();
		@mkdir($upload_dir['basedir'].'/phpFlickrCache');
		if (!is_writable($upload_dir['basedir'].'/phpFlickrCache')) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			wp_die(__('Flickr Photostream can\'t be activated: the cache Folder is not writable', 'flickr-photostream') 
				. ' (' . $upload_dir['basedir'] .'/phpFlickrCache' . ')'
			);
		}
	}
}

//Add the language and the permalink
add_action('init', 'flickrps_init');
function flickrps_init() {
	/* languages */
	load_plugin_textdomain('flickr-photostream', false, dirname(plugin_basename( __FILE__ )) . '/languages/');
}

//Register with hook 'wp_enqueue_scripts' which can be used for front end CSS and JavaScript
add_action('wp_enqueue_scripts', 'flickrps_addCSSandJS');
function flickrps_addCSSandJS() {
	wp_register_style('justifiedGalleryCSS', plugins_url('justifiedgallery/css/justifiedGallery.min.css', __FILE__));
	wp_register_style('swipeboxCSS', plugins_url('swipebox/css/swipebox.min.css', __FILE__));
	
	wp_register_script('justifiedGalleryJS', plugins_url('justifiedgallery/js/jquery.justifiedGallery.min.js', __FILE__));
	wp_register_script('swipeboxJS', plugins_url('swipebox/js/jquery.swipebox.min.js', __FILE__));

	wp_enqueue_style('justifiedGalleryCSS');
	wp_enqueue_style('swipeboxCSS');
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('justifiedGalleryJS');
	wp_enqueue_script('swipeboxJS');
}

function flickrps_formatError($errorMsg) {
	return '<div class="flickrps-error"><span style="color:red">' 
		. __('FlickrPhotostream error', 'flickr-photostream') 
		. ': </span><span class="flickrps-error-msg">' . $errorMsg . '</span></div>';
}

function flickrps_formatFlickrAPIError($errorMsg) {
	return '<div class="flickrps-error"><span style="color:red">' 
		. __('Flickr API error', 'flickr-photostream') 
		. ': </span><span class="flickrps-error-msg">' . $errorMsg . '</span></div>';
}

function flickrps_createGallery($action, $atts) {
	global $flickr_photostream_imagesHeight_default;
	global $flickr_photostream_maxPhotosPP_default;
	global $flickr_photostream_lastRow_default;
	global $flickr_photostream_fixedHeight_default;
	global $flickr_photostream_pagination_default;
	global $flickr_photostream_lightbox_default;
	global $flickr_photostream_captions_default;
	global $flickr_photostream_randomize_default;
	global $flickr_photostream_margins_default;
	global $flickr_photostream_openOriginals_default;
	global $flickr_photostream_bcontextmenu_default;
	static $shortcode_unique_id = 0;
	$ris = "";
	
	require_once("phpFlickr/phpFlickr.php");

	$page_num = (get_query_var('page')) ? get_query_var('page') : 1;

	//Options-----------------------
	extract( shortcode_atts( array(
		//left value: the variable to set (e.g. user_id option in shortcode set the variable $user_id in the function scope)
		//right value: the default value, in our case we take this values from the options where we store them.
		'user_id' => get_option('$flickr_photostream_userID'),
		'id' => NULL,
		'tags' => NULL,
		'tags_mode' => 'any',
		'images_height' => get_option('$flickr_photostream_imagesHeight', $flickr_photostream_imagesHeight_default), // Flickr images size
		'max_num_photos' => get_option('$flickr_photostream_maxPhotosPP', $flickr_photostream_maxPhotosPP_default), // Max number of Photos	
		'last_row' => get_option('$flickr_photostream_lastRow', $flickr_photostream_lastRow_default),
		'fixed_height' => get_option('$flickr_photostream_fixedHeight', $flickr_photostream_fixedHeight_default) == 1,
		'lightbox' => get_option('$flickr_photostream_lightbox', $flickr_photostream_lightbox_default),
		'captions' => get_option('$flickr_photostream_captions', $flickr_photostream_captions_default) == 1,
		'randomize' => get_option('$flickr_photostream_randomize', $flickr_photostream_randomize_default) == 1,
		'pagination' => get_option('$flickr_photostream_pagination', $flickr_photostream_pagination_default),
		'margins' => get_option('$flickr_photostream_margins', $flickr_photostream_margins_default),
		'open_originals' => get_option('$flickr_photostream_openOriginals', $flickr_photostream_openOriginals_default) == 1,
		'block_contextmenu' => get_option('$flickr_photostream_bcontextmenu', $flickr_photostream_bcontextmenu_default) == 1
	), $atts ) );

	//LEGACY for the old options
	if($pagination === '1') $pagination = 'prevnext';
	else if ($pagination !== 'none' && $pagination !== 'prevnext' && $pagination !== 'numbers') $pagination = 'none';
	if($lightbox === '1') $lightbox = 'colorbox';
	if($lightbox === '0') $lightbox = 'none';

	$images_height = (int)$images_height;
	if($images_height < 30) $images_height = 30;

	$max_num_photos = (int)$max_num_photos;
	if ($max_num_photos < 1) $max_num_photos = 1;

	$margins = (int)$margins;
	if ($margins < 0) $margins = 1;
	if ($margins > 30) $margins = 30;

	if($pagination === 'none') $page_num = 1;

	//-----------------------------

	//Inizialization---------------
	$flickrAPIKey = get_option('$flickr_photostream_APIKey'); //Flickr API Key
	
	$f = new phpFlickr($flickrAPIKey);
	$upload_dir = wp_upload_dir();
	$f->enableCache("fs", $upload_dir['basedir']."/phpFlickrCache");

	$photos_url = array();
	$photos = array();
	$photos_main_index = '';

	$target_blank = true; //TODO in the settings page?
	$maximum_pages_nums = 10; //TODO configurable?

	//Errors-----------------------
	if ($action === 'phs' || $action === 'gal' || $action === 'tag') {
		if (!isset($user_id) || strlen($user_id) == 0) 
			return(flickrps_formatError(__('You must specify the user_id for this action, using the "user_id" attribute', 'flickr-photostream')));	
	}

	if ($action === 'gal') {
		if (!isset($id) || strlen($id) == 0) 
			return(flickrps_formatError(__('You must specify the id of the gallery, using the "id" attribute', 'flickr-photostream')));	
	}

	if ($action === 'set') {
		if (!isset($id) || strlen($id) == 0) 
			return(flickrps_formatError(__('You must specify the id of the set, using the "id" attribute', 'flickr-photostream')));	
	}

	if ($action === 'tag') {
		if (!isset($tags) || strlen($tags) == 0) 
			return(flickrps_formatError(__('You must specify the tags using the "tags" attribute', 'flickr-photostream')));
		if ($tags_mode !== 'any' && $tags_mode !== 'all') 
			return(flickrps_formatError(__('You must specify a valid tags_mode: "any" or "all"', 'flickr-photostream')));
	}

	if ($action === 'grp') {
		if (!isset($id) || strlen($id) == 0) 
			return(flickrps_formatError(__('You must specify the id of the group, using the "id" attribute', 'flickr-photostream')));	
	}

	if ($pagination !== 'none' && $pagination !== 'prevnext' && $pagination !== 'numbers') {
		return(flickrps_formatError(__('The pagination attribute can be only "none", "prevnext" or "numbers".', 'flickr-photostream')));		
	}

	if ($last_row !== 'hide' && $last_row !== 'justify' && $last_row !== 'nojustify') {
		return(flickrps_formatError(__('The last_row attribute can be only "hide", "justify" or "nojustify".', 'flickr-photostream')));		
	}

	if ($lightbox !== 'none' && $lightbox !== 'colorbox' && $lightbox !== 'swipebox') {
		return(flickrps_formatError(__('The lightbox attribute can be only "none", "colorbox" or "swipebox".', 'flickr-photostream')));		
	}

	//Photo loading----------------
	$extras = "description, original_format, url_l, url_z";
	if ($action === 'set') {
		//Show the photos of a particular photoset
		$photos = $f->photosets_getPhotos($id, $extras, 1, $max_num_photos, $page_num, NULL);	
		$photos_main_index = 'photoset';
	} else if ($action === 'gal') {
		//Show the photos of a particular gallery
		$photos_url[$user_id] = $f->urls_getUserPhotos($user_id);
		if ($f->getErrorCode() != NULL) return(flickrps_formatFlickrAPIError($f->getErrorMsg()));

		$gallery_info = $f->urls_lookupGallery($photos_url[$user_id] . 'galleries/' . $id);
		if ($f->getErrorCode() != NULL) return(flickrps_formatFlickrAPIError($f->getErrorMsg()));

		$photos = $f->galleries_getPhotos($gallery_info['gallery']['id'], $extras, $max_num_photos, $page_num);	

		$photos_main_index = 'photos';
	} else if ($action === 'tag') {
		$photos = $f->photos_search(array(
			'user_id' => $user_id,
			'tags' => $tags,
			'tag_mode' => $tags_mode,
			'extras' => $extras,
			'per_page' => $max_num_photos, 
			'page' => $page_num
		));
		$photos_main_index = 'photos';
	} else if ($action === 'grp') {
		//Show the photos of a particular group pool
		//groups_pools_getPhotos ($group_id, $tags = NULL, $user_id = NULL, $jump_to = NULL, $extras = NULL, $per_page = NULL, $page = NULL) {
		$photos = $f->groups_pools_getPhotos($id, $tags, NULL, NULL, $extras, $max_num_photos, $page_num);
		$photos_main_index = 'photos';
	} else {
		//Show the classic photostream
		$photos = $f->people_getPublicPhotos($user_id, NULL, $extras, $max_num_photos, $page_num);
			
		//Need the authentication (TODO)
		//$photos = $f->people_getPhotos($user_id, 
		//	array("privacy_filter" => "1", "extras" => "description", "per_page" => $max_num_photos, "page" => $page_num));

		$photos_main_index = 'photos';
	}

	if ($f->getErrorCode() != NULL) return(flickrps_formatFlickrAPIError($f->getErrorMsg()));

	if(count((array)$photos[$photos_main_index]['photo']) == 0) return(__('No photos', 'flickr-photostream'));

	//we calculate that the aspect ratio has an average of 4:3
	if($images_height <= 75) {
		$imgSize = "thumbnail"; //thumbnail (longest side:100)
	}else if($images_height <= 180) {
		$imgSize = "small"; //small (longest side:240)
	}else{ //if <= 240
		$imgSize = "small_320"; //small (longest side:320)
	}

	$ris .= '<!-- Flickr Photostream by Miro Mannino -->' . "\n" .	'<div id="flickrGal' . $shortcode_unique_id . '" class="justified-gallery" >';

	$r = 0;

	$use_large_thumbnails = true;

	$photo_array = $photos[$photos_main_index]['photo'];
	foreach ($photo_array as $photo) {

		if (!isset($photo['url_l'])) {
			$use_large_thumbnails = false;
		}

		if ($lightbox !== 'none') {
			$ris .=	'<a href="';

			if($open_originals) {
				if (isset($photo['originalsecret'])) {
					$ris .= $f->buildPhotoURL($photo, "original");
				} else if (isset($photo['url_l'])) {
					$ris .= $photo['url_l'];
				} else {
					$ris .= $photo['url_z'];
				}
			} else {
				if (isset($photo['url_l'])) {
					$ris .= $photo['url_l'];
				} else {
					$ris .= $photo['url_z'];
				}
			}

			$ris .= '" rel="flickrGal' . $shortcode_unique_id 
					 .	'" title="' . $photo['title'] 
				 	 .	'">';	
			
		} else {

			//If it is a gallery the photo has an owner, else is the photoset owner (or the photostream owner)
			$photo_owner = isset($photo['owner']) ? $photo['owner'] : $photos[$photos_main_index]['owner'];

			//Save the owner url
			if (!isset($photos_url[$photo_owner])) {
				$photos_url[$photo_owner] = $f->urls_getUserPhotos($photo_owner);
				if ($f->getErrorCode() != NULL) return(flickrps_formatFlickrAPIError($f->getErrorMsg()));
			}

			if ($action === 'set') {
				$photos_url_in = '/in/set-' . $id . '/lightbox';
			} else {
				$photos_url_in = '/in/photostream/lightbox';
			}

			$ris .= '<a href="' . $photos_url[$photo_owner] . $photo['id'] . $photos_url_in . '" ';
			if ($target_blank) $ris .= 'target="_blank" ';
			$ris .= 'title="' . $photo['title'] . '">';
		
		}
		
		$ris .= '<img alt="' . htmlspecialchars($photo['title'], ENT_QUOTES, 'UTF-8') 
				 .	'" src="' . $f->buildPhotoURL($photo, $imgSize)
				 .	'" data-safe-src="' . $f->buildPhotoURL($photo, $imgSize) . '" /></a>';
		
	}

	$ris .= '</div>'
			 .	'<script type="text/javascript">';

	if ($block_contextmenu) {
		$ris .= '	function fpDisableContextMenu(imgs) {
								function absorbEvent_(event) {
									var e = event || window.event;
									e.preventDefault && e.preventDefault();
									e.stopPropagation && e.stopPropagation();
									e.cancelBubble = true;
									e.returnValue = false;
									return false;
								}

								imgs.on("contextmenu", absorbEvent_);
								imgs.on("ontouchstart", absorbEvent_);
								imgs.on("ontouchmove", absorbEvent_);
								imgs.on("ontouchend", absorbEvent_);
								imgs.on("ontouchcancel", absorbEvent_);
							}';
	}

	$ris .= 'jQuery(document).ready(function(){ jQuery("#flickrGal' . $shortcode_unique_id . '")';


	if ($lightbox === 'colorbox') {
		$ris .= '.on(\'jg.rowflush\', function() {
							jQuery(this).find("> a").colorbox({
								maxWidth : "85%",
								maxHeight : "85%",
								current : "",';

		if ($block_contextmenu) {
			$ris .= 	'	onComplete: function() {
										fpDisableContextMenu(jQuery("#colorbox .cboxPhoto"));
									}';
		}

		$ris .=		'});
						})';
	} else if ($lightbox === 'swipebox') {
		$ris .= '	.on(\'jg.complete\', function() {
								jQuery("#flickrGal' . $shortcode_unique_id . '").find("> a").swipebox({
										afterOpen : function () { 
											setTimeout(function() {
												fpDisableContextMenu(jQuery("#swipebox-overlay .slide img"));
											}, 100);
										}
								});
							})';
	}

	$ris .= '.justifiedGallery({'
			 .	'\'lastRow\': \'' . $last_row . '\', '
			 .	'\'rowHeight\':' . $images_height . ', '
			 .	'\'fixedHeight\':' . ($fixed_height ? 'true' : 'false') . ', '		 
			 .	'\'captions\':' . ($captions ? 'true' : 'false') . ', '
			 .	'\'randomize\':' . ($randomize ? 'true' : 'false') . ', '
			 .	'\'margins\':' . $margins;
	
	if (!$use_large_thumbnails) {
		$ris .= ', \'sizeRangeSuffixes\': {
								\'lt100\':\'_t\',
								\'lt240\':\'_m\',
								\'lt320\':\'_n\',
								\'lt500\':\'\',
								\'lt640\':\'_z\',
								\'lt1024\':\'_z\'
							}';
	}

	$ris .= '});';

	if ($block_contextmenu) {
		$ris .= 'fpDisableContextMenu(jQuery("#flickrGal' . $shortcode_unique_id . '").find("> a"));';
	}
	
	$ris .= ' });'
			 .	'</script>';

	//Navigation---------------------
	if($pagination !== 'none') {
		
		$num_pages = $photos[$photos_main_index]['pages'];

		if ($num_pages > 1) {

			$permalink = get_permalink();
		
			if ($pagination === 'numbers') {
					
				$ris .= '<div class="page-links">'
						 .	'<span class="page-links-title">Pages:</span> ';

				$low_num = $page_num - floor($maximum_pages_nums/2);
				$high_num = $page_num + ceil($maximum_pages_nums/2) - 1;

				if ($low_num < 1) {
					$high_num += 1 - $low_num; 
					$low_num = 1;
				}

				if ($high_num > $num_pages) {
					$high_num = $num_pages;
				}

				if ($low_num > 1) {
					$ris .= '<a href="' . add_query_arg('page', ($low_num - 1), $permalink) . '"><span>...</span></a> ';
				}

				for ($i = $low_num; $i <= $high_num; $i++) {
					if ($i == $page_num) $ris .= '<span>' . $i . '</span> ';
					else {
						$ris .= '<a href="' . add_query_arg('page', $i, $permalink) . '"><span>' . $i . '</span></a> ';
					}
				}

				if ($high_num < $num_pages) {
					$ris .= '<a href="' . add_query_arg('page', ($high_num + 1), $permalink) . '"><span>...</span></a> ';
				}

				$ris .= '</div>';

			} else if ($pagination === 'prevnext') {
					
				$ris .= '<div>';

				if ($page_num < $num_pages) {
					$ris .= '<div class="nav-previous">'
					 .	'<a href="' . add_query_arg('page', ((int)$page_num + 1), $permalink) . '">' . __('<span class="meta-nav">&larr;</span> Older photos', 'flickr-photostream') . '</a>'
					 .	'</div>';
				}

				if ($page_num > 1) { //a link to the newer photos
					$ris .= '<div class="nav-next">'
					 .	'<a href="' . add_query_arg('page', ((int)$page_num - 1), $permalink) . '">' . __('Newer photos <span class="meta-nav">&rarr;</span>', 'flickr-photostream') . '</a>'
					 .	'</div>';	
				}

				$ris .= '</div>';

			}
		}
	}

	$shortcode_unique_id++;
	return($ris);
}

//[flickr_photostream user_id="..." ...]
function flickr_photostream($atts, $content = null) {
	return flickrps_createGallery('phs', $atts);
}
add_shortcode('flickr_photostream', 'flickr_photostream');
add_shortcode('flickrps', 'flickr_photostream'); //TODO to remove, legacy code

//[flickr_set id="..." ...]
function flickr_set($atts, $content = null) {
	return flickrps_createGallery('set', $atts);	
}
add_shortcode('flickr_set', 'flickr_set');

//[flickr_gallery user_id="..." id="..." ...]
function flickr_gallery($atts, $content = null) {
	return flickrps_createGallery('gal', $atts);		
}
add_shortcode('flickr_gallery', 'flickr_gallery');

//[flickr_tags user_id="..." tags="..." tags_mode="any/all" ...]
function flickr_tags($atts, $content = null) {
	return flickrps_createGallery('tag', $atts);
}
add_shortcode('flickr_tags', 'flickr_tags');

//[flickr_group id="..."]
function flickr_group($atts, $content = null) {
	return flickrps_createGallery('grp', $atts);
}
add_shortcode('flickr_group', 'flickr_group');


//Options
include("flickr-photostream-setting.php");

?>