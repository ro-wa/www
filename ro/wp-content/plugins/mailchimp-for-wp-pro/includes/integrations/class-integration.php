<?php

if( ! defined("MC4WP_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

abstract class MC4WP_Integration {

	/**
	 * @var string
	 */
	protected $type = 'integration';

	/**
	 * @var string
	 */
	protected $checkbox_name = '_mc4wp_subscribe';

	/**
	 * @var array
	 */
	protected $options;

	/**
	* Constructor
	*/
	public function __construct() {
		$this->checkbox_name = '_mc4wp_subscribe' . '_' . $this->type;
	}

	/**
	 * Get the checkbox options
	 *
	 * @return array
	 */
	public function get_options() {

		if( $this->options === null ) {
			$this->options = mc4wp_get_options( 'checkbox' );
		}

		return $this->options;
	}

	/**
	 * Was the honeypot filled?
	 *
	 * @return bool
	 */
	protected function is_honeypot_filled() {

		// Check if honeypot was filled (by spam bots)
		if( isset( $_POST['_mc4wp_required_but_not_really'] ) && ! empty( $_POST['_mc4wp_required_but_not_really'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Should the checkbox be pre-checked?
	 *
	 * @return bool
	 */
	public function is_prechecked() {
		$opts = $this->get_options();
		return !!( $opts['precheck'] );
	}

	/**
	 * Get the text for the label element
	 *
	 * @return string
	 */
	public function get_label_text() {

		$opts = $this->get_options();

		// Get general label text
		$label = $opts['label'];

		// Override label text if a specific text for this integration is set
		if ( isset( $opts['text_' . $this->type . '_label'] ) && ! empty( $opts['text_' . $this->type . '_label'] ) ) {
			// custom label text was set
			$label = $opts['text_' . $this->type . '_label'];
		}

		// replace label variables
		$label = mc4wp_replace_variables( $label, $opts['lists'] );

		return $label;
	}

	/**
	* @return bool
	*/
	public function checkbox_was_checked() {

		if( $this->is_honeypot_filled() ) {
			return false;
		}

		return ( isset( $_POST[ $this->checkbox_name ] ) && $_POST[ $this->checkbox_name ] == 1 );
	}

	/**
	* Outputs a checkbox
	*
	* @param string $hook
	*/
	public function output_checkbox() {
		echo $this->get_checkbox();
	}

	/**
	* @param mixed $hook Array or string
	* @return string
	*/
	public function get_checkbox( $args = array() ) {

		$opts = mc4wp_get_options( 'checkbox' );

		$checked = ( $this->is_prechecked() ) ? 'checked ' : '';

		// set label text
		if ( isset( $args['labels'][0] ) ) {
			// cf 7 shortcode
			$label = $args['labels'][0];
		} else {
			$label = $this->get_label_text();
		}

		// CF7 checkbox?
		if( is_array( $args ) && isset( $args['options'] ) ) {

			// check for default:0 or default:1 to set the checked attribute
		 	if( in_array( 'default:1', $args['options'] ) ) {
		 		$checked = 'checked';
		 	} else if( in_array( 'default:0', $args['options'] ) ) {
		 		$checked = '';
		 	}
		 	
		}

		$content = "\n<!-- MailChimp for WP Pro v". MC4WP_VERSION ." -->\n";

		do_action( 'mc4wp_before_checkbox' ); 

		// checkbox
		$content .= '<p id="mc4wp-checkbox">';
		$content .= '<label>';
		$content .= '<input type="checkbox" name="'. esc_attr( $this->checkbox_name ) .'" value="1" '. $checked . '/> ';
		$content .= $label;
		$content .= '</label>';
		$content .= '</p>';

		// honeypot
		$content .= '<textarea name="_mc4wp_required_but_not_really" style="display: none !important;"></textarea>';

		do_action( 'mc4wp_after_checkbox' );

		return $content;
	}

	/**
	 * @return array
	 */
	protected function get_lists() {

		// get checkbox lists options
		$opts = mc4wp_get_options( 'checkbox' );
		$lists = $opts['lists'];

		// get lists from form, if set.
		if( isset( $_POST['_mc4wp_lists'] ) && ! empty( $_POST['_mc4wp_lists'] ) ) {

			$lists = $_POST['_mc4wp_lists'];

			// make sure lists is an array
			if( ! is_array( $lists ) ) {

				// sanitize value
				$lists = sanitize_text_field( $lists );

				$lists = array( $lists );
			}

		}

		// allow plugins to filter final
		$lists = apply_filters( 'mc4wp_lists', $lists );

		return $lists;
	}

	/**
	* Makes a subscription request
	*
	* @param string $email
	* @param array $merge_vars
	* @param string $signup_type
	* @param int $comment_ID
	* @return string|boolean
	*/
	protected function subscribe( $email, array $merge_vars = array(), $signup_type = 'comment', $comment_id = null ) {

		$api = mc4wp_get_api();
		$opts = mc4wp_get_options( 'checkbox' );
		$lists = $this->get_lists();

		if( empty( $lists) ) {

			// show helpful error message to admins, but only if not using ajax
			if( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && current_user_can( 'manage_options' ) ) {
				wp_die('
					<h3>MailChimp for WP - Error</h3>
					<p>Please select a list to subscribe to in the <a href="'. admin_url( 'admin.php?page=mc4wp-pro-checkbox-settings' ) .'">checkbox settings</a>.</p>
					<p style="font-style:italic; font-size:12px;">This message is only visible to administrators for debugging purposes.</p>
					', 'Error - MailChimp for WP', array( 'back_link' => true ) );
			}

			return 'no_lists_selected';
		}

		// maybe guess first and last name
		if ( isset( $merge_vars['NAME'] ) && !isset( $merge_vars['FNAME'] ) && !isset( $merge_vars['LNAME'] ) ) {

			$strpos = strpos( $merge_vars['NAME'], ' ' );
			if ( $strpos !== false ) {
				$merge_vars['FNAME'] = substr( $merge_vars['NAME'], 0, $strpos );
				$merge_vars['LNAME'] = substr( $merge_vars['NAME'], $strpos );
			} else {
				$merge_vars['FNAME'] = $merge_vars['NAME'];
			}
		}

		// set ip address
		if( ! isset( $merge_vars['OPTIN_IP'] ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$merge_vars['OPTIN_IP'] = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		}

		$result = false;

		/**
		 * @filter `mc4wp_merge_vars`
		 * @expects array
		 * @param array $merge_vars
		 * @param string $signup_type
		 *
		 * Use this to filter the final merge vars before the request is sent to MailChimp
		 */
		$merge_vars = apply_filters( 'mc4wp_merge_vars', $merge_vars, $signup_type );

		/**
		 * @filter `mc4wp_merge_vars`
		 * @expects string
		 * @param string $email_type
		 *
		 * Use this to change the email type this users should receive
		 */
		$email_type = apply_filters( 'mc4wp_email_type', 'html' );

		/**
		 * @action `mc4wp_before_subscribe`
		 * @param string $email
		 * @param array $merge_vars
		 *
		 * Runs before the request is sent to MailChimp
		 */
		do_action( 'mc4wp_before_subscribe', $email, $merge_vars );

		foreach( $lists as $list_id ) {
			$result = $api->subscribe( $list_id, $email, $merge_vars, $email_type, $opts['double_optin'], false, true, $opts['send_welcome'] );
		}

		/**
		 * @action `mc4wp_after_subscribe`
		 * @param string $email
		 * @param array $merge_vars
		 * @param boolean $result
		 *
		 * Runs after the request is sent to MailChimp
		 */
		do_action( 'mc4wp_after_subscribe', $email, $merge_vars, $result );

		if ( $result === true ) {
			$from_url = ( isset($_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : '';
			do_action( 'mc4wp_subscribe_checkbox', $email, $lists, $signup_type, $merge_vars, $comment_id, $from_url );
		}

		// check if result succeeded, show debug message to administrators (only in NON-AJAX requests)
		if ( $result !== true && $api->has_error() && current_user_can( 'manage_options' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ( ! isset( $_POST['_wpcf7_is_ajax_call'] ) || $_POST['_wpcf7_is_ajax_call'] != 1 ) ) {
			wp_die( "<h3>MailChimp for WP - Error</h3>
					<p>The MailChimp server returned the following error message as a response to our sign-up request:</p>
					<pre>" . $api->get_error_message() . "</pre>
					<p>This is the data that was sent to MailChimp: </p>
					<strong>Email</strong>
					<pre>{$email}</pre>
					<strong>Merge variables</strong>
					<pre>" . print_r( $merge_vars, true ) . "</pre>
					<p><small>This message is only visible to administrators for debugging purposes.</small></p>
					", "Error - MailChimp for WP", array( 'back_link' => true ) );
		}

		return $result;
	}
}