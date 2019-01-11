<?php
/**
 * Admin Bar class.
 *
 * @package Review_Mode
 */

namespace Review_Mode;

/**
 * Class Admin_Bar
 */
class Admin_Bar {

	/**
	 * Admin_Bar constructor.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', [ $this, 'register_button' ], 999 );
		add_action( 'admin_print_styles', [ $this, 'print_styles' ] );
		add_action( 'wp_head', [ $this, 'print_styles' ] );
	}

	/**
	 * Get toggle mode url.
	 *
	 * @return string
	 */
	private function get_toggle_url() {
		$action_name = Options::ACTION_NAME;
		$meta_key = Options::META_KEY;
		return admin_url(
			sprintf(
				"/admin-ajax.php?action=${action_name}&${meta_key}=%s&redirect_to=%s&nonce=%s",
				! absint( Options::is_current_user_active() ),
				urlencode( $_SERVER['REQUEST_URI'] ),
				wp_create_nonce( 'toggle_review_mode' )
			)
		);
	}

	/**
	 * Add button to admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar \WP_Admin_Bar instance.
	 */
	public function register_button( \WP_Admin_Bar $wp_admin_bar ) {
		$text  = ( Options::is_current_user_active() ) ? esc_html__( 'Review mode active', 'review-mode' ) : esc_html__( 'Review mode inactive', 'review-mode' );
		$title = '<span class="ab-icon"></span><span class="ab-label">' . $text . '</span>';
		$link  = $this->get_toggle_url();

		if ( current_user_can( CAPABILITY ) ) {
			$wp_admin_bar->add_menu(
				[
					'id'    => 'review-mode',
					'title' => $title,
					'href'  => $link,
					'meta'  => [
						'class' => '',
					],
				]
			);
		}
	}

	/**
	 * Print style tag.
	 */
	public function print_styles() {
		?>
		<style type="text/css">
			#wpadminbar #wp-admin-bar-review-mode {
			}

			#wpadminbar #wp-admin-bar-review-mode .ab-icon:before {
				content: "\f339";
			<?php if ( Options::is_current_user_active() ) : ?>
				color: yellow;
			<?php endif; ?>
				top: 2px;
			}

		</style>
		<?php
	}
}
