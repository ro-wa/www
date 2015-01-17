<?php
/* 
Flickr Photostream
Version: 3.1.6
Author: Miro Mannino
Author URI: http://miromannino.it

Copyright 2012 Miro Mannino (miro.mannino@gmail.com)
thanks to Dan Coulter for phpFlickr Class (dan@dancoulter.com)

This file is part of Flickr Photostream.

Flickr Photostream is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later version.

Flickr Photostream is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Flickr 
Photostream Wordpress Plugin.  If not, see <http://www.gnu.org/licenses/>.
*/

//uninstall plugin, remove the options for privacy
register_uninstall_hook( __FILE__, 'flickrps_plugin_uninstall');
if (!function_exists( 'flickrps_plugin_uninstall')) {
	function flickrps_plugin_uninstall() {
		if (get_option('$flickr_photostream_userID')) {
			delete_option('$flickr_photostream_userID');
		}
		if (get_option('$flickr_photostream_APIKey')) {
			delete_option('$flickr_photostream_APIKey');
		}
		if (get_option('$flickr_photostream_maxPhotosPP')) {
			delete_option('$flickr_photostream_maxPhotosPP');
		}
		if (get_option('$flickr_photostream_imagesHeight')) {
			delete_option('$flickr_photostream_imagesHeight');
		}
		if (get_option('$flickr_photostream_lastRow')) {
			delete_option('$flickr_photostream_lastRow');
		}
		if (get_option('$flickr_photostream_fixedHeight')) {
			delete_option('$flickr_photostream_fixedHeight');
		}
		if (get_option('$flickr_photostream_pagination')) {
			delete_option('$flickr_photostream_pagination');
		}
		if (get_option('$flickr_photostream_lightbox')) {
			delete_option('$flickr_photostream_lightbox');
		}
		if (get_option('$flickr_photostream_captions')) {
			delete_option('$flickr_photostream_captions');
		}
		if (get_option('$flickr_photostream_randomize')) {
			delete_option('$flickr_photostream_randomize');
		}
		if (get_option('$flickr_photostream_margins')) {
			delete_option('$flickr_photostream_margins');
		}
		if (get_option('$flickr_photostream_openOriginals')) {
			delete_option('$flickr_photostream_openOriginals');
		}
		if (get_option('$flickr_photostream_bcontextmenu')) {
			delete_option('$flickr_photostream_bcontextmenu');
		}
	}
}

// add the admin options page
add_action('admin_menu', 'flickr_photostream_admin_add_page');
function flickr_photostream_admin_add_page() {
	add_options_page('FlickrPhotostreamSettings', 'Flickr Photostream', 'activate_plugins', 'flickrps', 'flickr_photostream_setting');
}

