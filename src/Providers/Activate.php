<?php
namespace PPerf_Analysis\Providers;

use PPerf_Analysis\Models\Plugin_Run;
use PPerf_Analysis\Models\Run;
use PPerf_Analysis\Pages\Overview_Page;
use PPerf_Analysis\Pages\Plugin_Cumulative_Query_Page;
use PPerf_Analysis\Pages\Settings_Page;
use PPerf_Analysis\Services\Tracking;

class Activate {
	protected static $start = 0;
	protected static $total_query_timing = 0;
	public static $request_id;

	public function register() {
		// Tracking hooks.
		$this->hook_tracking_details();

		// Order matters.
		$this->hook_analysis_pages();
		$this->hook_settings_pages();
	}

	public function hook_tracking_details() {
		$tracker = new Tracking();

		add_action( 'shutdown', [ $tracker, 'on_shutdown' ], 9999 );
		add_action( 'log_query_custom_data', [ $tracker, 'on_query' ], 10, 5 );
	}

	public function hook_analysis_pages() {
		// Add parent page to admin menu
		add_action( 'admin_menu',
			function () {
				$perf_page = new Overview_Page();

				// Add a top-level menu page
				add_menu_page(
					'Plugin Performance Analysis', // Page title
					'Plugin Analysis', // Menu title
					'manage_options', // Capability required to access the page
					PPERF_ANALYSIS_SLUG, // Menu slug
					[ $perf_page, 'render' ] // Callback function to render the page content
				);
			}
		);

		// Add parent page to admin menu
		add_action( 'admin_menu',
			function () {
				$page = new Plugin_Cumulative_Query_Page();

				// Add child page under the parent page
				add_submenu_page(
					PPERF_ANALYSIS_SLUG, // Parent menu slug
					'Cumulative Query Speeds by Plugin', // Page title
					'Cumulative Query Speed', // Menu title
					'manage_options', // Capability required to access the page
					PPERF_ANALYSIS_SLUG . '-cumulative-query-plugin', // Menu slug
					[ $page, 'render' ] // Callback function to render the page content
				);
			}
		);

		add_action( 'admin_enqueue_scripts', function ( $hook ) {
			// Check if the current page is the plugin's admin page
			$pages = ['toplevel_page_' . PPERF_ANALYSIS_SLUG  , 'plugin-analysis_page_' . PPERF_ANALYSIS_SLUG . '-cumulative-query-plugin'];
			if (in_array( $hook,$pages)) {
				$plugin_base_url = plugins_url( '/', PPERF_ANALYSIS_BASE_PATH );
				// Enqueue your JavaScript file
				wp_enqueue_script( 'pperf-d3-js', $plugin_base_url . 'assets/js/chart.min.js', array( 'jquery' ), '1.0', false );

				// Enqueue your CSS file
				//wp_enqueue_style('pperf-d3-css', 'path/to/your-plugin.css', array(), '1.0');
			}
		} );
	}

	public function hook_settings_pages() {
		$settings_page = new Settings_Page();
		add_action( 'admin_init', [ $settings_page, 'register_settings' ] );
		$settings_slug = PPERF_ANALYSIS_SLUG . '-save';
		$clear_slug    = PPERF_ANALYSIS_SLUG . '-clear';

		add_action( "admin_post_$settings_slug", [ $settings_page, 'handle_settings_form_submission' ] );
		add_action( "admin_post_$clear_slug", [ $settings_page, 'handle_clear_database_form_submission' ] );
		// Add parent page to admin menu
		add_action( 'admin_menu',
			function () {
				$settings_page = new Settings_Page();

				// Add child page under the parent page
				add_submenu_page(
					PPERF_ANALYSIS_SLUG, // Parent menu slug
					'Performance Analysis Settings', // Page title
					'Settings', // Menu title
					'manage_options', // Capability required to access the page
					PPERF_ANALYSIS_SLUG . '-settings', // Menu slug
					[ $settings_page, 'render' ] // Callback function to render the page content
				);
			}
		);
	}

}