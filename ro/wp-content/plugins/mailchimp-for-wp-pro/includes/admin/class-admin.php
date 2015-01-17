<?php

if( ! defined("MC4WP_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Admin {

	/**
	 * @var bool True if the BWS Captcha plugin is activated.
	 */
	private $has_captcha_plugin = false;

	/**
	 * @var string The relative path to the main plugin file from the plugins dir
	 */
	private $plugin_file = 'mailchimp-for-wp-pro/mailchimp-for-wp-pro.php';

	/**
	* @var License_Manager
	*/ 
	private $license_manager;

	/**
	 * @var array
	 */
	private $checkbox_plugins;

	/**
	* Constructor
	*/
	public function __construct() {

		$this->license_manager = $this->load_license_manager();

		$this->setup_hooks(); 
	}

	/**
	* The upgrade routine
	* Only runs after updating plugin files (if version was bumped)
	*
	* @return boolean Boolean indication whether the upgrade routine ran
	*/ 
	public function upgrade() {

		$db_version = get_option( 'mc4wp_version', 0 );

		if( version_compare( MC4WP_VERSION, $db_version, '<=' ) ) {
			return false;
		}

		// define a constant that we're running an upgrade
		define( 'MC4WP_DOING_UPGRADE', true );

		// upgrade to 2.4.5
		if( version_compare( $db_version, '2.4.6', '<' ) ) {

			// get link to custom stylesheet
			$custom_stylesheet = get_option( 'mc4wp_custom_css_file', '' );

			// make sure link is protocol relative
			$custom_stylesheet = str_ireplace( array( 'http://', 'https://' ), '//', $custom_stylesheet );

			// update option again
			update_option( 'mc4wp_custom_css_file', $custom_stylesheet );
		}

		// upgrade to 2.4
		if( version_compare( $db_version, '2.4', '<' ) ) {

			// upgrade custom form stylesheets
			$custom_form_styles = get_option( 'mc4wp_form_css', array() );

			// get all forms
			$forms = get_posts( 'post_type=mc4wp-form&posts_per_page=-1' );
			foreach( $forms as $form ) {
				$form_styles[ 'form-' . $form->ID ] = $custom_form_styles;
			}

			delete_option( 'mc4wp_form_css' );
			update_option( 'mc4wp_form_styles', $form_styles );
		}

		update_option( 'mc4wp_version', MC4WP_VERSION );
		return true;
	}

	/**
	* Loads the plugin license manager
	*
	* @return MCWP_Plugin_License_Manager An instance of the Plugin_License_Manager class
	*/
	private function load_license_manager() {

		$product = new MC4WP_Product();
		$license_manager = new DVK_Plugin_License_Manager( $product );
		$license_manager->setup_hooks();
		return $license_manager;
	}

	/**
	* Registers all the hooks
	*/
	private function setup_hooks() {

		global $pagenow;

		// Actions used throughout WP Admin
		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
		add_action( 'admin_menu', array( $this, 'build_menu' ) );

		// Hooks for Plugins overview
		if( isset( $pagenow ) && $pagenow === 'plugins.php' ) {
			$this->plugin_file = plugin_basename( MC4WP_PLUGIN_FILE );

			add_filter( 'plugin_action_links_' . $this->plugin_file, array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );

			add_action( 'admin_notices', array( $this, 'show_notice_to_deactivate_lite' ) );
		}

		// Hooks for "edit form" pages
		if( true === $this->on_edit_form_page() ) {

			// is Captcha plugin running?
			$this->has_captcha_plugin = function_exists( 'cptch_display_captcha_custom' );

			add_action( 'do_meta_boxes', array( $this, 'remove_meta_boxes' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 25 );
			add_action( 'save_post', array( $this, 'save_form_data' ) );
			add_action( 'admin_head', array( $this, 'unload_conflicting_assets' ), 99 );

			add_filter( 'user_can_richedit', array( $this, 'disable_visual_editor' ) );
			add_filter( 'gettext', array( $this, 'change_publish_button' ), 10, 2 );
			add_filter( 'default_content', array( $this, 'get_default_form_markup' ), 10, 2 );
			add_filter( 'post_updated_messages', array( $this, 'set_form_updated_messages' ) );
			add_filter( 'quicktags_settings', array( $this, 'set_quicktags_buttons' ), 10, 2 );
			add_filter( 'wp_insert_post_data', array( $this, 'strip_form_tags' ) );
		}
	}


	/**
	 * Initializes the plugin
	 *
	 * - Registers settings
	 * - Loads the translation files
	 * - Runs the upgrade routine
	 * - Checks if the Captcha plugin is activated
	 */
	public function initialize() {

		// load textdomain
		$this->load_plugin_textdomain();

		// register settings
		$this->register_settings();

		// run upgrade routine
		$this->upgrade();
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_plugin_textdomain() {
		// load the plugin text domain
		load_plugin_textdomain( 'mailchimp-for-wp', false, dirname( $this->plugin_file ) . '/languages/' );
	}

	/**
	 * Add settings link to Plugins page
	 *
	 *
	 * @param $links
	 * @param $file
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links, $file ) {
		$settings_link = '<a href="' . admin_url('admin.php?page=mc4wp-pro') . '">' . __( 'Settings', 'mailchimp-for-wp' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Adds meta links to the plugin in the WP Admin > Plugins screen
	 *
	 * @param array $links
	 * @param string $file
	 *
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $file ) {

		if( $file !== $this->plugin_file ) {
			return $links;
		}

		$links[] = '<a href="http://docs.mc4wp.com/">' . __( 'Documentation', 'mailchimp-for-wp' ) . '</a>';
		return $links;
	}

	/**
	 * Returns a boolean indicating whether we're editing a MailChimp for WP form
	 *
	 * @return bool
	 */
	private function on_edit_form_page() {
		global $pagenow, $post;

		// check if current page is an "edit post type" page
		if( false === isset( $pagenow ) || ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) ) {
			return false;
		}

		// use global $post object if set
		if( is_object( $post ) ) {
			return ( $post->post_type === 'mc4wp-form' );
		}

		// use cheap string comparision if post_type is in request superglobal
		if( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] === 'mc4wp-form' ) {
			return true;
		}

		// query current post if post is in request superglobal
		if( isset( $_REQUEST['post'] ) && is_numeric( $_REQUEST['post'] ) ) {
			$p = get_post( absint( $_REQUEST['post'] ) );
			return ( is_object( $p  ) && $p ->post_type === 'mc4wp-form' );
		}

		return false;
	}

	/**
	* Change the publish button to "Save Form" or "Update Form"
	*/
	public function change_publish_button( $translation, $text ) {

		switch( $text ) {
			case 'Publish':
				$translation = __( 'Save Form', 'mailchimp-for-wp' );
				break;

			case 'Update':
				$translation = __( 'Update Form', 'mailchimp-for-wp' );
				break;
		}

		return $translation;
	}

	/**
	* Set Quicktags buttons for MCWP Forms
	* @return array 
	*/
	public function set_quicktags_buttons( $settings, $editor_id ) {
		$settings['buttons'] = 'strong,em,link,img,ul,li,close';
		return $settings;
	}

	/**
	* Register plugin settings
	*/
	public function register_settings() {
		register_setting( 'mc4wp_settings', 'mc4wp', array( $this, 'validate_settings' ) );
		register_setting( 'mc4wp_checkbox_settings', 'mc4wp_checkbox', array( $this, 'validate_checkbox_settings' ) );
		register_setting( 'mc4wp_form_settings', 'mc4wp_form', array( $this, 'validate_settings' ) );
		register_setting( 'mc4wp_form_styles_settings', 'mc4wp_form_styles', array( 'MC4WP_Styles_Builder', 'validate' ) );
	}

	/**
	* Set the default form mark-up
	* @return string
	*/
	public function get_default_form_markup( $content = '', $post = null ) {
		if ( is_object( $post ) && $post->post_type === 'mc4wp-form' ) {

			$email_placeholder = __( 'Your email address', 'mailchimp-for-wp' );
			$email_label = __('Email address', 'mailchimp-for-wp' );
			$signup_button =  __( 'Sign up', 'mailchimp-for-wp' );

			return "<p>\n\t<label for=\"mc4wp_email\">{$email_label} </label>\n\t<input type=\"email\" id=\"mc4wp_email\" name=\"EMAIL\" placeholder=\"{$email_placeholder}\" required />\n</p>\n\n<p>\n\t<input type=\"submit\" value=\"{$signup_button}\" />\n</p>";
		}

		return $content;
	}

	/**
	* Set notices after saving a form
	* @return array
	*/
	public function set_form_updated_messages( $messages ) {

		$form = get_post();

		$back_link = __( 'Back to general form settings', 'mailchimp-for-wp' );
		$messages['mc4wp-form'] = $messages['post'];
		$messages['mc4wp-form'][1] = __( 'Form updated.', 'mailchimp-for-wp' );
		$messages['mc4wp-form'][6] = __( 'Form saved.', 'mailchimp-for-wp' );

		// create array of missing form fields
		$missing_form_fields = array();

		// check if form contains EMAIL field
		$search = preg_match( '/<(input|textarea)(?=[^>]*name="EMAIL")[^>]*>/i', $form->post_content );
		if( ! $search) {
			$missing_form_fields[] = sprintf( __( 'An EMAIL field. Example: <code>%s</code>', 'mailchimp-for-wp' ), '&lt;input type="email" name="EMAIL" /&gt;' );
		}

		// check if form contains submit button
		$search = preg_match( '/<(input|button)(?=[^>]*type="submit")[^>]*>/i', $form->post_content );
		if( ! $search ) {
			$missing_form_fields[] = sprintf( __( 'A submit button. Example: <code>%s</code>', 'mailchimp-for-wp' ), '&lt;input type="submit" value="'. __( 'Sign Up', 'mailchimp-for-wp' ) .'" /&gt;' );
		}

		$opts = mc4wp_get_form_settings( $form->ID, true );
		$mailchimp = new MC4WP_MailChimp();

		// loop through selected list ids
		if( isset( $opts['lists'] ) && is_array( $opts['lists'] ) ) {

			foreach( $opts['lists'] as $list_id ) {

				// get list object
				$list = $mailchimp->get_list( $list_id );
				if( ! is_object( $list ) ) {
					continue;
				}

				// loop through merge vars of this list
				foreach( $list->merge_vars as $merge_var ) {

					// if field is required, make sure it's in the form mark-up
					if( ! $merge_var->req || $merge_var->tag === 'EMAIL' ) {
						continue;
					}

					// search for field tag in form mark-up using 'name="FIELD_NAME' without closing " because of array fields
					$search = stristr( $form->post_content, 'name="'. $merge_var->tag );
					if( false === $search ) {
						$missing_form_fields[] = sprintf( __( 'A \'%s\' field', 'mailchimp-for-wp' ), $merge_var->tag );
					}

				}

			}

		}

		$additional_message = '';
		
		if( ! empty( $missing_form_fields ) ) {
			$additional_message = '<br><br>' . __( 'Your form is missing the following (required) form fields:', 'mailchimp-for-wp') . ' <br>';

			foreach( $missing_form_fields as $missing_field ) {
				$additional_message .= '<br>- ' . $missing_field;
			}
		}

		// add back link and additional message to all messages
		foreach( $messages['mc4wp-form'] as $key => $message ) {
			$messages['mc4wp-form'][$key] .= $additional_message . '<br><br><a href="'. admin_url( 'admin.php?page=mc4wp-pro-form-settings' ) .'">&laquo; '. $back_link . '</a>';
		}

		return $messages;
	}

	/**
	* Strips <form> tags from form content before it is saved to the database
	* 
	* @param array $data
	* @return array
	*/
	public function strip_form_tags( $data ) {

		if( $data['post_type'] !== 'mc4wp-form' ) {
			return $data;
		}

		$data['post_content'] = preg_replace( '/<\/?form(.|\s)*?>/i', '', $data['post_content'] );

		return $data;
	}

	/**
	* @var int $post_ID
	*/
	public function save_form_data( $post_id ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['_mc4wp_nonce'] ) || ! wp_verify_nonce( $_POST['_mc4wp_nonce'], 'mc4wp_save_form' ) ) {
			return false; 
		}

		if( ! isset( $_POST['mc4wp_form'] ) || ! is_array( $_POST['mc4wp_form'] ) ) {
			return false;
		}


		// fill array with user data
		$data = $_POST['mc4wp_form'];
		unset( $_POST['mc4wp_form'] );

		$meta = array(
			'lists' => $data['lists']
		);

		$optional_meta_keys = array( 'send_email_copy', 'email_copy_receiver', 'double_optin', 'update_existing', 'replace_interests', 'send_welcome', 'ajax', 'hide_after_success', 'redirect', 'text_success', 'text_error', 'text_invalid_email', 'text_already_subscribed', 'text_invalid_captcha', 'text_required_field_missing' );
		foreach ( $optional_meta_keys as $meta_key ) {
			if ( isset( $data[$meta_key] ) ) {
				$meta[$meta_key] = $data[$meta_key];
			}
		}

		return update_post_meta( $post_id, '_mc4wp_settings', $meta );
	}

	/**
	* Adds meta boxes to the MCWP Forms screen
	*/
	public function add_meta_boxes() {
		add_meta_box( 'mc4wp-form-settings', __( 'Form Settings', 'mailchimp-for-wp' ), array( $this, 'show_required_form_settings_metabox' ), 'mc4wp-form', 'side', 'high' );
		add_meta_box( 'mc4wp-optional-settings', __( 'Optional Settings', 'mailchimp-for-wp' ), array( $this, 'show_optional_form_settings_metabox' ), 'mc4wp-form', 'normal', 'high' );
		add_meta_box( 'mc4wp-form-variables', __( 'Form Variables', 'mailchimp-for-wp' ), array( $this, 'show_form_variables_metabox' ), 'mc4wp-form', 'side' );
	}

	/**
	 * Remove all metaboxes except "submitdiv" and the mc4wp- meta boxes.
	 *
	 * Also removes all metaboxes added by other plugins..
	 */
	public function remove_meta_boxes() {
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes["mc4wp-form"] ) && is_array( $wp_meta_boxes["mc4wp-form"] ) ) {
			$meta_boxes = $wp_meta_boxes["mc4wp-form"];
			$allowed_meta_boxes = array( 'submitdiv' );

			foreach ( $meta_boxes as $context => $context_boxes ) {

				if ( ! is_array( $context_boxes ) ) {
					continue;
				}

				foreach ( $context_boxes as $priority => $priority_boxes ) {
					if ( !is_array( $priority_boxes ) ) {
						continue;
					}

					foreach ( $priority_boxes as $meta_box_id => $meta_box_args ) {
						if ( stristr( $meta_box_id, 'mc4wp' ) === false && ! in_array( $meta_box_id, $allowed_meta_boxes ) ) {
							unset( $wp_meta_boxes["mc4wp-form"][$context][$priority][$meta_box_id] );
						}
					}
				}
			}
		}
	}

	/**
	 * Outputs the form variables metabox
	 * @param WP_Post $post
	 */
	public function show_form_variables_metabox( $post ) {
		?><p><?php _e( 'Use the following variables to add some dynamic content to your form.' , 'mailchimp-for-wp' ); ?></p><?php
		include MC4WP_PLUGIN_DIR . 'includes/views/parts/admin-text-variables.php';
	}

	/**
	 * Outputs the required form settings metabox
	 * @param WP_Post $post
	 */
	public function show_required_form_settings_metabox( $post ) {
		$mailchimp = new MC4WP_MailChimp();
		$lists = $mailchimp->get_lists();
		$form_settings = mc4wp_get_form_settings( $post->ID );
		include MC4WP_PLUGIN_DIR . 'includes/views/metaboxes/required-form-settings.php';
	}

	/**
	 * Outputs the optional form settings metabox
	 * @param WP_Post $post
	 */
	public function show_optional_form_settings_metabox( $post ) {
		$form_settings = mc4wp_get_form_settings( $post->ID );
		$inherited_settings = mc4wp_get_options('form');
		$final_settings = mc4wp_get_form_settings( $post->ID, true );
		include MC4WP_PLUGIN_DIR . 'includes/views/metaboxes/optional-form-settings.php';
	}

	/**
	 * Disables the visual editor for MC4WP Forms
	 *
	 * @param bool $default
	 * @return boolean
	 */
	public function disable_visual_editor( $default ) {
		return false;
	}

	/**
	* Sanitize the plugin settings
	*
	* @var array $settings Raw input array of settings
	* @return array $settings Sanitized array of settings
	*/
	public function validate_settings( array $settings ) {

		if( isset( $settings['api_key'] ) ) {
			$settings['api_key'] = sanitize_text_field( $settings['api_key'] );
		}

		return $settings;
	}


	/**
	* Validate the checkbox settings
	*
	* @param array $settings Raw input array of settings
	* @return array $settings Sanitized array of settings
	*/
	public function validate_checkbox_settings( array $settings ) {
		// strip tags from general label
		$settings['label'] = strip_tags( $settings['label'], '<b><strong><i><em><a><span><strike><u>' );

		// strip tags from custom labels
		$checkbox_label_keys = array_keys( $this->get_checkbox_compatible_plugins() );
		foreach ( $checkbox_label_keys as $key ) {
			if ( isset( $settings['text_' . $key . '_label'] ) ) {
				$settings['text_' . $key . '_label'] = strip_tags( $settings['text_' . $key . '_label'], '<b><strong><i><em><a><span><strike><u>' );
			}
		}

		// validate woocommerce checkbox position
		if( isset( $settings['woocommerce_position'] ) ) {

			// make sure position is either 'order' or 'billing'
			if( ! in_array( $settings['woocommerce_position'], array( 'order', 'billing' ) ) ) {
				$settings['woocommerce_position'] = 'billing';
			}
		}

		return $settings;
	}

	/**
	* Build the MCWP Admin Menu
	*/
	public function build_menu() {

		/**
		 * @filter mc4wp_settings_cap
		 * @expects     string      A valid WP capability like 'manage_options' (default)
		 *
		 * Use to customize the required user capability to access the MC4WP settings pages
		 */
		$required_cap = apply_filters('mc4wp_settings_cap', 'manage_options');

		add_menu_page( 'MailChimp for WP Pro', 'MailChimp for WP', $required_cap, 'mc4wp-pro', array( $this, 'show_general_settings_page' ), MC4WP_PLUGIN_URL . 'assets/img/menu-icon.png', '99.13371337');

		// only add admin pages to menu if license is active and valid.
		add_submenu_page( 'mc4wp-pro', __( 'MailChimp & License', 'mailchimp-for-wp' ) . ' - MailChimp for WP Pro', __( 'MailChimp & License', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-pro', array( $this, 'show_general_settings_page' ) );
		add_submenu_page( 'mc4wp-pro', __( 'Checkboxes', 'mailchimp-for-wp' ) . ' - MailChimp for WP Pro', __( 'Checkboxes', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-pro-checkbox-settings', array( $this, 'show_checkbox_settings_page' ) );
		add_submenu_page( 'mc4wp-pro', __( 'Forms', 'mailchimp-for-wp' ) . ' - MailChimp for WP Pro', __( 'Forms', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-pro-form-settings', array( $this, 'show_form_settings_page' ) );
		add_submenu_page( 'mc4wp-pro', __( 'Reports', 'mailchimp-for-wp' ) . ' - MailChimp for WP Pro', __( 'Reports', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-pro-reports', array( $this, 'show_reports_page' ) );

	}
	
	/**
	* Load scripts and stylesheet on MCWP Admin pages
	*/
	public function load_assets( $hook = '' ) {

		// should we load the minified version?
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( isset( $_GET['page'] ) && stristr( $_GET['page'], 'mc4wp-pro' ) ) {

			/*
				Any MailChimp for WP Settings Page
			*/

			// Styles
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_style( 'mc4wp-admin-styles', MC4WP_PLUGIN_URL . 'assets/css/admin-styles'. $suffix .'.css' );

			// Scripts
			wp_register_script( 'mc4wp-admin-settings',  MC4WP_PLUGIN_URL . 'assets/js/admin-settings'. $suffix .'.js', array( 'jquery', 'wp-color-picker' ), MC4WP_VERSION, true );
			wp_enqueue_script( array( 'jquery', 'mc4wp-admin-settings' ) );

			/* Reports page */
			if ( $_GET['page'] === 'mc4wp-pro-reports' && ( ! isset( $_GET['tab'] ) || $_GET['tab'] === 'statistics' ) ) {

				// load flot
				wp_register_script( 'mc4wp-flot', MC4WP_PLUGIN_URL . 'assets/js/third-party/jquery.flot.min.js', array( 'jquery' ), MC4WP_VERSION, true );
				wp_register_script( 'mc4wp-flot-time', MC4WP_PLUGIN_URL . 'assets/js/third-party/jquery.flot.time.min.js', array( 'jquery' ), MC4WP_VERSION, true );
				wp_register_script( 'mc4wp-statistics', MC4WP_PLUGIN_URL . 'assets/js/admin-statistics'. $suffix .'.js', array( 'mc4wp-flot-time' ), MC4WP_VERSION, true );

				wp_enqueue_script( array( 'jquery', 'mc4wp-flot', 'mc4wp-statistics' ) );

				// print ie excanvas script in footer
				add_action( 'admin_print_footer_scripts', array( $this, 'print_excanvas_script' ), 1 );
			}

			/* CSS Edit Page */
			if ( $_GET['page'] === 'mc4wp-pro-form-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'css-builder' ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'jquery-ui-accordion' );
				wp_enqueue_script( 'mc4wp-form-css', MC4WP_PLUGIN_URL . 'assets/js/admin-form-css'. $suffix .'.js', array(), MC4WP_VERSION, true );
			}

		}

		if ( $this->on_edit_form_page() ) {
			/*
				Edit `mc4wp-form` pages
			*/
			wp_dequeue_script( 'ztranslate-script' );

			// Styles
			wp_enqueue_style( 'mc4wp-admin-styles', MC4WP_PLUGIN_URL . 'assets/css/admin-styles'. $suffix .'.css', array(), MC4WP_VERSION );

			// Scripts
			wp_register_script( 'mc4wp-beautifyhtml', MC4WP_PLUGIN_URL . 'assets/js/third-party/beautify-html.min.js', array( 'jquery' ), MC4WP_VERSION, true);
			wp_register_script( 'mc4wp-admin-formhelper',  MC4WP_PLUGIN_URL . 'assets/js/admin-formhelper'. $suffix .'.js', array( 'jquery', 'quicktags' ), MC4WP_VERSION, true );
			wp_enqueue_script( array( 'jquery', 'mc4wp-beautifyhtml', 'mc4wp-admin-formhelper' ) );
			wp_localize_script( 'mc4wp-admin-formhelper', 'mc4wp',
				array(
					'has_captcha_plugin' => $this->has_captcha_plugin
				)
			);

			// we don't need the following scripts
			wp_dequeue_script( 'autosave', 'suggest' );

		}

	}

	/**
	 * Do not load conflicting scripts on edit form pages
	 *
	 * - zTranslate
	 * - ...
	 */
	public function unload_conflicting_assets() {
		wp_dequeue_script( 'ztranslate-script' );
	}

	/**
	* Get Checkbox integrations
	*
	* @return array 
	*/
	public function get_checkbox_compatible_plugins() {

		if( $this->checkbox_plugins === null ) {

			// build array of checkbox compatible plugins
			$this->checkbox_plugins = array(
				'comment_form' => __( "Comment form", 'mailchimp-for-wp' ),
				"registration_form" => __( "Registration form", 'mailchimp-for-wp' )
			);

			if( is_multisite() ) {
				$this->checkbox_plugins['multisite_form'] = __( "MultiSite forms", 'mailchimp-for-wp' );
			}

			if( class_exists("BuddyPress") ) {
				$this->checkbox_plugins['buddypress_form'] = __( "BuddyPress registration", 'mailchimp-for-wp' );
			}

			if( class_exists('bbPress') ) {
				$this->checkbox_plugins['bbpress_forms'] = "bbPress";
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				$this->checkbox_plugins['edd_checkout'] = sprintf( __( '%s checkout', 'mailchimp-for-wp' ), 'Easy Digital Downloads' );
			}

			if ( class_exists( 'WooCommerce' ) ) {
				$this->checkbox_plugins['woocommerce_checkout'] = sprintf( __( '%s checkout', 'mailchimp-for-wp' ), 'WooCommerce' );
			}

		}

		return $this->checkbox_plugins;
	}

	/**
	* Get selected Checkbox integrations
	* @return array
	*/
	public function get_selected_checkbox_hooks() {
		$checkbox_plugins = $this->get_checkbox_compatible_plugins();
		$selected_checkbox_hooks = array();
		$checkbox_opts = mc4wp_get_options( 'checkbox' );

		// check which checkbox hooks are selected
		foreach ( $checkbox_plugins as $code => $name ) {

			if ( isset( $checkbox_opts['show_at_'.$code] ) && $checkbox_opts['show_at_'.$code] ) { 
				$selected_checkbox_hooks[$code] = $name; 
			}
		}

		return $selected_checkbox_hooks;
	}

	/**
	* Show general settings page
	*/
	public function show_general_settings_page() {
		$opts = mc4wp_get_options('general');

		$connected = mc4wp_get_api()->is_connected();
		if ( ! $connected ) {
			add_settings_error( "mc4wp", "invalid-api-key", sprintf( __( 'Please make sure the plugin is connected to MailChimp. <a href="%s">Provide a valid API key.</a>', 'mailchimp-for-wp' ), admin_url( '?page=mc4wp-pro' ) ), 'updated' );
		}

		// cache renewal triggered manually?
		$force_cache_refresh = isset( $_POST['mc4wp-renew-cache'] ) && $_POST['mc4wp-renew-cache'] == 1;
		$mailchimp = new MC4WP_MailChimp();
		$lists = $mailchimp->get_lists( $force_cache_refresh );

		if ( $force_cache_refresh ) {
			if ( false === empty ( $lists ) ) {
				add_settings_error( "mc4wp", "mc4wp-cache-success", __( 'Renewed MailChimp cache.', 'mailchimp-for-wp' ), 'updated' );
			} else {
				add_settings_error( "mc4wp", "mc4wp-cache-error", __( 'Failed to renew MailChimp cache - please try again later.', 'mailchimp-for-wp' ) );
			}
		}

		require MC4WP_PLUGIN_DIR . 'includes/views/pages/admin-general-settings.php';
	}

	/**
	* Show checkbox settings page
	*/
	public function show_checkbox_settings_page() {
		$opts = mc4wp_get_options('checkbox');
		$mailchimp = new MC4WP_MailChimp();
		$lists = $mailchimp->get_lists();

		$checkbox_plugins = $this->get_checkbox_compatible_plugins();
		$selected_checkbox_hooks = $this->get_selected_checkbox_hooks();

		require MC4WP_PLUGIN_DIR . 'includes/views/pages/admin-checkbox-settings.php';
	}

	/**
	* Show form settings page
	*/
	public function show_form_settings_page() {
		$tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'general-settings';
		$opts = mc4wp_get_options('form');

		if ( $tab === 'general-settings' ) {
			$table = new MC4WP_Forms_Table( );
		} else {

			// get all forms
			$forms = get_posts( 'post_type=mc4wp-form&posts_per_page=-1' );

			// get form to which styles should apply
			if( isset( $_GET['form_id'] ) ) {
				$form_id = absint( $_GET['form_id'] );
			} elseif( isset( $forms[0] ) ) {
				$form_id = $forms[0]->ID;
			} else {
				$form_id = 0;
			}

			// get css settings for this form (or 0)
			$styles = MC4WP_Styles_Builder::get_form_styles( $form_id );

			// create preview url
			$preview_url = add_query_arg( array( 'form_id' => $form_id, '_mc4wp_css_preview' => 1 ), home_url( '/', is_ssl() ) );
		}

		require MC4WP_PLUGIN_DIR . 'includes/views/pages/admin-form-settings.php';
	}

	/**
	* Show log page
	*/
	public function show_log_page() {
		$table = new MC4WP_Log_Table( );
		$tab = 'log';
		include_once MC4WP_PLUGIN_DIR . 'includes/views/pages/admin-reports.php';
	}

	/**
	* Show reports (stats) page
	*/
	public function show_stats_page() {
		$statistics = new MC4WP_Statistics();

		// set default range or get range from URL
		$range = ( isset( $_GET['range'] ) ) ? $_GET['range'] : 'last_week';

		// get data
		if ( $range !== 'custom' ) {
			$args = $statistics->get_range_times( $range );
		} else {
			// construct timestamps from given date in select boxes
			$start = strtotime( implode( '-', array( $_GET['start_year'], $_GET['start_month'], $_GET['start_day'] ) ) );
			$end = strtotime( implode( '-', array( $_GET['end_year'], $_GET['end_month'], $_GET['end_day'] ) ) );

			// calculate step size
			$step = $statistics->get_step_size( $start, $end );
			$given_day = $_GET['start_day'];

			$args = compact( "step", "start", "end", "given_day" );
		}

		// check if start timestamp comes after end timestamp
		if ( $args['start'] >= $args['end'] ) {
			$args = $statistics->get_range_times( 'last_week' );
			add_settings_error( 'mc4wp', 'mc4wp-stats', __( 'End date can\'t be before the start date', 'mailchimp-for-wp' ) );
		}

		// setup statistic settings
		$ticksizestep = ( $args['step'] === 'week' ) ? 'month' : $args['step'];
		$statistics_settings = $this->statistics_settings = array( 'ticksize' => array( 1, $ticksizestep ) );
		$statistics_data = $this->statistics_data = $statistics->get_statistics( $args );

		// add scripts
		wp_localize_script( 'mc4wp-statistics', 'mc4wp_statistics_data', $statistics_data );
		wp_localize_script( 'mc4wp-statistics', 'mc4wp_statistics_settings', $statistics_settings );

		$start_day = ( isset( $_GET['start_day'] ) ) ? $_GET['start_day'] : 0;
		$start_month = ( isset( $_GET['start_month'] ) ) ? $_GET['start_month'] : 0;
		$start_year = ( isset( $_GET['start_year'] ) ) ? $_GET['start_year'] : 0;
		$end_day = ( isset( $_GET['end_day'] ) ) ? $_GET['end_day'] : 0;
		$end_month = ( isset( $_GET['end_month'] ) ) ? $_GET['end_month'] : 0;
		$end_year = ( isset( $_GET['end_year'] ) ) ? $_GET['end_year'] : 0;
		$tab = 'statistics';

		include_once MC4WP_PLUGIN_DIR . 'includes/views/pages/admin-reports.php';
	}

	/**
	* Show reports page
	*/
	public function show_reports_page() {
		$tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'stats';

		$disable_logging = apply_filters( 'mc4wp_disable_logging', false );

		if( $disable_logging ) {
			echo '<p>' . sprintf( __( 'You disabled logging using the %s filter. Re-enable it to use the Reports page.', 'mailchimp-for-wp' ), '<code>mc4wp_disable_logging</code>' ) . '</p>';
		} elseif ( $tab === 'log' ) {
			return $this->show_log_page();
		} else {
			return $this->show_stats_page();
		}
	}

	/**
	 * Show a notice if MailChimp for WP Lite is activated
	 */
	public function show_notice_to_deactivate_lite() {
		if ( false === is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' ) ) {
			return;
		}
		?><div class="updated">
			<p><?php printf( __( '<strong>Welcome to MailChimp for WordPress Pro!</strong> We transfered the settings you had set in the Lite version, please <a href="%s">deactivate it now</a> to prevent problems', 'mailchimp-for-wp' ), admin_url( 'plugins.php#mailchimp-for-wordpress-lite' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Print the IE canvas fallback script in the footer on statistics pages
	 */
	public function print_excanvas_script() {
		?><!--[if lte IE 8]><script language="javascript" type="text/javascript" src="<?php echo MC4WP_PLUGIN_URL . 'assets/js/third-party/excanvas.min.js'; ?>"></script><![endif]--><?php
	}

}