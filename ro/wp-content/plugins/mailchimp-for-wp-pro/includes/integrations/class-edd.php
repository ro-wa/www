<?php

// prevent direct file access
if( ! defined("MC4WP_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_EDD_Integration extends MC4WP_Integration {

	protected $type = 'edd_checkout';
	
	public function __construct() {

		parent::__construct();

		add_action( 'edd_purchase_form_user_info', array( $this, 'output_checkbox' ) );
		add_action( 'edd_checkout_before_gateway', array( $this, 'subscribe_from_edd' ), 10, 3 );
	}

	public function subscribe_from_edd( $data, $user_info, $valid_data ) {
		
		if ( $this->checkbox_was_checked() === false ) { 
			return false;
		}

		$email = $user_info['email'];
		$merge_vars = array(
			'NAME' => $user_info['first_name'] . ' ' . $user_info['last_name'],
			'FNAME' => $user_info['first_name'],
			'LNAME' => $user_info['last_name']
		);

		return $this->subscribe( $email, $merge_vars, 'edd_checkout' );
	}

}

