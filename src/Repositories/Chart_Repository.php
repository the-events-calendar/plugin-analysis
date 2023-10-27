<?php

namespace PPerf_Analysis\Repositories;

class Chart_Repository {

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function get_watched_pages() {
		$options = get_option( PPERF_ANALYSIS_SLUG . '-options' );

		return $options['watched_pages'] ?? [];
	}

	public function compiled_per_plugin_chart_data( $q ) {
		$res     = $this->wpdb->get_results( $q );
		$speeds  = [];
		$labels  = [];
		$context = [ 'expandedLabels' => [] ];
		foreach ( $res as $row ) {
			// Array order matters here.
			$labels[]                    = $row->plugin;
			$context['expandedLabels'][] = $row->plugin;
			$speeds[]                    = $row->avg_load_time;
		}

		return [ $speeds, $labels, $context ];
	}

	public function compiled_chart_data( $q ) {
		$res     = $this->wpdb->get_results( $q );
		$speeds  = [];
		$labels  = [];
		$context = [ 'expandedLabels' => [] ];
		foreach ( $res as $row ) {
			// Array order matters here.
			$labels[]                    = $this->get_shortened_label( $row );
			$context['expandedLabels'][] = $this->get_expanded_label( $row );
			$speeds[]                    = $row->avg_load_time;
		}

		return [ $speeds, $labels, $context ];
	}


	public function get_query_speed_by_plugin_chart_data() {

		$watched_pages = $this->get_watched_pages();
		$table         = pperf_get_plugin_run_table_name();
		$run_table     = pperf_get_run_table_name();
		$charts        = [];

		// Get plugin names.
		$q    = "SELECT DISTINCT
    $table.plugin_name
FROM
    $table";
		$rows = $this->wpdb->get_results( $q );
		foreach ( $rows as $plugin ) {
			$charts[ $plugin->plugin_name ] = [];
			$q                              = "SELECT 
    CONCAT($table.plugin_name,
            ' ',
            $table.plugin_version) AS `plugin`,
    AVG($table.total_query_time) AS `avg_load_time`,
    COUNT(*) AS `total`
FROM
    $table
        INNER JOIN
    $run_table ON $run_table.perf_run_id = $table.perf_run_id
WHERE $table.plugin_name = %s
GROUP BY `plugin`
LIMIT 10";

			// Put in Chart format.
			list( $speeds, $labels, $context ) = $this->compiled_per_plugin_chart_data( $this->wpdb->prepare( $q, $plugin->plugin_name ) );
			$charts[ $plugin->plugin_name ] [] = $this->chart_me( 'All Pages', $speeds, $labels, $context );

			foreach ( $watched_pages as $watched_page ) {
				// Watched
				$q = "SELECT 
    CONCAT($table.plugin_name,
            ' ',
            $table.plugin_version) AS `plugin`,
    AVG($table.total_query_time) AS `avg_load_time`,
    COUNT(*) AS `total`
FROM
    $table
        INNER JOIN
    $run_table ON $run_table.perf_run_id = $table.perf_run_id
WHERE
    $run_table.request_uri = %s
        AND $table.plugin_name = %s
GROUP BY `plugin`
LIMIT 10";
				list( $speeds, $labels, $context ) = $this->compiled_per_plugin_chart_data( $this->wpdb->prepare( $q, $watched_page, $plugin->plugin_name ) );
				$charts[ $plugin->plugin_name ] [] = $this->chart_me( $watched_page, $speeds, $labels, $context );
			}
		}

		return $charts;
	}

	public function get_query_speed_chart_data() {
		$watched_pages = $this->get_watched_pages();
		$table         = pperf_get_run_table_name();
		$charts        = [];
// All
		$q = "SELECT avg(total_query_time) as `avg_load_time`, plugins_version_hash, count(*) as `total`
FROM $table
group by plugins_version_hash
limit 10";

		// Put in Chart format.
		list( $speeds, $labels, $context ) = $this->compiled_chart_data( $q );
		$charts[] = $this->chart_me( 'All Pages', $speeds, $labels, $context );

		foreach ( $watched_pages as $watched_page ) {
			// Watched
			$q = "SELECT avg(total_query_time) as `avg_load_time`, plugins_version_hash, count(*) as `total`
FROM $table
where request_uri = %s
group by plugins_version_hash
limit 10";
			list( $speeds, $labels, $context ) = $this->compiled_chart_data( $this->wpdb->prepare( $q, $watched_page ) );
			$charts[] = $this->chart_me( $watched_page, $speeds, $labels, $context );
		}

		return $charts;
	}

