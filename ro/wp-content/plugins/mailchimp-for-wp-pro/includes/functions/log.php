<?php
if( ! defined( "MC4WP_VERSION" ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* Log a MailChimp for WordPress sign-up request
*
* @param string $email
* @param array $list_ids
* @param string $signup_method
* @param array $merge_vars
* @param int $form_id
* @param int $comment_id
* @param string $url
*
* @return boolean
*/
function mc4wp_log( $email, array $list_ids, $signup_method, $signup_type, array $merge_vars = array(), $form_id = null, $comment_id = null, $url = '' ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'mc4wp_log';
	$list_ids = implode( ',', $list_ids );

	return $wpdb->insert( $table_name, array(
			'email' => $email,
			'list_ids' => $list_ids,
			'signup_method' => $signup_method,
			'signup_type' => $signup_type,
			'form_ID' => $form_id,
			'comment_ID' => $comment_id,
			'merge_vars' => json_encode( $merge_vars ),
			'datetime' => current_time( 'mysql' ),
			'url' => $url
		)
	);

}

/**
* Query the MC4WP_Log table
* 
* @param array $args (optional)
* @return array 
*/
function mc4wp_get_logs( array $args = array() ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mc4wp_log';

	$args = wp_parse_args( $args, array(
			'select' => '*',
			'email' => '',
			'signup_method' => '',
			'limit' => 1,
			'offset' => 0,
			'orderby' => 'id',
			'order' => 'DESC'
		) );

	$where = array();
	$params = array();

	// build general select from query
	$query = "SELECT {$args['select']} FROM {$table_name}";

	// add email where
	if ( '' !== $args['email'] ) {
		$where[]= "email LIKE %s";
		$params[] = '%%' . $wpdb->esc_like( $args['email'] ). '%%';
	}

	// add signup method where
	if ( '' !== $args['signup_method'] && in_array( $args['signup_method'], array( 'form', 'checkbox' ) ) ) {
		$where[] = "signup_method = %s";
		$params[] = $args['signup_method'];
	}

	// add where parameters
	if ( count( $where ) > 0 ) {
		$query .= ' WHERE '. implode( ' AND ', $where );
	}

	// prepare parameters
	if( ! empty( $params ) ) {
		$query = $wpdb->prepare( $query, $params );
	}

	// return result count
	if ( $args['select'] === 'COUNT(*)' ) {
		$query .= ' LIMIT 1';
		return (int) $wpdb->get_var( $query );
	}

	// return single row
	if( $args['limit'] === 1 ) {
		$query .= ' LIMIT 1';
		return $wpdb->get_row( $query );
	}

	// perform rest of query
	$args['limit'] = absint( $args['limit'] );
	$args['offset'] = absint( $args['offset'] );
	$order_by = sanitize_key( $args['orderby'] ) . ' ' . strtoupper( sanitize_key( $args['order'] ) );
	$query .= sprintf(' ORDER BY %s LIMIT %d, %d', $order_by, $args['offset'], $args['limit'] );

	return $wpdb->get_results( $query );
}

/**
* Delete one or multiple logs by ID
* @param array $ids
*/
function mc4wp_delete_logs( array $ids ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mc4wp_log';

	// create comma-separated string
	$comma_separated_ids = implode( ',', $ids );

	// escape string for usage in IN clause
	$comma_separated_ids = esc_sql( $comma_separated_ids );
	return $wpdb->query( "DELETE FROM {$table_name} WHERE id IN ({$comma_separated_ids})" );
}
