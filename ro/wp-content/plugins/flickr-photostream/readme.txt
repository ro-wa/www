=== Flickr Photostream ===
Contributors: miro.mannino
Donate link: http://miromannino.com/projects/flickr-photostream/#helptheproject
Tags: photography, gallery, photo, flickr, photostream, set, justified, grid
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt

Just your beautiful Flickr photos. In a Justified Grid.

== Description ==

Plugin that allows you to show your Flickr **Photostream**, **Photosets**, **Galleries**, **Group Pools**, or **Tags** in your blog, with a very elegant and awesome layout.

Create a gallery with the **same style of Flickr or Google+**! Awesome thumbnails disposition with a **justified grid**, calculated by a fast javascript algorithm called <a href="http://miromannino.com/projects/justified-gallery/ ‎" title="Justified Gallery">Justified Gallery</a>! You can **configure the height of the rows** to have a grid that can be *like the justified grid of Flickr or of Google+*. But, you can do more! For example you can *configure the margin between the images*, create rows with fixed height, or decide if you want to justify the last row or not!

You can also configure a gallery to show photos with a link to Flickr or with a **Lightbox** (Swipebox or Colorbox).

Always high quality thumbnails! The plugin chooses the **right resolution for the images**, using the "Flickr size suffixes", no small images are resized to be bigger and no big images are resized to be smaller! You can create gallery with very large thumbnails!

Remember that this plugin is not an official Flickr® plugin, any help will be greatly appreciated.

 = Features: = 

 * A gallery with the same layout of Flickr or Google+, configurable as you want.
 * Fast and light. Also uses a cache to load galleries instantly.
 * You can show photos from your Flickr Photostream, from a **Photoset**, from a **Gallery**, or from a **Group Pool**.
 * You can show all the photos that has some **Tags**.
 * You can create multiple galleries with different settings, also in the same page.
 * Customizable image sizes, always with a justified disposition.
 * Photo titles shown when the mouse is above.
 * Decide if use a lightbox (Colorbox or Swipebox) to show the original photo, or Flickr.
 * Customizable style, you need just to change a CSS.
 * Pagination with SEO friendly URLs. Decide if you want to show the newer photos or not.
 * Available in English and Italian

= Live Demo = 