	public function get_page_speed_chart_data() {
		$watched_pages = $this->get_watched_pages();
		$table         = pperf_get_run_table_name();
		$charts        = [];
// All
		$q = "SELECT avg(end_time) - avg(start_time) as `avg_load_time`, plugins_version_hash,count(*) as `total`
FROM $table
group by plugins_version_hash
limit 10";
		// Put in Chart format.
		list( $speeds, $labels, $context ) = $this->compiled_chart_data( $q );
		$charts[] = $this->chart_me( 'All Pages', $speeds, $labels, $context );

		foreach ( $watched_pages as $watched_page ) {
			// Watched
			$q = "SELECT avg(end_time) - avg(start_time) as `avg_load_time`, plugins_version_hash,count(*) as `total`
FROM $table
where request_uri = %s
group by plugins_version_hash
limit 10";
			list( $speeds, $labels, $context ) = $this->compiled_chart_data( $this->wpdb->prepare( $q, $watched_page ) );
			$charts[] = $this->chart_me( $watched_page, $speeds, $labels, $context );
		}

		return $charts;
	}

	public function get_total_queries_chart_data() {
		$watched_pages = $this->get_watched_pages();
		$table         = pperf_get_run_table_name();
		$charts        = [];
// All
		$q = "SELECT avg(num_queries) as `avg_load_time`, plugins_version_hash,count(*) as `total`
FROM $table
group by plugins_version_hash
limit 10";
		// Put in Chart format.
		list( $speeds, $labels, $context ) = $this->compiled_chart_data( $q );
		$charts[] = $this->chart_me( 'All Pages', $speeds, $labels, $context );

		foreach ( $watched_pages as $watched_page ) {
			// Watched
			$q = "SELECT avg(num_queries) as `avg_load_time`, plugins_version_hash,count(*) as `total`
FROM $table
where request_uri = %s
group by plugins_version_hash
limit 10";
			list( $speeds, $labels, $context ) = $this->compiled_chart_data( $this->wpdb->prepare( $q, $watched_page ) );
			$charts[] = $this->chart_me( $watched_page, $speeds, $labels, $context );
		}

		return $charts;
	}

	public function get_expanded_label( $row ) {
		$plugins     = $this->get_plugins_from_hash( $row->plugins_version_hash );
		$full_labels = [];
		foreach ( $plugins as $plugin => $version ) {
			$full_labels[] = "$plugin $version";
		}
		$plugins_expanded = implode( "\n", $full_labels );

		return <<<STR
Page Hits: {$row->total}

Active Plugins:
{$plugins_expanded}

STR;
	}

	public function get_shortened_label( $row, int $max_length = 22 ) {
		$plugins      = $this->get_plugins_from_hash( $row->plugins_version_hash );
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
		$table = pperf_get_run_table_name();
		$q     = "SELECT active_plugins FROM $table WHERE plugins_version_hash=%s LIMIT 1";

		return unserialize( $this->wpdb->get_var( $this->wpdb->prepare( $q, $hash ) ) );
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

	public function get_query_speed_by_plugin_charts(): array {
		return $this->get_query_speed_by_plugin_chart_data();
	}

	public function get_query_speed_charts(): array {
		$charts = $this->get_query_speed_chart_data();
		$html   = [];
		foreach ( $charts as $i => $chart ) {
			$html[] = $this->get_chart( "pperf_main_qry_spd_$i", $chart );
		}

		return $html;
	}

	public function get_page_speed_by_plugin_charts(): array {
		$charts = $this->get_page_speed_chart_data();
		$html   = [];
		foreach ( $charts as $i => $chart ) {
			$html[] = $this->get_chart( "pperf_main_pg_spd_$i", $chart );
		}

		return $html;
	}

	public function get_total_queries_by_plugin_charts(): array {
		$charts = $this->get_total_queries_chart_data();
		$html   = [];
		foreach ( $charts as $i => $chart ) {
			$html[] = $this->get_chart( "pperf_main_total_qry_$i", $chart );
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