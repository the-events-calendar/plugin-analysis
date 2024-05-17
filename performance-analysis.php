<?php
/**
 * Plugin Name: Plugin Analysis
 * Description: Track various page and plugin performance metrics over time against previous plugin versions. Useful
 * for monitoring performance of upcoming vs. past releases holistically.
 * Version: 1.5.0
 * License: GPL2
 * Author: The Events Calendar
 * Text Domain: plugin-perf-analysis
 */

use PPerf_Analysis\Controllers\Activate;
use PPerf_Analysis\StellarWP\DB\DB;
use PPerf_Analysis\StellarWP\DB\Config;
use PPerf_Analysis\lucatume\DI52\Container;

const PPERF_ANALYSIS_VERSION   = '1.5.0';
const PPERF_ANALYSIS_SLUG      = 'pperf-analysis';
const PPERF_HOOK_PREFIX        = 'pperf_analysis';
const PPERF_TEXT_DOMAIN        = 'plugin-perf-analysis';
const PPERF_ANALYSIS_BASE_PATH = __FILE__;
define( 'PPERF_ANALYSIS_BASE_DIR', plugin_dir_path( __FILE__ ) );
define( 'PPERF_ANALYSIS_BASE_URL', plugins_url( '/', __FILE__ ) );

require __DIR__ . '/vendor/autoload.php';

// Initialize the plugin.
try {
	/** @var Activate $plugin */
	$plugin = ( new Container() )->get( Activate::class );
	$plugin->register();
} catch ( \Exception $e ) {
	$message = 'The Plugin Analysis plugin caught the following error: %s';
	trigger_error( sprintf( $message, $e->getMessage() ), E_USER_ERROR );
}

function pperf_get_run_table_name() {
	global $wpdb;
	$table_prefix = $wpdb->prefix;

	return "{$table_prefix}pperf_run";
}

function pperf_get_snapshot_table_name() {
	global $wpdb;
	$table_prefix = $wpdb->prefix;

	return "{$table_prefix}pperf_snapshot";
}

function pperf_get_plugin_run_table_name() {
	global $wpdb;
	$table_prefix = $wpdb->prefix;

	return "{$table_prefix}pperf_plugin_run";
}


add_action( 'plugins_loaded', function () {
	Config::setHookPrefix( PPERF_HOOK_PREFIX );
	DB::init();
}, 0 );

register_activation_hook( __FILE__, static function () {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$schemas          = [];
	$plugin_run_table = pperf_get_plugin_run_table_name();
	$run_table        = pperf_get_run_table_name();
	$snapshot_table   = pperf_get_snapshot_table_name();

	$schemas[] = "CREATE TABLE `$run_table` (
`perf_run_id` bigint NOT NULL AUTO_INCREMENT,
`start_time` varchar(50) NOT NULL,
`end_time` varchar(50) NOT NULL,
`request_id` varchar(145) NOT NULL,
`request_uri` varchar(350) NOT NULL,
`num_queries` int(11) DEFAULT NULL,
`hooks` text,
`plugins_version_hash` varchar(45) NOT NULL,
`created_datetime` datetime DEFAULT NULL,
`total_query_time` varchar(50) NOT NULL,
PRIMARY KEY (`perf_run_id`),
KEY `plugins_version_hash` (`plugins_version_hash`),
KEY `time` (`start_time`,`end_time`)
);";

	$schemas[] = "CREATE TABLE `$plugin_run_table` (
`perf_plugin_run_id` bigint NOT NULL AUTO_INCREMENT,
`perf_run_id` bigint NOT NULL,
`num_queries` int(11) DEFAULT NULL,
`plugin_name` varchar(75) NOT NULL,
`plugin_version` varchar(10) NOT NULL,
`created_datetime` datetime DEFAULT NULL,
`total_query_time` varchar(50) NOT NULL,
PRIMARY KEY (`perf_plugin_run_id`),
KEY (`perf_run_id`),
KEY (`total_query_time`)
);";

	$schemas[] = "CREATE TABLE `$snapshot_table` (
`plugins_version_hash` varchar(80) NOT NULL,
`active_plugins` text,
`created_datetime` datetime DEFAULT NULL,
PRIMARY KEY (`plugins_version_hash`)
);";
	foreach ( $schemas as $schema ) {
		dbDelta( $schema );
	}
} );