function flickr_photostream_setting() {
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

	//Get Values
	$flickr_photostream_userID_saved = get_option('$flickr_photostream_userID', "");
	$flickr_photostream_APIKey_saved = get_option('$flickr_photostream_APIKey', "");
	$flickr_photostream_imagesHeight_saved = (int)get_option('$flickr_photostream_imagesHeight', $flickr_photostream_imagesHeight_default);
	$flickr_photostream_maxPhotosPP_saved = (int)get_option('$flickr_photostream_maxPhotosPP', $flickr_photostream_maxPhotosPP_default);
	$flickr_photostream_lastRow_saved = get_option('$flickr_photostream_lastRow', $flickr_photostream_lastRow_default);
	$flickr_photostream_fixedHeight_saved = (int)get_option('$flickr_photostream_fixedHeight', $flickr_photostream_fixedHeight_default);
	$flickr_photostream_pagination_saved = get_option('$flickr_photostream_pagination', $flickr_photostream_pagination_default);
	$flickr_photostream_lightbox_saved = get_option('$flickr_photostream_lightbox', $flickr_photostream_lightbox_default);
	$flickr_photostream_captions_saved = (int)get_option('$flickr_photostream_captions', $flickr_photostream_captions_default);
	$flickr_photostream_randomize_saved = (int)get_option('$flickr_photostream_randomize', $flickr_photostream_randomize_default);
	$flickr_photostream_margins_saved = (int)get_option('$flickr_photostream_margins', $flickr_photostream_margins_default);
	$flickr_photostream_openOriginals_saved = (int)get_option('$flickr_photostream_openOriginals', $flickr_photostream_openOriginals_default);
	$flickr_photostream_bcontextmenu_saved = (int)get_option('$flickr_photostream_bcontextmenu', $flickr_photostream_bcontextmenu_default);
	
	//Save Values
	if (isset($_POST['Submit'])) {

		$error = false;
		$error_msg = "";

		//Check the API Key
		require_once("phpFlickr/phpFlickr.php");
		$flickr_photostream_APIKey_saved = $_POST["flickr_photostream_APIKey"];
		$f = new phpFlickr($flickr_photostream_APIKey_saved);

		if ($f->test_echo() == false) {
			$error = true;
			$error_msg .=	'<li>' . __('API Key is not valid', 'flickr-photostream' ) . '</li>'; 
		}

		$flickr_photostream_userID_saved = $_POST["flickr_photostream_userID"];
		if (!$error) {
			if ($f->urls_getUserProfile($flickr_photostream_userID_saved) == false) {
				$error = true;
				$error_msg .=	'<li>' . __('Invalid UserID', 'flickr-photostream' ) . '</li>'; 		
			}
		}

		$flickr_photostream_imagesHeight_saved = (int)$_POST["flickr_photostream_imagesHeight"];
		if ($flickr_photostream_imagesHeight_saved < 30) {
			$error = true;
			$error_msg .= '<li>' . __('The \'Images Height\' field must have a value greater than or equal to 30', 'flickr-photostream' ) . '</li>';
		}
		$flickr_photostream_maxPhotosPP_saved = (int)$_POST["flickr_photostream_maxPhotosPP"];
		if ($flickr_photostream_maxPhotosPP_saved <= 0) {
			$error = true;
			$error_msg .= '<li>' . __('The \'Photos per page\' field must have a value greater than 0', 'flickr-photostream' ) . '</li>';
		}
		$flickr_photostream_lastRow_saved = $_POST["flickr_photostream_lastRow"];

		if (isset($_POST["flickr_photostream_fixedHeight"]))
			$flickr_photostream_fixedHeight_saved = ((int)$_POST["flickr_photostream_fixedHeight"] != 0)? 1:0;
		else
			$flickr_photostream_fixedHeight_saved = 0;

		$flickr_photostream_pagination_saved = $_POST["flickr_photostream_pagination"];
		$flickr_photostream_lightbox_saved = $_POST["flickr_photostream_lightbox"];

		if (isset($_POST["flickr_photostream_captions"]))
			$flickr_photostream_captions_saved = ((int)$_POST["flickr_photostream_captions"] != 0)? 1:0;
		else
			$flickr_photostream_captions_saved = 0;

		if (isset($_POST["flickr_photostream_randomize"]))
			$flickr_photostream_randomize_saved = ((int)$_POST["flickr_photostream_randomize"] != 0)? 1:0;
		else
			$flickr_photostream_randomize_saved = 0;

		$flickr_photostream_margins_saved = (int)$_POST["flickr_photostream_margins"];

		if (isset($_POST["flickr_photostream_openOriginals"]))
			$flickr_photostream_openOriginals_saved = ((int)$_POST["flickr_photostream_openOriginals"] != 0)? 1:0;
		else
			$flickr_photostream_openOriginals_saved = 0;

		if (isset($_POST["flickr_photostream_bcontextmenu"]))
			$flickr_photostream_bcontextmenu_saved = ((int)$_POST["flickr_photostream_bcontextmenu"] != 0)? 1:0;
		else
			$flickr_photostream_bcontextmenu_saved = 0;

		if ($flickr_photostream_margins_saved <= 0 || $flickr_photostream_margins_saved > 30) {
			$error = true;
			$error_msg .= '<li>' . __('The \'Margins\' field must have a value greater than 0, and not greater than 30', 'flickr-photostream' ) . '</li>';
		}

		if ($error == false) {
			update_option( '$flickr_photostream_APIKey', $flickr_photostream_APIKey_saved );
			update_option( '$flickr_photostream_userID', $flickr_photostream_userID_saved );
			update_option( '$flickr_photostream_imagesHeight', $flickr_photostream_imagesHeight_saved );
			update_option( '$flickr_photostream_maxPhotosPP', $flickr_photostream_maxPhotosPP_saved );
			update_option( '$flickr_photostream_lastRow', $flickr_photostream_lastRow_saved );
			update_option( '$flickr_photostream_fixedHeight', $flickr_photostream_fixedHeight_saved );
			update_option( '$flickr_photostream_pagination', $flickr_photostream_pagination_saved );
			update_option( '$flickr_photostream_lightbox', $flickr_photostream_lightbox_saved );
			update_option( '$flickr_photostream_captions', $flickr_photostream_captions_saved );
			update_option( '$flickr_photostream_randomize', $flickr_photostream_randomize_saved );
			update_option( '$flickr_photostream_margins', $flickr_photostream_margins_saved );
			update_option( '$flickr_photostream_openOriginals', $flickr_photostream_openOriginals_saved );
			update_option( '$flickr_photostream_bcontextmenu', $flickr_photostream_bcontextmenu_saved );
?>
		<div class="updated">
			<p><strong><?php _e('Settings updated.', 'flickr-photostream' ); ?></strong></p>
		</div>
<?php
		}else{
?>
		<div class="updated">
			<p><strong><?php _e('Invalid values, the settings have not been updated', 'flickr-photostream' ); ?></strong></p>
			<ul style="color:red"><?php echo($error_msg); ?></ul>
		</div>
<?php
		}
	}
?>

	<style type="text/css">
		#poststuff h3 { cursor: auto; }
	</style>

			 
	<div class="wrap">
		<h2>Flickr Photostream</h2>

		<div id="poststuff">

			<div class="postbox">

				<h3><?php _e('Help', 'flickr-photostream' ); ?></h3>
				<div class="inside">
					<p>
						<?php _e('To display the default user\'s Photostream, create a page and simply write the following shortcode where you want to display the gallery.', 'flickr-photostream' ); ?>
						<div style="margin-left: 30px">
							<pre>[flickr_photostream]</pre>
						</div>
					</p>
					<p>
						<?php _e('However, you can also use the attributes to have settings that are different than the defaults. For example:', 'flickr-photostream' ); ?>
						<div style="margin-left: 30px">
							<pre>[flickr_photostream max_num_photos="50" no_pages="true"]</pre>
							<?php _e('displays the latest 50 photos of the default user Photostream, without any page navigation. (the other settings are the defaults)', 'flickr-photostream' ); ?>
						</div>
					</p>
					<p>
						<?php _e('You can also configure it to show other Photostreams. For example:', 'flickr-photostream' ); ?>
						<div style="margin-left: 30px">
							<pre>[flickr_photostream user_id="67681714@N03"]</pre>
							<?php _e('displays the Photostream of the specified user, no matter what is the default user ID in the settings. Remember that you can use <a href="http://idgettr.com/" target="_blank">idgettr</a> to retrieve the <code>user_id</code>.', 'flickr-photostream' ); ?>
						</div>
					</p>

		
					<h4><?php _e('Sets', 'flickr-photostream' ); ?></h4>
					<p>
						<?php _e('To show the photos of a particular photo set, you need to know its <code>photoset_id</code>.', 'flickr-photostream' ); ?>
						<?php _e('For example, the <code>photoset_id</code> of the photo set located in the URL:', 'flickr-photostream' ); ?>
						<code>http://www.flickr.com/photos/miro-mannino/sets/72157629228993613/</code>
						<?php _e('is: ', 'flickr-photostream' ); ?>
						<code>72157629228993613</code>.
						<?php _e('You can see that it is always the number after the word \'/sets/\'.', 'flickr-photostream' ); ?>
						<div>
							<?php _e('To show a particular photoset, you need to use the <code>flickr_set</code> shortcode, and specify the <code>photoset_id</code> with the attribute <code>id</code>. For example:', 'flickr-photostream' ); ?>
							<div style="margin-left: 30px">
								<pre>[flickr_set id="72157629228993613"]</pre>
							</div>
						</div>
					</p>

					<h4><?php _e('Galleries', 'flickr-photostream' ); ?></h4>
					<p>
						<?php _e('To show the photos of a particular gallery, you need to know the <code>user_id</code> of the user that owns it, and its <code>gallery_id</code>.', 'flickr-photostream' ); ?>
						<?php _e('For example, the <code>gallery_id</code> of the gallery located in the URL:', 'flickr-photostream' ); ?>
						<code>http://www.flickr.com/photos/miro-mannino/galleries/72157636382842016/</code>
						<?php _e('is: ', 'flickr-photostream' ); ?>
						<code>72157636382842016</code>.
						<?php _e('You can see that it is always the number after the word \'/galleries/\'.', 'flickr-photostream' ); ?>
						<div>
							<?php _e('To show a particular gallery, you need to use the <code>flickr_gallery</code> shortcode, and specify the <code>user_id</code> with the attribute <code>user_id</code>, and the <code>gallery_id</code> with the attribute <code>id</code>. For example:', 'flickr-photostream' ); ?>
							<div style="margin-left: 30px">
								<pre>[flickr_gallery user_id="67681714@N03" gallery_id="72157636382842016"]</pre>
							</div>
						</div>
						<?php _e('Remember that, if the gallery is owned by the default user (specified in the settings), you don\'t need the <code>user_id</code> attribute in the shortcode.', 'flickr-photostream' ); ?>
					</p>

					<h4><?php _e('Group pools', 'flickr-photostream' ); ?></h4>
					<p>
						<?php _e('To show the photos of a particular group pool, you need to know the <code>group_id</code>, that you can retrieve using <a href="http://idgettr.com/" target="_blank">idgettr</a>.', 'flickr-photostream' ); ?>
						<div>
							<?php _e('To show a particular group pool, you need to use the <code>flickr_group</code> shortcode, and specify the <code>group_id</code> with the attribute <code>id</code>. For example:', 'flickr-photostream' ); ?>
							<div style="margin-left: 30px">
								<pre>[flickr_group id="1563131@N22"]</pre>
							</div>
						</div>
					</p>

					<h4><?php _e('Tags', 'flickr-photostream' ); ?></h4>
					<p>
						<?php _e('To show the photos that have some particular tags, you need to use the <code>flickr_tags</code> shortcode, and specify the <code>user_id</code> and the tags with the attribute <code>tags</code>, as a comma-delimited list of words. For example:', 'flickr-photostream' ); ?>
						<div style="margin-left: 30px">
							<pre>[flickr_tags user_id="67681714@N03" tags="cat, square, nikon"]</pre>
							<?php _e('Displays photos with one or more of the tags listed (the list is viewed as an OR combination, that is the default behavior).', 'flickr-photostream' ); ?>
						</div>
						<p>
							<?php _e('You can also exclude results that match a term by prepending it with a <code>-</code> character.', 'flickr-photostream' ); ?>
							<?php _e('Then, you can choose to use the list as a OR combination of tags (to return photos that have <b>any</b> tag), or an AND combination (to return photos that have <b>all</b> the tags).', 'flickr-photostream' ); ?>
							<?php _e('To do this, you need to use the <code>tags_mode</code>, specifying "any" or "all". For example:', 'flickr-photostream' ); ?>						
							<div style="margin-left: 30px">
								<pre>[flickr_tags user_id="67681714@N03" tags="cat, square, nikon" tags_mode="all"]</pre>
								<?php _e('Displays photos with all the tags listed (the list is viewed as an AND combination).', 'flickr-photostream' ); ?>
							</div>
						</p>
						<?php _e('Remember that, if the photo that you want to display is owned by the default user (specified in the settings), you don\'t need the <code>user_id</code> attribute in the shortcode.', 'flickr-photostream' ); ?>
					</p>

				</div>
			</div>

			<div class="postbox">

				<h3><?php _e('Settings', 'flickr-photostream' ); ?></h3>
				<div class="inside">

					<form method="post" name="options" target="_self">
						<h4><?php _e('Global Settings', 'flickr-photostream' ); ?></h4>

						<table class="form-table">
							<tr>
								<th scope="row">
									<label><?php _e('Flickr API Key', 'flickr-photostream'); ?></label>
								</th>
								<td>
									<label for="flickr_photostream_APIKey">
									<input type="text" name="flickr_photostream_APIKey" 
										value="<?php echo($flickr_photostream_APIKey_saved); ?>"
										style="margin-right:10px"
									/> 	
									<?php _e('Get your Flickr API Key from ', 'flickr-photostream' ); ?><a href="http://www.flickr.com/services/api/keys/" target="_blank">Flickr API</a>
									<div><?php _e('You can\'t use an attribute to change this setting', 'flickr-photostream'); ?></div>
									</label>
								</td>
							</tr>
						</table>

						<h4><?php _e('Default Settings', 'flickr-photostream' ); ?></h4>

						<table class="form-table">
							<tr>
								<th scope="row"><?php _e('User ID', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_userID">
										<input type="text" name="flickr_photostream_userID"
											value="<?php echo($flickr_photostream_userID_saved); ?>"
											style="margin-right:10px"
										/>
										<?php _e('Get the User ID from ', 'flickr-photostream' ); ?><a href="http://idgettr.com/" target="_blank">idgettr</a>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'user_id' . __('</code> attribute to change this default value', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Images Height (in px)', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_imagesHeight">
										<input type="text" name="flickr_photostream_imagesHeight" 
											value="<?php echo($flickr_photostream_imagesHeight_saved); ?>"
										/>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'images_height' . __('</code> attribute to change this default value', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Maximum number of photos per page', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_maxPhotosPP">
										<input type="text" name="flickr_photostream_maxPhotosPP" 
											value="<?php echo($flickr_photostream_maxPhotosPP_saved); ?>"
										/>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'max_num_photos' . __('</code> attribute to change this default value', 'flickr-photostream') ); ?></div>
									</label> 	
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Last Row', 'flickr-photostream' ); ?></th>
								<td>
									<label for="">
										<select name="flickr_photostream_lastRow" style="margin-right:5px">
											<option value="justify" <?php if ($flickr_photostream_lastRow_saved === 'justify') { echo('selected="selected"'); }; ?> ><?php _e('Justify', 'flickr-photostream' );?></option>
											<option value="nojustify" <?php if ($flickr_photostream_lastRow_saved === 'nojustify') { echo('selected="selected"'); }; ?> ><?php _e('No justify', 'flickr-photostream' ); ?></option>
											<option value="hide" <?php if ($flickr_photostream_lastRow_saved === 'hide') { echo('selected="selected"'); }; ?> ><?php _e('Hide if it cannot be justified', 'flickr-photostream' ); ?></option>
										</select>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'last_row' . __('</code> attribute to change this default value (with the value <code>justify</code>, <code>nojustify</code> or <code>hide</code>)', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Fixed Height', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_fixedHeight">
										<input type="checkbox" name="flickr_photostream_fixedHeight" 
											<?php if ($flickr_photostream_fixedHeight_saved == 1) { echo('checked="checked"'); }; ?> 
											value="1"
											style="margin-right:5px"
										/>
										<?php _e('If enabled, each row has the same height, but the images will be cut more.', 'flickr-photostream' ); ?></li>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'fixed_height' . __('</code> attribute to change this default value (with the value <code>true</code> or <code>false</code>)', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Pagination', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_pagination">
										<select name="flickr_photostream_pagination" style="margin-right:5px">
											<option value="none" <?php if ($flickr_photostream_pagination_saved === 'none') { echo('selected="selected"'); }; ?> ><?php _e('None', 'flickr-photostream'); ?></option>
											<option value="prevnext" <?php if ($flickr_photostream_pagination_saved === 'prevnext') { echo('selected="selected"'); }; ?> ><?php _e('Previous and Next', 'flickr-photostream'); ?></option>
											<option value="numbers" <?php if ($flickr_photostream_pagination_saved === 'numbers') { echo('selected="selected"'); }; ?> ><?php _e('Page Numbers', 'flickr-photostream'); ?></option>
										</select>
										<?php _e('If enabled, navigation buttons will be shown, and you can see the older photos.<br/><i>Use only one instance per page with this settings enabled!</i>', 'flickr-photostream' ); ?></li>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'pagination' . __('</code> attribute to change this default value (with the value <code>none</code>, <code>prevnext</code> or <code>numbers</code>)', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Lightbox', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_lightbox">
									<select name="flickr_photostream_lightbox" style="margin-right:5px">
										<option value="none" <?php if ($flickr_photostream_lightbox_saved === 'none') { echo('selected="selected"'); }; ?> ><?php _e('None', 'flickr-photostream'); ?></option>
										<option value="colorbox" <?php if ($flickr_photostream_lightbox_saved === 'colorbox') { echo('selected="selected"'); }; ?> >Colorbox</option>
										<option value="swipebox" <?php if ($flickr_photostream_lightbox_saved === 'swipebox') { echo('selected="selected"'); }; ?> >Swipebox</option>
									</select>
									<div>
										<?php echo( __('With Colorbox, make sure that you have installed it with a plugin (i.e. ', 'flickr-photostream' ) . '<a href="http://wordpress.org/extend/plugins/jquery-colorbox/">jQuery Colorbox</a>, <a href="http://wordpress.org/extend/plugins/lightbox-plus/">Lightbox Plus</a>).'); ?>
										<?php _e('On the contrary, Swipebox comes with this plugin and you don\'t have to provide it with another plugin.', 'flickr-photostream'); ?>
									</div>
									<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'lightbox' . __('</code> attribute to change this default value (with the value <code>none</code>, <code>colorbox</code> or <code>swipebox</code>).', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Captions', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_captions">
									<input type="checkbox" name="flickr_photostream_captions" 
										<?php if ($flickr_photostream_captions_saved == 1) { echo('checked="checked"'); }; ?> 
										value="1" 
										style="margin-right:5px"
									/>
									<?php _e('If enabled, the title of the photo will be shown over the image when the mouse is over. Note: <i>captions, with small images, become aesthetically unpleasing</i>.', 'flickr-photostream'); ?></li>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'captions' . __('</code> attribute to change this default value (with the value <code>true</code> or <code>false</code>)', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Randomize order', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_randomize">
										<input type="checkbox" name="flickr_photostream_randomize" 
											<?php if ($flickr_photostream_randomize_saved == 1) { echo('checked="checked"'); }; ?> 
											value="1"
											style="margin-right:5px"
										/>
										<?php _e('If enabled, the photos of the same page are randomized.', 'flickr-photostream' ); ?></li>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'randomize' . __('</code> attribute to change this default value (with the value <code>true</code> or <code>false</code>)', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Margin between the images', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_margins">
										<input type="text" name="flickr_photostream_margins" 
											value="<?php echo($flickr_photostream_margins_saved); ?>"
											style="margin-right:10px"
										/>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'margins' . __('</code> attribute to change this default value', 'flickr-photostream') ); ?></div>
									</label> 	
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Open original images', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_openOriginals">
										<input type="checkbox" name="flickr_photostream_openOriginals" 
											<?php if ($flickr_photostream_openOriginals_saved == 1) { echo('checked="checked"'); }; ?> 
											value="1"
											style="margin-right:5px"
										/>
										<?php _e('If enabled, the lightbox will show the original images if they are available. Consider to leave this option off if your original images are very large.', 'flickr-photostream' ); ?></li>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'open_originals' . __('</code> attribute to change this default value (with the value <code>true</code> or <code>false</code>)', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Block right click', 'flickr-photostream' ); ?></th>
								<td>
									<label for="flickr_photostream_bcontextmenu">
										<input type="checkbox" name="flickr_photostream_bcontextmenu" 
											<?php if ($flickr_photostream_bcontextmenu_saved == 1) { echo('checked="checked"'); }; ?> 
											value="1"
											style="margin-right:5px"
										/>
										<?php _e('If enabled, the context menu will be blocked, so for the user is more difficult to save the images', 'flickr-photostream' ); ?></li>
										<div><?php echo( __('You can use the <code>', 'flickr-photostream') . 'block_contextmenu' . __('</code> attribute to change this default value (with the value <code>true</code> or <code>false</code>)', 'flickr-photostream') ); ?></div>
									</label>
								</td>
							</tr>
						</table>

						<p>
							<input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes', 'flickr-photostream' ); ?>" />
						</p>
					</form>
				</div>
			</div>

			<div class="postbox">
				<h3><?php _e('Help the project', 'flickr-photostream' ); ?></h3>
				<div class="inside">
					<p>
						<?php _e('Help the project to grow. Donate something, or simply <a href="http://wordpress.org/plugins/flickr-photostream" target="_blank">rate the plugin on Wordpress</a>.', 'flickr-photostream' ); ?>
						<form action="https://www.paypal.com/<cgi-bin/webscr" method="post" target="_top">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHLwYJKoZIhvcNAQcEoIIHIDCCBxwCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBaCyf+oIknmFhsXzg6/NMzIQqul6xv29/NoxNeLY9qTQx7cWHk58Zr8VoWG1ukzEr6kPHash3WD0EeMFtjnNaYXi9aYkvhwF6eSBYXwQYuQLNqKs4bN7QIoa5FLy6SZ0zWwPmgv/0U7338IJVIGsXftvFNQyb5S8MjHO6avNgmHDELMAkGBSsOAwIaBQAwgawGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIvVcVYkSJki+AgYjC6BBHnJH4/eA8hmo8xUB5j3TRadrqtaz/7o4OMu0lHsFilPob3qDJfZN7IQlL/PwJ0lN5x1Ruc2PyxTnDcc7eo/ho0N8wXTROArUcKpct4Tw7h/sFe4NW25B6lG+hx9fK/57569WwyRPK5psQumX4XQ+QIF/s6wYq84ufhbYVmY3oISDrzfGroIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMTMxMDA4MTUwOTE1WjAjBgkqhkiG9w0BCQQxFgQUiz62NrfLtqFKo3ajhtRp1q7EJzkwDQYJKoZIhvcNAQEBBQAEgYBPmyE8cQbzBqmOu2G4U7UguyWIoWopnGd/4TSzOpekRgUGO1AuRSECyUOirZozJDRqxnSBkuh6LKU9BuSQKErrLYaWWY0eIsyr7g1tD6v0ZllRFdAAWznJnqsw5pligM0YItaZ7ARTbk1IQP4fKm3I0rRMirxNQE4k1/8BPIMzTA==-----END PKCS7-----
							">
							<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1">
						</form>
					</p>
				</div>
			</div>

		</div>
	</div>

<?php 
}
?>