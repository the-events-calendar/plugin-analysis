<?php

namespace PPerf_Analysis\Pages;

use PPerf_Analysis\Repositories\Chart_Repository;
use PPerf_Analysis\Services\Templates;

class Overview_Page {

	public function render() {
		$chart_repo = new Chart_Repository();

		echo Templates::render_view( 'pages/overview.php', [
			'page_speed_by_plugin_charts'    => $chart_repo->get_page_speed_chart_data(),
			'query_speed_charts'             => $chart_repo->get_query_speed_chart_data(),
			'total_queries_by_plugin_charts' => $chart_repo->get_total_queries_chart_data(),
		] );
	}

}