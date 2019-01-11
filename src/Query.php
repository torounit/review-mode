<?php

namespace Review_Mode;

/**
 * Class Query
 * @package Review_Mode
 */
class Query {

	/**
	 * Query constructor.
	 */
	public function __construct() {
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], 10, 1 );
	}

	/**
	 * @param \WP_Query $query
	 */
	public function pre_get_posts( \WP_Query $query ) {
		if ( is_admin() ) {
			return;
		}

		if ( ! Options::is_current_user_active() ) {
			return;
		}

		$default_status = ( is_user_logged_in() ) ? [ 'publish', 'private' ] : [ 'publish' ];
		$post_status    = $query->get( 'post_status', $default_status );
		if ( ! is_array( $post_status ) ) {
			$post_status = explode( ',', $post_status );
		}

		$post_status = array_merge( $post_status, [ 'pending', 'draft' ] );
		$query->set( 'post_status', $post_status );
	}
}
