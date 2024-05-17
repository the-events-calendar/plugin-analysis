<?php
namespace PPerf_Analysis\Services\Charts;
use PPerf_Analysis\Services\Templates;

/**
 * Base class to handle mutating the data into a shape for displaying on a particular chart graphic.
 */
abstract class Abstract_Chart implements Chart_Contract {

	protected array $snapshots = [];
	/**
	 * The type of the chart rendered. By default will only return bar charts.
	 * @var string|null
	 */
	protected ?string $shape = 'bar';

	public function __construct( string $shape,array $snapshots  ) {
		// @todo Shape is not used. May need to rethink approach..
		// @todo .. may effect data structure, may effect rendering/template, may effect pivots and query
		$this->shape = $shape;
		$this->snapshots = $snapshots;
	}

	public function to_d3( array $data ):array {

		$speeds  = [];
		$labels  = [];
		$context = [ 'expandedLabels' => [] ];
		foreach ( $data as $row ) {
			// Array order matters here.
			$labels[]                    = $row->plugin;
			$context['expandedLabels'][] = $row->plugin;
			$speeds[]                    = $row->avg_load_time;
		}

		// Create colors
		$colors_bg     = [];
		$colors_border = [];
		foreach ( $rows as $row ) {
			[$result, $sat, $lum]             = self::color_from_string_two( $row->plugins_version_hash );

			$colors_bg[]     = 'hsla('.$result.', '.$sat.'%, '.$lum.'%, 0.6)';
			$colors_border[] = 'hsl('.$result.', '.$sat.'%, '.$lum.'%)';
			//[$r, $g, $b]             = self::color_from_string( $row->plugins_version_hash );
			//$colors_bg[]     = 'rgba(' . $r . ',' . $g . ',' . $b . ', 0.55)';
			//$colors_border[] = 'rgba(' . $r . ',' . $g . ',' . $b . ', 1)';
		}

		$datasets = [
			[
				'label'           => $label,
				'data'            => $data,
				'borderWidth'     => 2,
				'context'         => $context,
				'backgroundColor' => $colors_bg,
				'borderColor'     => $colors_border,
			],
		];
		$chart    = [
			'type'    => 'bar',
			'data'    => [ 'labels' => $labels, 'datasets' => $datasets ],
			'options' => [ 'scales' => [ 'y' => [ 'beginAtZero' => true ] ] ],
		];

		return [ $speeds, $labels, $context ];
	}

	public function html():string {
		$data = $this->to_d3($this->data(), $this->shape);
		// Stagger render of chart, because it's purdy.
		static $stagger_by = 0; // @todo move?
		$stagger_by += 120;

		return Templates::render_view( 'd3-chart', [
			'stagger_by' => $stagger_by,
			'chart_data' => $data,
			'id'         => uniqid(preg_replace('/[^0-9a-zA-Z]/', '_', $this->name())),
		] );
	}



