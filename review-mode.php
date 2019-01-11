<?php
/**
 * Plugin Name:     Review Mode
 * Text Domain:     review-mode
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Review_Mode
 */

namespace Review_Mode;

require_once dirname( __FILE__ ) . '/src/Query.php';
require_once dirname( __FILE__ ) . '/src/Admin_Bar.php';
require_once dirname( __FILE__ ) . '/src/Options.php';

const CAPABILITY = 'edit_others_posts';

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'review-mode', false, basename( dirname( __FILE__ ) ) . '/languages' );
	new Query();
	new Admin_Bar();
	new Options();
} );
