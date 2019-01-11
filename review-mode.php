<?php
/**
 * Plugin Name:     Review Mode
 * Plugin URI:      https://github.com/torounit/review-mode
 * Description:     Add mode to show pending posts in all archives for reviewing.
 * Author:          Toro_Unit
 * Author URI:      https://torounit.com
 * Text Domain:     review-mode
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Review_Mode
 */

namespace Review_Mode;

require_once dirname( __FILE__ ) . '/src/class-query.php';
require_once dirname( __FILE__ ) . '/src/class-admin-bar.php';
require_once dirname( __FILE__ ) . '/src/class-options.php';

const CAPABILITY = 'edit_others_posts';

add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'review-mode', false, basename( dirname( __FILE__ ) ) . '/languages' );
		new Query();
		new Admin_Bar();
		new Options();
	}
);