See a Live Demo in [Miro Mannino's Blog](http://miromannino.com/my-photos)


== Installation ==

1. Upload the folder `flickr-photostream` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings through the 'Settings > Flickr Photostream' page.
4. Remember to set also the API key and the default User ID
5. Create a page with the shortcode `[flickr_photostream]` to show the specified user photostream (you can add attributes in the shortcode, to show set, gallery or simply to have settings that are different than the default).
6. (optional) If you want to use Colorbox, install a Colorbox plugin (i.e. [JQuery Colorbox](http://www.techotronic.de/plugins/jquery-colorbox/)). Then, check the settings the lightbox option.

== Frequently Asked Questions ==

= Can I have in the same blog two photostream of different Flickr's users?  =

Yes, use the shortcode attributes called `user_id`. For example the shortcode `[flickr_photostream user_id="67681714@N03"]` displays the photostream of the specified user, no matter what is the default user ID specified in the settings.

= Are the photos syncronized with Flickr? =

Yes, of course. But remember that the cache (and also Flickr) doesn't allow to see the changes immediately.

= Why I can’t see the last photos I uploaded? =

Probably because they aren’t public: Flickr Photostream can’t show private photos.

= API Key is not valid? =

There could be many reasons. 

* Try to install CURL installed in your server
* Probably the CURL certificate isn’t properly installed
* Update CURL to the latest version


== Screenshots ==

1. A tipical Photosream
2. A Photostream with more pages
3. The settings
3. The captions


== Changelog ==

= 3.1.6 =

* Try to fix errors in phpFlickr

= 3.1.5 = 

* Now is possible to use the `tags` option in the [flickr_group]
* Fixed the errors with WP_DEBUG enabled in Wordpress
* Improved the option that disables the context menu
* Revert phpFlickr to the nextend version put in 3.1.2
* Spanish translation

= 3.1.4 = 

* new option to disable the right click 
* phpFlickr 3.1.1

= 3.1.2 =

* nextend version of phpFlickr to solve the keys problems 
* Fixed 'No photos' with tags
* Fixed links to Flickr for sets
* Justified gallery 3.2

= 3.0 =

* Justified gallery 3.0
	* Less images crops 
	* Faster load (rows appear when are ready)
	* No more white spaces when the images are loading
* Randomize order
* Capability to show the original images in the lightbox

= 2.3.2 =

* pagination style workaround for themes that use 'pre' tags
* workaround for a swipe box bug, when there are more than one justified gallery in one page
* pagination settings error (changed the behavior for those people that founded usage problems)
* changed the available size behavior. Some Flickr images is very very huge! Now it try to show the large size image in the lightbox, it this is not available try to show the original image, and if this is not available show the medium size. Unfortunately, Flickr doesn't store very large sizes (only the original). 
* fixed some bugs with tags

= 2.2 =

* removed the setting 'use large thumbnails': founded a way to determine it automatically
* fixed the links with original photos

= 2.1 = 

* now it works with Photon.
* fixed errors for those that don't have large image in flickr (added the 'use large thumbnails' option).
* now the links display the original pictures and not the large ones, this improve the quality and the compatibility.

= 2.0 =

* Group pools
* Tags
* New shortcodes, due to the number of functionalities: photostream, group, tags, set, and galleries.
* The shortcode [flickrps] is still usable to show a photostream, but will be removed in the future (USE THE NEW SHORTCODES).
* Now one can use the Colorbox or the Swipebox lightbox
* Standard Wordpress pagination
* Pagination with prev and next links, or with page numbers
* Performance improvements, reduced the numbers of calls to the Flickr server.
* Justified Gallery updated to version 2.0

= 1.6 =

* Sets 
* Galleries
* Some performance improvements
* New settings UI style
* Some bugs fixed, thanks to nammourdesigns.
* New error detection system, now it's easier to find the wrong settings
* pagination settings has been changed, to be more understandable
* Justified Gallery updated to version 1.0.4

= 1.5 =
* updated Justified Gallery to version 1.0.2

= 1.4 =
* Now the plugin uses the [Justified Gallery](http://miromannino.com/projects/justified-gallery/) JQuery plugin to build the justified layout.
* Corrected some bugs in the default settings

= 1.3 =
* Algorithm improved, faster and now Internet Explorer compatible
* Added captions
* Now, you can add multiple instance on the same page
* Now, the CSS rules force the scrollbar to be always visible, this to prevent loops
* Fixed some errors
* Usability improved

= 1.2 =
* Deleted the custom Lightbox. Now, to use a lightbox, you need to use a plugin that enable colorbox.
* Added error message if the plugin doesn't find a plugin that enable colorbox.
* Added a loading phase to show the images directly in a justified grid.
* The images fade-in only when they are completely loaded.
* Simplified the settings page.
* Fixed an issue of the "IE8 or lower error message" in case of multiple gallery per page.

= 1.1 =
* Optional Lightbox
* Option to use or not the pages
* Support for multiple gallery instances
* All options is now "default options", every instance can have different options
* Now, you can have different instances that show different user photostreams

= 1.0.1 =
* Justified grid algorithm disabled for IE8 or lower
* Error message for IE8 or lower
* Fixed some css issues
* Speed improvements to the images loading

= 1.0 =
* First version


== Upgrade Notice ==

= 2.3 =

* pagination style workaround for themes that use 'pre' tags

* fixed some bugs with tags

= 2.2 =

* fixed the links with original photos

= 2.1 = 

* now it works with Photon.
* fixed errors for those that don't have large image in flickr (added the 'use large thumbnails' option).
* now the links display the original pictures and not the large ones, this improve the quality and the compatibility.

= 2.0 =

* New shortcodes, due to the number of functionalities: photostream, group, tags, set, and galleries. The shortcode [flickrps] is still usable to show a photostream, but will be removed in the future (USE THE NEW SHORTCODES).
* Group pools, Tags
* Now one can use the Colorbox or the Swipebox lightbox
* Standard Wordpress pagination
* Justified Gallery updated to version 2.0

= 1.6 =

* Sets, Galleries
* New error detection system, now it's easier to find the wrong settings
* Justified Gallery updated to version 1.0.4

= 1.5 =
* updated Justified Gallery to version 1.0.2

= 1.4 =
* Now the plugin uses the [Justified Gallery](http://miromannino.com/projects/justified-gallery/) JQuery plugin to build the justified layout.

= 1.3 =
* Algorithm improved, faster and now Internet Explorer compatible. Added captions. Now, you can add multiple instance on the same page. Fixed some errors and usability improved.

= 1.2 =
* The images fade-in only when they are completely loaded. Added a loading phase to show the images directly in a justified grid. Deleted the custom Lightbox. Now, to use a lightbox, you need to use a plugin that enable colorbox, in this way you are free to configure the style of the lightbox. Simplified the settings page.

= 1.1 =
* Lightbox, support for multiple gallery instances with different options.

= 1.0.1 =
* Justified grid algorithm disabled for IE8 or lower. Fixed some css issues. Speed improvements on images loading.

= 1.0 =
* First version.