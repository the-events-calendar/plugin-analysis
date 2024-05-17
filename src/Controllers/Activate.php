<?php

namespace PPerf_Analysis\Controllers;

class Activate {

	public function register() {
		// Tracking.
		( new Tracking() )->register();

		// Hey world, we are here.
		do_action( PPERF_HOOK_PREFIX . '_app_register' );

		// Pages. Order matters.
		( new Pages\Overview_Page() )->register();
		( new Pages\Custom_Chart_Page() )->register();
		( new Pages\Plugin_Cumulative_Query_Page() )->register();
		( new Pages\Settings_Page() )->register();

		if ( is_admin() ) {
			$this->enqueue_admin_resources();
		}
	}

	public function enqueue_admin_resources() {
		// Our chart and boostrap global libs.

		add_action( 'admin_enqueue_scripts', function ( $hook ) {
			// Check if the current page is the plugin's admin page
			$pages = [
				'toplevel_page_' . PPERF_ANALYSIS_SLUG,
				'plugin-analysis_page_' . PPERF_ANALYSIS_SLUG . '-cumulative-query-plugin',
				'plugin-analysis_page_' . PPERF_ANALYSIS_SLUG . '-custom-chart-builder',
			];
			if ( in_array( $hook, $pages ) ) {
				$plugin_base_url = plugins_url( '/', PPERF_ANALYSIS_BASE_PATH );
				// Enqueue your JavaScript file
				wp_enqueue_script( 'pperf-d3-js', $plugin_base_url . 'resources/js/chart.min.js', array( 'jquery' ), '1.0', false );

				// Enqueue your CSS file
				wp_enqueue_style( 'pperf-bootstrap-grid-css', $plugin_base_url . 'resources/bootstrap-5.3.2-dist/css/bootstrap-grid.css', array(), '1.0' );
			}
		} );
	}
}