<?php

namespace PPerf_Analysis\Controllers\Pages;

use PPerf_Analysis\Repositories\Chart_Repository;
use PPerf_Analysis\Models\Snapshot_Repository;
use PPerf_Analysis\Services\Charts\Page_Speed;
use PPerf_Analysis\Services\Templates;

class Custom_Chart_Page {

	public function register() {
		add_action( 'admin_menu', function () {
			$page = new Custom_Chart_Page();

			// Add child page under the parent page
			add_submenu_page( PPERF_ANALYSIS_SLUG, // Parent menu slug
			                  'Build Customized Chart', // Page title
			                  'Build Chart', // Menu title
			                  'manage_options', // Capability required to access the page
			                  PPERF_ANALYSIS_SLUG . '-custom-chart-builder', // Menu slug
			                  [ $page, 'render' ] // Callback function to render the page content
			);
		} );
	}

	public function render() {
		$chart_repo = new Chart_Repository();
		$vars       = [
			'_wpnonce' => wp_create_nonce(),
			'snapshots' => (new Snapshot_Repository())->prepareQuery()->orderBy('created_datetime', 'DESC')->getAll(),
		];

		$vars = array_merge( $vars, $this->build_report_vars_from_request( $_REQUEST ) );


		echo Templates::render_view( 'pages/build-chart.php', $vars );
	}

	public function build_report_vars_from_request( $request ) {
		$view_vars = [];
		$metrics   = [
			'pg_speed' => Page_Speed::class,
		];

		$snapshots_requested = isset( $request['snapshots'] ) ? $request['snapshots'] : [];
		$metrics_requested   = isset( $request['metrics'] ) ? $request['metrics'] : [];
		$render_pipeline     = [];
		foreach ( $metrics_requested as $metric ) {
			if ( isset( $metrics[ $metric ] ) ) {
				$render_pipeline[] = new $metrics[ $metric ]( 'bar', (array) $snapshots_requested );
			}
		}

		if ( empty( $render_pipeline ) ) {
			return [];
		}

		return $view_vars;
	}
}