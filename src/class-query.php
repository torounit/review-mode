<?php
/**
 * WP_Query control.
 *
 * @package Review_Mode
 */

namespace Review_Mode;

/**
 * Class Query
 *
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
	 * Get statuses to add.
	 *
	 * @return array
	 */
	private function get_append_statuses() {
		return apply_filters( 'review_mode_statuses', [ 'pending' ] );
	}

	/**
	 * Add post status in all query.
	 *
	 * @param \WP_Query $query \WP_Query instance.
	 */
	public function pre_get_posts( \WP_Query $query ) {
		if ( is_admin() ) {
			return;
		}

		if ( ! Options::is_current_user_active() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );

		$capability     = $this->current_user_can_read_posts( $post_type );
		$default_status = ( $capability ) ? [ 'publish', 'private' ] : [ 'publish' ];
		$post_status    = $query->get( 'post_status', $default_status );

		if ( ! is_array( $post_status ) ) {
			$post_status = explode( ',', $post_status );
		}

		if ( [ 'publish' ] === $post_status || $this->array_same_values( $post_status, $default_status ) ) {
			$post_status = array_merge( $post_status, $this->get_append_statuses() );
			$query->set( 'post_status', $post_status );
		}
	}

	/**
	 * Check arrays have same values.
	 *
	 * @param array $a array.
	 * @param array $b array.
	 *
	 * @return bool
	 */
	private function array_same_values( $a, $b ) {
		if ( empty( $a ) || empty( $b ) ) {
			return false;
		}

		return ! count( array_diff( $a, $b ) ) &&
			! count( array_diff( $b, $a ) );
	}

	/**
	 * Check capability
	 *
	 * @param array|string $post_type post type for check.
	 *
	 * @return boolean
	 */
	public function current_user_can_read_posts( $post_type ) {
		if ( is_array( $post_type ) && count( $post_type ) > 1 ) {
			$post_type_cap = 'multiple_post_type';
		} else {
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			$post_type_object = get_post_type_object( $post_type );
			if ( empty( $post_type_object ) ) {
				$post_type_cap = $post_type;
			}
		}

		if ( ! empty( $post_type_object ) ) {
			$read_private_cap = $post_type_object->cap->read_private_posts;
		} else {
			if ( empty( $post_type_cap ) ) {
				return false;
			}
			$read_private_cap = 'read_private_' . $post_type_cap . 's';
		}

		return current_user_can( $read_private_cap );
	}
}
