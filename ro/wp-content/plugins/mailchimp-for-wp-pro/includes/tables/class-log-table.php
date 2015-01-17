<?php
if( ! defined("MC4WP_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MC4WP_Log_Table extends WP_List_Table {

	/**
	 * @var int
	 */
	private $per_page = 20;

	/**
	 * @var array
	 */
	private $log_counts = array(
		'all' => 0,
		'checkbox' => 0,
		'form' => 0
	);


	public function __construct( ) {
		//Set parent defaults
		parent::__construct( array(
				'singular' => __( 'Subscriber Log', 'mailchimp-for-wp' ),  //singular name of the listed records
				'plural'   => __( 'Subscriber Logs', 'mailchimp-for-wp' ), //plural name of the listed records
				'ajax'     => false                          //does this table support ajax?
			) );

		$this->process_bulk_action();
		$this->prepare_items();
	}

	function get_bulk_actions() {
		$actions = array(
			'delete'    => 'Delete'
		);
		return $actions;
	}

	public function get_columns() {

		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'email' => __( 'Email', 'mailchimp-for-wp' ),
			'list' => __( 'List', 'mailchimp-for-wp' ),
			'signup_type' => __( 'Type', 'mailchimp-for-wp' ),
			'source' => __( "Source", "mailchimp-for-wp" ),
			'merge_vars' => __( "Data", 'mailchimp-for-wp' ),
			'datetime' => __( "Subscribed", 'mailchimp-for-wp' )
		);

		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'email'  => array( 'email', false ),
			'datetime' => array( 'datetime', false ),
			'signup_type' => array( 'signup_type', false ),
			'list'   => array( 'list_ids', false )
		);
		return $sortable_columns;
	}

	public function prepare_items() {
		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$hidden = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $this->get_log_items();

		$this->log_counts = array(
			'all' => $this->get_total_log_count(),
			'checkbox' => $this->get_log_count( 'checkbox' ),
			'form' => $this->get_log_count( 'form' )
		);

		if ( isset( $_GET['view'] ) && in_array( $_GET['view'] , array_keys( $this->get_views() ) ) ) {
			$total_items  = $this->log_counts[$_GET['view']];
		} else {
			$total_items  = $this->log_counts['all'];
		}

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page
			)
		);
	}

	public function process_bulk_action() {

		if( ! isset( $_GET['log'] ) ) {
			return;
		}

		check_admin_referer( 'bulk-' . $this->_args['plural'] );

		$ids = $_GET['log'];

		if ( ! is_array( $ids ) ) {
			$ids = array( absint( $ids ) );
		}

		if ( $this->current_action() == 'delete' ) {
			add_settings_error( 'mc4wp', 'mc4wp-logs-deleted', __( 'Log items deleted.', 'mailchimp-for-wp' ), 'updated' );
			return mc4wp_delete_logs( $ids );
		}
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'datetime':
			return esc_html( $item->$column_name );
			break;
		case 'merge_vars':

			// don't print any merge vars if none were submitted
			$merge_vars = json_decode( $item->merge_vars, true );
			if ( ! is_array( $merge_vars ) || empty( $merge_vars ) ) {
				return '';
			}

			// build string
			$content = '';
			foreach ( $merge_vars as $name => $value ) {

				// skip non-scalar values for now
				if ( ! is_scalar( $value ) ) {
					continue;
				}

				$content .= sprintf( '<strong>%s:</strong> %s', esc_html( $name ), esc_html( $value ) ) . '<br />';
			}
			return $content;
			break;
		default:
			return '';
			break;
		}
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_source( $item ) {
		$parsed_url = parse_url( $item->url );

		if ( is_array( $parsed_url ) ) {
			$url = $parsed_url['path'];

			if( ! empty($parsed_url['query'] ) ) {
				$url .= '?' . $parsed_url['query'];
			}
		} else {
			$url = $item->url;
		}

		$shortened_url = ( strlen( $url ) >= 50 ) ? substr( $url, 0, 50 ) . '..' : $url;
		return '<a href="'. esc_url( $item->url ) .'">'. esc_html( $shortened_url ) . '</a>';
	}

	public function column_list( $item ) {
		$list_names = array();
		$list_ids = explode( ',', $item->list_ids );
		$mailchimp = new MC4WP_MailChimp();
		foreach ( $list_ids as $list_id ) {
			$list_names[] = $mailchimp->get_list_name( $list_id );
		}

		return join( ', ', $list_names );
	}

	public function column_email( $item ) {
		$actions = array(
			'delete' => '<a href="' . wp_nonce_url( admin_url( sprintf( 'admin.php?page=%s&action=%s&log=%s&tab=log', $_REQUEST['page'], 'delete', $item->ID ) ), 'bulk-' . $this->_args['plural'] ) . '">' . __( 'Delete', 'mailchimp-for-wp' ) . '</a>',
		);

		return esc_html( $item->email ) . $this->row_actions( $actions );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="log[]" value="%s" />', $item->ID
		);
	}

	public function column_signup_type( $item ) {

		$type = strtolower( trim( $item->signup_type ) );

		switch ( $type ) {
			case 'comment':
				$comment = get_comment( $item->comment_ID );

				if( ! is_object( $comment ) || $comment->comment_approved === 'trash' ) {
					return __( 'Comment', 'mailchimp-for-wp' ) .' <em>('. __( 'deleted', 'mailchimp-for-wp' ) .')</em>';
				}

				// build link to comment
				$link = get_permalink( $comment->comment_post_ID );
				$link = $link . '#comment-' . $comment->comment_ID;
				return '<a href="'. esc_url( $link ) .'">'. __( 'Comment', 'mailchimp-for-wp' ) . '</a>';
				break;

			case 'registration':
				return __( 'Registration', 'mailchimp-for-wp' );
				break;

			case 'form':
			case 'sign-up-form':
				$title = get_the_title( $item->form_ID );
				$title = strlen( $title ) > 20 ? substr( $title, 0, 20 ) . '..' : $title;
				$form_name = __( 'Form', 'mailchimp-for-wp' ) . " #{$item->form_ID}: $title";
				return '<a href="' . admin_url( 'post.php?action=edit&post=' . $item->form_ID ) . '">' . $form_name . '</a>';
				break;

			case 'buddypress_registration':
				return __( 'BuddyPress registration', 'mailchimp-for-wp' );
				break;

			case 'multisite_registration':
				return __( 'MultiSite registration', 'mailchimp-for-wp' );
				break;

			case 'edd_checkout':
				return 'Easy Digital Downloads ' . __( 'Checkout', 'mailchimp-for-wp' );
				break;

			case 'woocommerce_checkout':
				return 'WooCommerce ' . __( 'Checkout', 'mailchimp-for-wp' );
				break;

			case 'cf7':
			case 'contact_form_7':
				return __( 'Contact Form 7', 'mailchimp-for-wp' );
				break;

			case 'bbpress_new_topic':
				return __( 'bbPress: New Topic', 'mailchimp-for-wp' );
				break;

			case 'bbpress_new_reply':
				return __( 'bbPress: New Reply', 'mailchimp-for-wp' );
				break;

			case 'other_form':
			case 'other':
			case 'general':
				return __( 'Other Form', 'mailchimp-for-wp' );
				break;
		};

		return $item->signup_type;
	}

	private function get_log_items() {
		$args = array();
		$args['offset'] = ( $this->get_pagenum() - 1 ) * $this->per_page;
		$args['limit'] = $this->per_page;

		if ( isset( $_GET['s'] ) ) {
			$args['email']= $_GET['s'];
		}

		if ( isset( $_GET['view'] ) ) {
			$args['signup_method'] = $_GET['view'];
		}

		if ( isset( $_GET['orderby'] ) ) {
			$args['orderby'] = $_GET['orderby'];
		}

		if ( isset( $_GET['order'] ) ) {
			$args['order'] = $_GET['order'];
		}

		return mc4wp_get_logs( $args );
	}

	private function get_total_log_count() {

		$args = array();
		$args['select'] = "COUNT(*)";

		if ( isset( $_GET['s'] ) ) {
			$args['email']= $_GET['s'];
		}
		return mc4wp_get_logs( $args );
	}

	private function get_log_count( $method ) {
		$args = array();
		$args['select'] = "COUNT(*)";
		$args['signup_method'] = $method;
		return mc4wp_get_logs( $args );
	}

	public function no_items() {
		_e( 'No subscribe requests found.', 'mailchimp-for-wp' );
	}


	/**
	 * Setup available views
	 *
	 * @access      private
	 * @since       1.0
	 * @return      array
	 */

	public function get_views() {

		$base = admin_url( 'admin.php?page=mc4wp-pro-reports&tab=log' );
		$current = isset( $_GET['view'] ) ? $_GET['view'] : '';

		$link_html = '<a href="%s"%s>%s</a>(%s)';

		$views = array(
			'all'      => sprintf( $link_html,
				esc_url( remove_query_arg( 'view', $base ) ),
				$current === 'all' || $current == '' ? ' class="current"' : '',
				esc_html__( 'All', 'mailchimp-for-wp' ),
				$this->log_counts['all']
			),
			'form'   => sprintf( $link_html,
				esc_url( add_query_arg( 'view', 'form', $base ) ),
				$current === 'form' ? ' class="current"' : '',
				esc_html__( 'Form', 'mailchimp-for-wp' ),
				$this->log_counts['form']
			),
			'comment' => sprintf( $link_html,
				esc_url( add_query_arg( 'view', 'checkbox', $base ) ),
				$current === 'checkbox' ? ' class="current"' : '',
				esc_html__( 'Checkbox', 'mailchimp-for-wp' ),
				$this->log_counts['checkbox']
			)
		);

		return $views;

	}
}
