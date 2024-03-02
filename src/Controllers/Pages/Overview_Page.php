<?php

namespace PPerf_Analysis\Controllers\Pages;

use PPerf_Analysis\Repositories\Chart_Repository;
use PPerf_Analysis\Services\Templates;

class Overview_Page {


	public function register() {
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
	}
	public function render() {
		$chart_repo = new Chart_Repository();

		echo Templates::render_view( 'pages/overview.php', [
			'page_speed_by_plugin_charts'    => $chart_repo->get_page_speed_chart_data(),
			'query_speed_charts'             => $chart_repo->get_query_speed_chart_data(),
			'total_queries_by_plugin_charts' => $chart_repo->get_total_queries_chart_data(),
		] );
	}

}