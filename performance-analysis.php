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

use Perf\Models\Run;
use Perf\Providers\Activate;
use Perf\Page;
const PPERF_ANALYSIS_VERSION = '1.0.0';
const PPERF_ANALYSIS_SLUG   = 'plugin-perf';
require __DIR__.'/vendor/autoload.php';

$act = new  Activate();
$act->register();

// Add parent page to admin menu
add_action('admin_menu',
	function() {
		$perf_page = new Page();
		// Add a top-level menu page
		$parent_page = add_menu_page(
			'Plugin Performance Analysis', // Page title
			'Plugin Perf', // Menu title
			'manage_options', // Capability required to access the page
			PPERF_ANALYSIS_SLUG, // Menu slug
			[$perf_page, 'analysis_overview'] // Callback function to render the page content
		);

		// Add child page under the parent page
		/*add_submenu_page(
			PPERF_ANALYSIS_SLUG, // Parent menu slug
			'Child Page', // Page title
			'Child Page', // Menu title
			'manage_options', // Capability required to access the page
			'child-page', // Menu slug
			'pperf_analysis_other' // Callback function to render the page content
		);*/
	}
);


add_action('admin_enqueue_scripts', function  ($hook) {
	// Check if the current page is the plugin's admin page
	if ($hook === 'toplevel_page_'.PPERF_ANALYSIS_SLUG) {
		$plugin_base_url = plugins_url('/', __FILE__);
		// Enqueue your JavaScript file
		wp_enqueue_script('pperf-d3-js', $plugin_base_url.'assets/js/chart.min.js', array('jquery'), '1.0', false);

		// Enqueue your CSS file
		//wp_enqueue_style('pperf-d3-css', 'path/to/your-plugin.css', array(), '1.0');
	}
});

// @todo Add "per run" stats (table?) - with; plugins activated, wp version, php version (cache plugin?)

// Callback function for the parent page
function pperf_analysis_overview() {
	global $wpdb;
	$run = new Run();
	// @todo Put as a database flag
	// @todo Ignore pages that do not match
	$watched_uris = [
			'/events/list/?tribe-bar-date=2023-05-01',
	];
	echo '<h1>Plugin Performance Overview</h1>';
	echo "<h3>URI's Being Watched</h3>";
	echo "<b>".implode("</b>, <b>",$watched_uris)."</b>";
	echo "<h3>Speed to WP Shutdown</h3>";
	$q = "SELECT DISTINCT request_uri, count(*) FROM {$run->get_table()}
where request_uri in('".implode("','", $watched_uris)."')
group by request_uri
limit 50";

	$results = $wpdb->get_results($q);
	foreach ($results as $row) {
		// Get averages
		$q = "SELECT *
FROM {$run->get_table()}
where request_uri = %s
order by created_datetime DESC
limit 100";
		$res = $wpdb->get_results($wpdb->prepare($q, $row->request_uri));
		foreach ($res as $r) {
			$date = date( 'Y-m-d g:i:sa', strtotime( $r->created_datetime ) );
			echo "<b>$date</b> " . $r->request_uri . " - time to shutdown: " . ( $r->end_time - $r->start_time );
			echo "<br/>";
		}

	}
}


// Callback function for the child page
function pperf_analysis_other() {
	echo '<h1>todo</h1>';
	// Add your child page content here
}

register_activation_hook( __FILE__, static function() {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $wpdb;
	$table_prefix = $wpdb->prefix;
	$schemas = [];

	$table = "{$table_prefix}pperf_run";
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
PRIMARY KEY (`perf_run_id`),
KEY `plugins_version_hash` (`plugins_version_hash`),
KEY `time` (`start_time`,`end_time`)
);";

	foreach ($schemas as $schema) {
		dbDelta( $schema );
	}
} );
