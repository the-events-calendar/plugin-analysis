<?php
/**
 * Plugin Name: 1 - Plugin Performance Analysis
 * Plugin URI: https://example.com/my-plugin
 * Description: This is my plugin description.
 * Version: 1.0.0
 * Author: John Doe
 * Author URI: https://example.com/
 * License: GPL2
 */

use PPerf_Analysis\Providers\Activate;
use PPerf_Analysis\StellarWP\DB\DB;

const PPERF_ANALYSIS_VERSION   = '1.0.0';
const PPERF_ANALYSIS_SLUG      = 'plugin-perf';
const PPERF_ANALYSIS_BASE_PATH = __FILE__;

require __DIR__ . '/vendor/autoload.php';

$act = new  Activate();
$act->register();
add_action( 'plugins_loaded', function() {
	DB::init();
}, 0 );

register_activation_hook( __FILE__, static function () {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;
	$table_prefix = $wpdb->prefix;
	$schemas      = [];

	$table     = "{$table_prefix}pperf_run";
	$schemas[] = "CREATE TABLE `$table` (
`perf_run_id` int(11) NOT NULL AUTO_INCREMENT,
`start_time` varchar(50) NOT NULL,
`end_time` varchar(50) NOT NULL,
`request_id` varchar(145) NOT NULL,
`request_uri` varchar(350) NOT NULL,
`num_queries` int(11) DEFAULT NULL,
`active_plugins` text,
`hooks` text,
`plugins_version_hash` varchar(45) NOT NULL,
`created_datetime` datetime DEFAULT NULL,
`total_query_time` varchar(50) NOT NULL,
PRIMARY KEY (`perf_run_id`),
KEY `plugins_version_hash` (`plugins_version_hash`),
KEY `time` (`start_time`,`end_time`)
);";

	foreach ( $schemas as $schema ) {
		dbDelta( $schema );
	}
} );
