<?php
/**
 * Plugin Name: WAM License
 * Plugin URI:
 * Description: Generates XML license for WAM products.
 * Version: 1.2
 * Author: AJ Tek Corporation
 * Author URI:
 * Text Domain: wamlicense
 */

namespace wamlicense;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require plugin_dir_path( __FILE__ ) . '/class-wamlicense.php';
require plugin_dir_path( __FILE__ ) . '/class-downloads-template.php';

new WAMLicense();
