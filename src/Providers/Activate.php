<?php

namespace PPerf_Analysis\Providers;
use PPerf_Analysis\Analysis_Page;
use PPerf_Analysis\Models\Run;
use PPerf_Analysis\Settings_Page;

class Activate {
	protected static $start = 0;
	protected static $total_query_timing = 0;
	public static $request_id;
	public function register() {
		self::$start = microtime(true);
		self::$request_id = uniqid();
		add_action('shutdown', [$this, 'on_shutdown'],9999);
		add_action( 'log_query_custom_data', [$this, 'on_query'],10,5 );
		$perf_page = new Analysis_Page();
		$settings_page = new Settings_Page();
		add_action('admin_init', [$settings_page,'register_settings']);
		$action_slug = PPERF_ANALYSIS_SLUG.'-save';

		add_action("admin_post_$action_slug", [$settings_page,'handle_settings_form_submission']);
// Add parent page to admin menu
		add_action('admin_menu',
			function() {
				$perf_page = new Analysis_Page();
				$settings_page = new Settings_Page();


				// Add a top-level menu page
				add_menu_page(
					'Plugin Performance Analysis', // Page title
					'Plugin Perf', // Menu title
					'manage_options', // Capability required to access the page
					PPERF_ANALYSIS_SLUG, // Menu slug
					[$perf_page, 'render'] // Callback function to render the page content
				);

				// Add child page under the parent page
				add_submenu_page(
					PPERF_ANALYSIS_SLUG, // Parent menu slug
					'Performance Analysis Settings', // Page title
					'Settings', // Menu title
					'manage_options', // Capability required to access the page
					PPERF_ANALYSIS_SLUG.'-settings', // Menu slug
					[$settings_page, 'render'] // Callback function to render the page content
				);
			}
		);

		// @todo All is useless data at this point. Need more definitive information.
		//add_action('all', [$this, 'on_all'],0);

		add_action('admin_enqueue_scripts', function  ($hook) {
			// Check if the current page is the plugin's admin page
			if ($hook === 'toplevel_page_'.PPERF_ANALYSIS_SLUG) {
				$plugin_base_url = plugins_url('/', PPERF_ANALYSIS_BASE_PATH);
				// Enqueue your JavaScript file
				wp_enqueue_script('pperf-d3-js', $plugin_base_url.'assets/js/chart.min.js', array('jquery'), '1.0', false);

				// Enqueue your CSS file
				//wp_enqueue_style('pperf-d3-css', 'path/to/your-plugin.css', array(), '1.0');
			}
		});
	}

	public function init() {
		if ( !defined('SAVEQUERIES') && property_exists( $GLOBALS['wpdb'], 'save_queries' ) ) {
			define('SAVEQUERIES', true);
			$GLOBALS['wpdb']->save_queries = true;
		}
	}

	public function get_request_id() {
		return self::$request_id;
	}

	protected $all = [];
	public function on_all( $name ) {
		//$args = func_get_args();
		if(isset($this->last)) {
			$span = microtime( true ) - $this->last_time;
			$key ="{$this->last} -> {$name}";
			// Opt for worst performing.
			if(!isset($this->all[$key]) || $span >   $this->all[$key]) {
				$this->all[ $key ] = $span;
			}
		}

		$this->last = $name;
		$this->last_time = microtime(true);
	}

	public function on_query($query_data, $query, $query_duration_ms, $query_callstack, $query_start) {
		self::$total_query_timing += $query_duration_ms;
		return $query_data;
	}

	public function on_shutdown() {
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		global $wpdb;

		$end = microtime(true);
		$run = new Run();
		$run->request_id = self::$request_id;
		$run->start_time = self::$start;
		$run->request_uri = $_SERVER['REQUEST_URI'];
		$run->end_time = $end;
		$run->num_queries = $wpdb->num_queries;
		$run->total_query_time = self::$total_query_timing;

		$serialize_plugin_data = [];
		// Generate plugin data.
		$active_plugins = get_option('active_plugins');
		foreach ($active_plugins as $plugin) {
			$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

			// Get the version from plugin data
			$serialize_plugin_data[$plugin_data['Name']] = $plugin_data['Version'];
		}

// @todo Too big? Save separate table?
		// @todo $run->hooks = $this->all;
		ksort($serialize_plugin_data );
		$run->active_plugins = serialize($serialize_plugin_data);
		$run->plugins_version_hash = sha1(serialize($serialize_plugin_data));
		$run->created_datetime = date('Y-m-d H:i:s', time());
		$run->save();
	}
}