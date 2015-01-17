<?php
if( ! defined("MC4WP_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}


if( ! class_exists( 'WP_List_Table', false ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MC4WP_Forms_Table extends WP_List_Table {

	public function __construct()
	{
		//Set parent defaults
		parent::__construct( array(
            'singular' => 'form',  //singular name of the listed records
            'plural'   => 'forms', //plural name of the listed records
            'ajax'     => false                          //does this table support ajax?
            ) );

		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$hidden = array();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->get_items();
	}

	public function get_columns()
	{
		return array(
			'ID'     	=> __( 'ID', 'mailchimp-for-wp' ),
			'post_title' => __( 'Form', 'mailchimp-for-wp' ),
			'shortcode' => __( 'Shortcode', 'mailchimp-for-wp' ),
			'lists' => __( 'List(s)', 'mailchimp-for-wp' ),
			'post_modified' => __( 'Last edited', 'mailchimp-for-wp' )
		);
	}

	public function get_sortable_columns()
	{
		return array(
			'ID' => array( 'id', true ),
			'post_title' => array('title', false),
			'post_modified' => array('post_modified', false)
		);
	}

	public function get_items()
	{
		$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : null;
		$order = isset($_GET['order']) ? $_GET['order'] : null;

		$forms = get_posts(array(
			'post_type' => 'mc4wp-form',
			'posts_per_page' => -1,
			'orderby' => $orderby,
			'order' => $order

		));

		return $forms;
	}

	public function column_default( $item, $column_name ) 
	{
		return $item->$column_name;
	}

	public function column_post_title($item)
	{
		$actions = array(
            'edit'      => '<a class="" title="'. __( 'Edit Form', 'mailchimp-for-wp' ) .'" href="'. get_edit_post_link($item->ID) .'">'. __( 'Edit Form', 'mailchimp-for-wp' ) . '</a>',
            'delete'    => '<a class="submitdelete" title="Delete Form" href="'.get_delete_post_link($item->ID, '', true).'">' . __( 'Delete', 'mailchimp-for-wp' ) . '</a>'
        );
        $form_title = (empty($item->post_title)) ? "(no title)" : $item->post_title;
        $title = '<strong><a class="row-title" title="'. __( 'Edit Form', 'mailchimp-for-wp' ) .'" href="' . get_edit_post_link($item->ID) . '">'. $form_title . '</a></strong>';
		return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );
	}

	public function column_shortcode($item) {
		return '<input type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form id=&quot;'.$item->ID.'&quot;]" class="mc4wp-shortcode-example">';
	}

	public function column_lists($item) {
		$form_settings = mc4wp_get_form_settings($item->ID, true);
		$content = '';

		if( ! empty( $form_settings['lists'] ) ) {
			$mailchimp = new MC4WP_MailChimp();
			foreach( $form_settings['lists'] as $list_id ) {
				$content .= $mailchimp->get_list_name($list_id) . "<br />";
			}
		} else {
			return '<a style="color: red; text-decoration: underline;" href="'.get_edit_post_link($item->ID).'">' . __( 'No MailChimp list(s) selected yet.', 'mailchimp-for-wp' ) . '</a>';
		}
		
		return $content;
	}

	public function no_items() {
		_e( 'You have not created any sign-up forms yet. Time to do so!', 'mailchimp-for-wp' );
	}

}