	// @todo
	public function get_watched_pages() {
		$options = get_option( PPERF_ANALYSIS_SLUG . '-options' );

		return $options['watched_pages'] ?? [];
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
		list( $speeds, $labels, $context, $results ) = $this->compiled_chart_data( $q );
		$charts[] = $this->chart_me( 'All Pages', $speeds, $labels, $context, $results );

		foreach ( $watched_pages as $watched_page ) {
			// Watched
			$q = "SELECT avg(num_queries) as `avg_load_time`, plugins_version_hash,count(*) as `total`
FROM $table
where request_uri = %s
group by plugins_version_hash
limit 10";
			list( $speeds, $labels, $context, $results ) = $this->compiled_chart_data( $this->wpdb->prepare( $q, $watched_page ) );
			$charts[] = $this->chart_me( $watched_page, $speeds, $labels, $context, $results );
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

	public static function color_from_string( $str ) {
		$hash = md5( $str );
		// Convert hash to rgb
		$r  = substr( $hash, 0, 1 );
		$r  = ord( $r );
		$up = substr( $r, 0, 1 ) % 2 === 0;
		if ( $up ) {
			$r = $r < 100 ? $r + 100 : $r;
		} else {
			$r = $r > 50 ? $r - 50 : $r;
		}

		$g = substr( $hash, 6, 1 );
		$g = ord( $g );
		if ( $up ) {
			$g = $g > 90 ? $g - 60 : $g;
		} else {
			$g = $g < 100 ? $g + 120 : $g;
		}

		$b = substr( $hash, - 1, 1 );
		$b = ord( $b );
		if ( $up ) {
			$b = $b < 100 ? $b + 100 : $b;
		} else {
			$b = $b > 30 ? $b - 30 : $b;
		}

		return [ round( $r ), round( $g ), round( $b ) ];
	}

	public static function hue2rgb($p, $q, $t) {
		if($t < 0) $t += 1;
		if($t > 1) $t -= 1;
		if($t < 1/6) return $p + ($q - $p) * 6 * $t;
		if($t < 1/2) return $q;
		if($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
		return $p;
	}
	public static function hslToRgb($h, $s, $l) {
		$r = 0; $g= 0; $b = 0;
		if ($s === 0) {
			$r = $g = $b = $l;
		} else {
			$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
			$p = 2 * $l - $q;
			$r = self::hue2rgb($p, $q, $h + 1/3);
			$g = self::hue2rgb($p, $q, $h);
			$b = self::hue2rgb($p, $q, $h - 1/3);
		}

		return [round($r * 255), round($g * 255), round($b * 255)];
	}
	public static function color_from_string_two($input) {
		$hash = $input;
		$chara = substr($hash,10,1);
		$char_int = ord($chara);
		if($char_int % 2 ===0) {
			$charb = substr($hash,2,1);
			$char_int += ord($charb);
		}
		$up = isset($charb);
		$result = 0;
		$sat =  ord(substr($hash,3,1)) / 2;
		$lum =  ord(substr($hash,4,1)) / 2;
		foreach(str_split($hash) as $i) {
			$result += ord($i)/16;
			if($up) {
				$sat += ord($i) / 16;
				$lum += ord($i) / 16;
			}
			if($chara === 'a') {
				break;
			}
			if($result > $char_int) {
				break;
			}
			if($sat > 80) {
				break;
			}
			if($lum > 70) {
				break;
			}
		}
		if($lum < 50) {
			$lum += 30;
		}
		/*		$rgb = self::hslToRgb($result, $sat, $lum);
				$bright = sqrt( 0.299 * pow($rgb[0], 2) + 0.587 * pow($rgb[1], 2) + 0.114 * pow($rgb[2], 2) );
				if ($bright >= 200) {
					$sat = 60;
				}*/

		return [$result,$sat,$lum];
	}

	public function chart_me( $label, $data, $labels, $context, $rows ) {
		// Create colors
		$colors_bg     = [];
		$colors_border = [];
		foreach ( $rows as $row ) {
			[$result, $sat, $lum]             = self::color_from_string_two( $row->plugins_version_hash );

			$colors_bg[]     = 'hsla('.$result.', '.$sat.'%, '.$lum.'%, 0.6)';
			$colors_border[] = 'hsl('.$result.', '.$sat.'%, '.$lum.'%)';
			//[$r, $g, $b]             = self::color_from_string( $row->plugins_version_hash );
			//$colors_bg[]     = 'rgba(' . $r . ',' . $g . ',' . $b . ', 0.55)';
			//$colors_border[] = 'rgba(' . $r . ',' . $g . ',' . $b . ', 1)';
		}

		$datasets = [
			[
				'label'           => $label,
				'data'            => $data,
				'borderWidth'     => 2,
				'context'         => $context,
				'backgroundColor' => $colors_bg,
				'borderColor'     => $colors_border,
			],
		];
		$chart    = [
			'type'    => 'bar',
			'data'    => [ 'labels' => $labels, 'datasets' => $datasets ],
			'options' => [ 'scales' => [ 'y' => [ 'beginAtZero' => true ] ] ],
		];

		return $chart;
	}

	public function get_query_speed_by_plugin_charts(): array {
		return $this->get_query_speed_by_plugin_chart_data();
	}

	public function get_chart( $id, array $chart ) {
		// Stagger render of chart, because it's purdy.
		static $stagger_by = 0;
		$stagger_by += 120;

		return Templates::render_view( 'd3-chart', [
			'stagger_by' => $stagger_by,
			'chart_data' => $chart,
			'id'         => $id,
		] );
	}


	////////////////////////////////////////////

	// @todo
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

		return [ $speeds, $labels, $context, $res ];
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
			list( $speeds, $labels, $context, $results ) = $this->compiled_per_plugin_chart_data( $this->wpdb->prepare( $q, $plugin->plugin_name ) );
			$charts[ $plugin->plugin_name ] [] = $this->chart_me( 'All Pages', $speeds, $labels, $context, $results );

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
				list( $speeds, $labels, $context, $results ) = $this->compiled_per_plugin_chart_data( $this->wpdb->prepare( $q, $watched_page, $plugin->plugin_name ) );
				$charts[ $plugin->plugin_name ] [] = $this->chart_me( $watched_page, $speeds, $labels, $context, $results );
			}
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

	public function chart_me( $label, $data, $labels, $context, $rows ) {
		// Create colors
		$colors_bg     = [];
		$colors_border = [];
		foreach ( $rows as $row ) {
			[$result, $sat, $lum]             = self::color_from_string_two( $row->plugins_version_hash );

			$colors_bg[]     = 'hsla('.$result.', '.$sat.'%, '.$lum.'%, 0.6)';
			$colors_border[] = 'hsl('.$result.', '.$sat.'%, '.$lum.'%)';
			//[$r, $g, $b]             = self::color_from_string( $row->plugins_version_hash );
			//$colors_bg[]     = 'rgba(' . $r . ',' . $g . ',' . $b . ', 0.55)';
			//$colors_border[] = 'rgba(' . $r . ',' . $g . ',' . $b . ', 1)';
		}

		$datasets = [
			[
				'label'           => $label,
				'data'            => $data,
				'borderWidth'     => 2,
				'context'         => $context,
				'backgroundColor' => $colors_bg,
				'borderColor'     => $colors_border,
			],
		];
		$chart    = [
			'type'    => 'bar',
			'data'    => [ 'labels' => $labels, 'datasets' => $datasets ],
			'options' => [ 'scales' => [ 'y' => [ 'beginAtZero' => true ] ] ],
		];

		return $chart;
	}

	public function get_query_speed_by_plugin_charts(): array {
		return $this->get_query_speed_by_plugin_chart_data();
	}

	public function get_chart( $id, array $chart ) {
		// Stagger render of chart, because it's purdy.
		static $stagger_by = 0;
		$stagger_by += 120;

		return Templates::render_view( 'd3-chart', [
			'stagger_by' => $stagger_by,
			'chart_data' => $chart,
			'id'         => $id,
		] );
	}
}