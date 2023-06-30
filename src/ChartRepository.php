<?php

namespace Perf;

class ChartRepository {


	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function get_query_speed_chart_data() {
		$watched_pages = [
			'/events/list/?tribe-bar-date=2023-05-01',
			'/events/',
		];

		$charts = [];
// All
		$q       = "SELECT avg(total_query_time) as `avg_load_time`, plugins_version_hash
FROM wp_pperf_run
group by plugins_version_hash
limit 10";
		$res     = $this->wpdb->get_results( $q );
		$speeds  = [];
		$labels  = [];
		$context = [ 'expandedLabels' => [] ];
		foreach ( $res as $r ) {
			$plugins = $this->get_plugins_from_hash( $r->plugins_version_hash );

			// Array order matters here.
			$labels[]                    = $this->get_shortened_label_from_plugins( $plugins );
			$context['expandedLabels'][] = $this->get_expanded_label_from_plugins( $plugins );
			$speeds[]                    = $r->avg_load_time;
		}

		// Put in Chart format.
		$charts[] = $this->chart_me( 'All Pages', $speeds, $labels, $context );

		foreach ( $watched_pages as $watched_page ) {
			// Watched
			$q       = "SELECT avg(total_query_time) as `avg_load_time`, plugins_version_hash
FROM wp_pperf_run
where request_uri = %s
group by plugins_version_hash
limit 10";
			$res     = $this->wpdb->get_results( $this->wpdb->prepare( $q, $watched_page ) );
			$speeds  = [];
			$labels  = [];
			$context = [ 'expandedLabels' => [] ];
			foreach ( $res as $r ) {
				$plugins = $this->get_plugins_from_hash( $r->plugins_version_hash );

				// Array order matters here.
				$labels[]                    = $this->get_shortened_label_from_plugins( $plugins );
				$context['expandedLabels'][] = $this->get_expanded_label_from_plugins( $plugins );
				$speeds[]                    = $r->avg_load_time;
			}

			// Put in Chart format.
			$charts[] = $this->chart_me( $watched_page, $speeds, $labels, $context );
		}



		return $charts;
	}

	public function get_page_speed_chart_data() {
		$watched_pages = [
			'/events/list/?tribe-bar-date=2023-05-01',
			'/events/',
		];

		$charts = [];
// All
		$q       = "SELECT avg(end_time) - avg(start_time) as `avg_load_time`, plugins_version_hash
FROM wp_pperf_run
group by plugins_version_hash
limit 10";
		$res     = $this->wpdb->get_results( $q );
		$speeds  = [];
		$labels  = [];
		$context = [ 'expandedLabels' => [] ];
		foreach ( $res as $r ) {
			$plugins = $this->get_plugins_from_hash( $r->plugins_version_hash );

			// Array order matters here.
			$labels[]                    = $this->get_shortened_label_from_plugins( $plugins );
			$context['expandedLabels'][] = $this->get_expanded_label_from_plugins( $plugins );
			$speeds[]                    = $r->avg_load_time;
		}

		// Put in Chart format.
		$charts[] = $this->chart_me( 'All Pages', $speeds, $labels, $context );

		foreach ( $watched_pages as $watched_page ) {
			// Watched
			$q       = "SELECT avg(end_time) - avg(start_time) as `avg_load_time`, plugins_version_hash
FROM wp_pperf_run
where request_uri = %s
group by plugins_version_hash
limit 10";
			$res     = $this->wpdb->get_results( $this->wpdb->prepare( $q, $watched_page ) );
			$speeds  = [];
			$labels  = [];
			$context = [ 'expandedLabels' => [] ];
			foreach ( $res as $r ) {
				$plugins = $this->get_plugins_from_hash( $r->plugins_version_hash );

				// Array order matters here.
				$labels[]                    = $this->get_shortened_label_from_plugins( $plugins );
				$context['expandedLabels'][] = $this->get_expanded_label_from_plugins( $plugins );
				$speeds[]                    = $r->avg_load_time;
			}

			// Put in Chart format.
			$charts[] = $this->chart_me( $watched_page, $speeds, $labels, $context );
		}



		return $charts;
	}

	public function get_expanded_label_from_plugins( array $plugins ) {
		$full_labels = [];
		foreach ( $plugins as $plugin => $version ) {
			$full_labels[] = "$plugin $version";
		}

		return implode( "\n", $full_labels );
	}

	public function get_shortened_label_from_plugins( array $plugins, int $max_length = 22 ) {
		$short_labels = array_map( function ( $plugin ) use ( $plugins ) {
			return ucwords( trim( preg_replace( '/\b(\w)|./', '$1', $plugin ) ) ) . ' ' . $plugins[ $plugin ];
		}, array_keys( $plugins ) );

		$abbrev_label = implode( ', ', $short_labels );
		if ( strlen( $abbrev_label ) > $max_length ) {
			return substr( $abbrev_label, 0, $max_length ) . '...';
		}

		return $abbrev_label;
	}


	public function get_plugins_from_hash( $hash ): array {
		$q = "SELECT active_plugins FROM wp_pperf_run WHERE plugins_version_hash=%s LIMIT 1";

		return json_decode( $this->wpdb->get_var( $this->wpdb->prepare( $q, $hash ) ), true );
	}

	public function chart_me( $label, $data, $labels, $context ) {
		$datasets = [
			[
				'label'       => $label,
				'data'        => $data,
				'borderWidth' => 2,
				'context'     => $context
			]
		];
		$chart    = [
			'type'    => 'bar',
			'data'    => [ 'labels' => $labels, 'datasets' => $datasets ],
			'options' => [ 'scales' => [ 'y' => [ 'beginAtZero' => true ] ] ]
		];

		return $chart;
	}

	public function get_query_speed_by_plugin_charts():array {
		$charts = $this->get_query_speed_chart_data();
		$html   = [];
		foreach ( $charts as $i => $chart ) {
			$html[]=   $this->get_chart( "pperf_main_qry_spd_$i", $chart   );
		}

		return $html;
	}

	public function get_page_speed_by_plugin_charts():array {
		$charts = $this->get_page_speed_chart_data();
		$html   = [];
		foreach ( $charts as $i => $chart ) {
			$html[]=   $this->get_chart( "pperf_main_pg_spd_$i", $chart   );
		}

		return $html;
	}

	public function get_chart( $id, array $chart ) {
		$chart_json = json_encode( $chart );

		return <<<STR
<canvas id="{$id}"></canvas>
<script>
	jQuery( document ).ready(function() {
		const ctx = document.getElementById('{$id}');
        const chartData = {$chart_json};
        chartData.options = {
    		plugins: {
                tooltip: {
      				callbacks: {
                        title: function(item) { 
                           const index = item[0].dataIndex;
                           return item[0].dataset.context.expandedLabels[index]
                       }
      				}
		    	},
		    }
        };
		new Chart(ctx, chartData);
	});
</script>
STR;
	}
}