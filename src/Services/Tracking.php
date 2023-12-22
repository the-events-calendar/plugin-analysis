<?php
namespace PPerf_Analysis\Services;

use PPerf_Analysis\Models\Plugin_Run;
use PPerf_Analysis\Models\Run;

class Tracking {
	protected static $start = 0;
	protected static $total_query_timing = 0;
	public static $request_id;

	public function __construct() {
		$this->init();
	}

	public function init() {
		self::$start      = microtime( true );
		self::$request_id = uniqid();
		if(!defined('SAVEQUERIES')) {
			define( 'SAVEQUERIES', true );
		}

		if ( property_exists( $GLOBALS['wpdb'], 'save_queries' ) ) {
			$GLOBALS['wpdb']->save_queries = true;
		}
	}

	public function get_request_id() {
		return self::$request_id;
	}

	protected $all = [];

	public function on_all( $name ) {
		//$args = func_get_args();
		if ( isset( $this->last ) ) {
			$span = microtime( true ) - $this->last_time;
			$key  = "{$this->last} -> {$name}";
			// Opt for worst performing.
			if ( ! isset( $this->all[ $key ] ) || $span > $this->all[ $key ] ) {
				$this->all[ $key ] = $span;
			}
		}

		$this->last      = $name;
		$this->last_time = microtime( true );
	}

	protected $trace = [];
	protected $query_speed = [];

	public function on_query( $query_data, $query, $query_duration_ms, $query_callstack, $query_start ) {
		$this->trace[]       = debug_backtrace( 2 );
		$this->query_speed[] = $query_duration_ms;
		// @todo count time
		// @todo count query
		self::$total_query_timing += $query_duration_ms;

		return $query_data;
	}

	public function on_shutdown() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		global $wpdb;

		$end                   = microtime( true );
		$run                   = new Run();
		$run->request_id       = self::$request_id;
		$run->start_time       = self::$start;
		$run->request_uri      = $_SERVER['REQUEST_URI'] ?? '';
		$run->end_time         = $end;
		// @todo Add field for WP version.
		$run->num_queries      = $wpdb->num_queries;
		$run->total_query_time = self::$total_query_timing;

		$serialize_plugin_data = [];
		// Generate plugin data.
		$active_plugins = get_option( 'active_plugins' );
		foreach ( $active_plugins as $plugin ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

			// Get the version from plugin data
			$serialize_plugin_data[trim( $plugin_data['Name']) ] = $plugin_data['Version'];
		}

		// @todo Too big? Save separate table?
		// @todo $run->hooks = $this->all;
		ksort( $serialize_plugin_data );
		$cereal = serialize($serialize_plugin_data);
		$run->active_plugins       = $cereal;
		$run->plugins_version_hash = sha1( $cereal );
		$run->created_datetime     = date( 'Y-m-d H:i:s', time() );
		$run->save();

		// @todo Add per plugin data
		Plugin_Run::collect_plugins_on_run($run, $this->trace, $this->query_speed );
	}


}