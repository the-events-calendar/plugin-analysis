<?php

namespace PPerf_Analysis\Pages;

use PPerf_Analysis\Repositories\Chart_Repository;
use PPerf_Analysis\Services\Charts\Page_Speed;
use PPerf_Analysis\Services\Templates;

class Custom_Chart_Page {

	public function render() {
		$chart_repo = new Chart_Repository();
		$vars = [
			'_wpnonce' => wp_create_nonce()
		];

		$vars = array_merge($vars, $this->build_report_vars_from_request($_REQUEST));


		echo Templates::render_view( 'pages/build-chart.php', $vars);
	}

	public function build_report_vars_from_request( $request ) {
		$view_vars = [];
		$metrics = [
			'pg_speed' => Page_Speed::class
		];

		$snapshots_requested =isset($request['snapshots']) ? $request['snapshots'] : [];
		$metrics_requested = isset($request['metrics']) ? $request['metrics'] : [];
		$render_pipeline = [];
		foreach ($metrics_requested as $metric) {
			if(isset($metrics[$metric])) {
				$render_pipeline[] = new $metrics[$metric]('bar', (array)$snapshots_requested);
			}
		}

		if(empty($render_pipeline)) {
			return [];
		}

		return $view_vars;
	}
}