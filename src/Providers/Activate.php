<?php

namespace Perf\Providers;
use Perf\Models\Run;

class Activate {
	protected static $start = 0;
	public static $request_id;
	public function register() {
		self::$start = microtime(true);
		self::$request_id = uniqid();
		add_action('shutdown', [$this, 'on_shutdown'],9999);
		add_action('all', [$this, 'on_all'],0);

	}

	public function init() {
		if ( !defined('SAVEQUERIES') && property_exists( $GLOBALS['wpdb'], 'save_queries' ) ) {
			define('SAVEQUERIES', true);
			$GLOBALS['wpdb']->save_queries = true;
		}
	}

	public function get_request_id() {
		return self::$request_id;
	}

	protected $all = [];
	public function on_all( $name ) {
		//$args = func_get_args();
		if(isset($this->last)) {
			$span = microtime( true ) - $this->last_time;
			$key ="{$this->last} -> {$name}";
			// Opt for worst performing.
			if(!isset($this->all[$key]) || $span >   $this->all[$key]) {
				$this->all[ $key ] = $span;
			}
		}

		$this->last = $name;
		$this->last_time = microtime(true);
	}

	public function on_shutdown() {
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		global $wpdb;

		$end = microtime(true);
		$run = new Run();
		$run->request_id = self::$request_id;
		$run->start_time = self::$start;
		$run->request_uri = $_SERVER['REQUEST_URI'];
		$run->end_time = $end;
		$run->num_queries = $wpdb->num_queries;
		$serialize_plugin_data = [];
		// Generate plugin data.
		$active_plugins = get_option('active_plugins');
		foreach ($active_plugins as $plugin) {
			$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

			// Get the version from plugin data
			$serialize_plugin_data[$plugin_data['Name']] = $plugin_data['Version'];
		}

// @todo Too big? Save separate table?
		// @todo $run->hooks = $this->all;
		$run->active_plugins = $serialize_plugin_data;

		$run->created_datetime = date('Y-m-d H:i:s', time());
		$run->save();
	}
}