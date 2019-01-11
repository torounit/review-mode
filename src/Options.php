<?php

namespace Review_Mode;

/**
 * Class Options
 * @package Review_Mode
 */
class Options {

	const META_KEY = 'review_mode_active';

	/**
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
	 * @param \WP_User $user
	 */
	public function form_field( \WP_User $user ) {
		$review_mode_active = get_user_meta( $user->ID, self::META_KEY, true );
		?>

		<div id="review_mode">
			<h3><?php esc_html_e( 'Review mode', 'review-mode' );?></h3>
			<table class="form-table">
				<tr>
					<th>
						<label for="<?php echo esc_attr( self::META_KEY ); ?>"><?php esc_html_e( 'Activate review mode', 'review-mode' );?></label>
					</th>
					<td>
						<label for="<?php echo esc_attr( self::META_KEY ); ?>">
							<input
								type="checkbox"
								id="<?php echo esc_attr( self::META_KEY ); ?>"
								name="<?php echo esc_attr( self::META_KEY ); ?>"
								value="1"
								<?php checked( $review_mode_active, 1 ); ?>
							><?php esc_html_e( 'Activate review mode', 'review-mode' );?></label>

						<p class="description"><?php esc_html_e( 'Show draft and pending posts.', 'review-mode' );?></p>
					</td>
				</tr>
			</table>
		</div>

		<?php
	}
}
