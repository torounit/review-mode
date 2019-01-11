<?php
/**
 * Option and Admin view.
 *
 * @package Review_Mode
 */

namespace Review_Mode;

/**
 * Class Options
 *
 * @package Review_Mode
 */
class Options {

	const ACTION_NAME = 'change_review_mode';
	const META_KEY = 'review_mode_active';

	/**
	 * Check activation for Review Mode.
	 *
	 * @return boolean
	 */
	public static function is_current_user_active() {
		$id = get_current_user_id();
		return ! ! get_user_meta( $id, self::META_KEY, true );
	}

	/**
	 * Options constructor.
	 */
	public function __construct() {
		add_action( 'edit_user_profile', [ $this, 'form_field' ], 9 );
		add_action( 'show_user_profile', [ $this, 'form_field' ], 9 );
		add_action( 'personal_options_update', [ $this, 'update' ] );
		add_action( 'edit_user_profile_update', [ $this, 'update' ] );
		add_action( 'wp_ajax_' . self::ACTION_NAME, [ $this, 'wp_ajax_change_review_mode' ] );
	}

	/**
	 * Get toggle mode url.
	 *
	 * @return string
	 */
	public static function get_toggle_mode_url() {
		$action_name = self::ACTION_NAME;
		$meta_key = self::META_KEY;
		$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
		return admin_url(
			sprintf(
				"/admin-ajax.php?action=${action_name}&${meta_key}=%s&redirect_to=%s&nonce=%s",
				! absint( self::is_current_user_active() ),
				urlencode( esc_url_raw( $request_uri ) ),
				wp_create_nonce( self::ACTION_NAME )
			)
		);
	}

	/**
	 * Toggle Review mode activate and deactivate.
	 */
	public function wp_ajax_change_review_mode() {
		$nonce = filter_input( INPUT_GET, 'nonce' );
		if ( ! wp_verify_nonce( $nonce, self::ACTION_NAME ) ) {
			wp_die();
		}
		$user_id = get_current_user_id();
		if ( current_user_can( CAPABILITY ) ) {
			$mode = filter_input( INPUT_GET, self::META_KEY );
			update_user_meta( $user_id, self::META_KEY, $mode );
		} else {
			wp_die( 'Permission denied.' );
		}
		wp_safe_redirect( esc_url_raw( urldecode( filter_input( INPUT_GET, 'redirect_to' ) ) ) );
		exit;
	}

	/**
	 * Update meta.
	 *
	 * @param int $user_id User id.
	 *
	 * @return bool|int
	 */
	public function update( $user_id ) {
		if ( ! current_user_can( CAPABILITY, $user_id ) ) {
			return false;
		}
		$review_mode = filter_input( INPUT_POST, self::META_KEY );

		return update_user_meta( $user_id, self::META_KEY, absint( $review_mode ) );
	}

	/**
	 * Form view.
	 *
	 * @param \WP_User $user current user object.
	 */
	public function form_field( \WP_User $user ) {
		$review_mode_active = get_user_meta( $user->ID, self::META_KEY, true );
		?>

		<div id="review_mode">
			<h3><?php esc_html_e( 'Review mode', 'review-mode' ); ?></h3>
			<table class="form-table">
				<tr>
					<th>
						<label for="<?php echo esc_attr( self::META_KEY ); ?>">
							<?php esc_html_e( 'Activate review mode', 'review-mode' ); ?>
						</label>
					</th>
					<td>
						<label for="<?php echo esc_attr( self::META_KEY ); ?>">
							<input
								type="checkbox"
								id="<?php echo esc_attr( self::META_KEY ); ?>"
								name="<?php echo esc_attr( self::META_KEY ); ?>"
								value="1"
								<?php checked( $review_mode_active, 1 ); ?>
							><?php esc_html_e( 'Activate review mode', 'review-mode' ); ?>
						</label>

						<p class="description">
							<?php esc_html_e( 'Show draft and pending posts.', 'review-mode' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<?php
	}
}
