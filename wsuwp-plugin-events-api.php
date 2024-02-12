<?php
/**
 * Plugin Name: WSUWP Plugin Events API
 * Plugin URI: https://github.com/wsuwebteam/wsuwp-plugin-events-api
 * Description: A single standized API for querying events from events.wsu.edu or a site using Modern Tribe.
 * Version: 1.0.1
 * Requires PHP: 7.3
 * Author: Washington State University, Dan White
 * Author URI: https://web.wsu.edu/
 * Text Domain: wsuwp-plugin-events-api
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Initiate plugin
require_once __DIR__ . '/includes/plugin.php';
