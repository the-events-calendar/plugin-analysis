<?php

namespace PPerf_Analysis\Models;

use PPerf_Analysis\StellarWP\Models\Model;
use PPerf_Analysis\StellarWP\Models\ModelQueryBuilder;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

class Plugin_Run extends Model {
	protected $table = 'pperf_run';
	protected $primary_key = 'perf_plugin_run_id';
	protected $properties = [
		'perf_run_id'        => 'int',
		'perf_plugin_run_id' => 'int',
		'num_queries'        => 'int',
		'plugin_version'     => 'string',
		'plugin_name'        => 'string',
		'created_datetime'   => 'datetime',
		'total_query_time'   => 'float'
	];

	/**
	 * @inheritDoc
	 */
	public static function create( array $attributes ): Model {
		$obj = new static( $attributes );

		return tribe( Plugin_Run_Repository::class )->insert( $obj );
	}

	/**
	 * @inheritDoc
	 */
	public static function find( $id ): Model {
		return tribe( Plugin_Run_Repository::class )->get_by_id( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function save(): Model {
		if ( $this->perf_plugin_run_id ) {
			return tribe( Plugin_Run_Repository::class )->update( $this );
		} else {
			return tribe( Plugin_Run_Repository::class )->insert( $this );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete(): bool {
		return tribe( Plugin_Run_Repository::class )->delete( $this );
	}

	/**
	 * @inheritDoc
	 */
	public static function query(): ModelQueryBuilder {
		return tribe( Plugin_Run_Repository::class )->prepareQuery();
	}


	public static function collect_plugins_on_run( $run, $traces, $query_speeds ) {
		$data = [];
		foreach ( $traces as $index => $trace ) {
			foreach ( $trace as $frame ) {
				// Can we find this plugin for this frame?
				if ( $plugin = self::which_plugin( $frame ) ) {
					$id                              = $plugin['Name'] . ':' . $plugin['Version'];
					$data[ $id ]                     = $data[ $id ] ?? [
							'total_query_time' => 0,
							'num_queries'      => 0,
							'Name'             => $plugin['Name'],
							'Version'          => $plugin['Version']
						];
					$data[ $id ]['total_query_time'] += $query_speeds[ $index ];
					$data[ $id ]['num_queries'] ++;
					break;
				}
			}
		}

		foreach ( $data as $id => $plugin_data ) {
			$plugin_run                   = new static();
			$plugin_run->num_queries      = $plugin_data['num_queries'];
			$plugin_run->total_query_time = $plugin_data['total_query_time'];
			$plugin_run->plugin_name      = $plugin_data['Name'];
			$plugin_run->plugin_version   = $plugin_data['Version'];
			$plugin_run->created_datetime = date( 'Y-m-d H:i:s', time() );
			$plugin_run->perf_run_id      = $run->perf_run_id;
			$plugin_run->save();
		}

		return $data;
	}

	public static function which_plugin( $frame ) {
		try {
			if ( isset( $frame['class'] ) ) {
				if ( ! class_exists( $frame['class'], false ) ) {
					return null;
				}
				if ( ! method_exists( $frame['class'], $frame['function'] ) ) {
					return null;
				}
				$ref  = new ReflectionMethod( $frame['class'], $frame['function'] );
				$file = $ref->getFileName();
			} elseif ( isset( $frame['function'] ) && function_exists( $frame['function'] ) ) {
				$ref  = new ReflectionFunction( $frame['function'] );
				$file = $ref->getFileName();
			} elseif ( isset( $frame['file'] ) ) {
				$file = $frame['file'];
			} else {
				return null;
			}

			return self::get_plugin_from_file( $file );

		} catch ( ReflectionException $e ) {
			return null;
		}
	}

	public static function get_plugin_by_dir(): array {
		static $plugins_by_dir = [];
		if ( ! empty( $plugins_by_dir ) ) {
			return $plugins_by_dir;
		}

		$plugins_by_dir = [];
		$active_plugins = get_option( 'active_plugins' );
		// Get plugin by version
		foreach ( $active_plugins as $plugin ) {
			$entry_file  = WP_PLUGIN_DIR . '/' . $plugin;
			$plugin_data = get_plugin_data( $entry_file );

			// Get the version from plugin data
			$plugins_by_dir[ dirname( $entry_file ) . '/' ] = [
				'Name'    => $plugin_data['Name'],
				'Version' => $plugin_data['Version']
			];
		}

		// @todo need better ignore
		unset( $plugins_by_dir[ PPERF_ANALYSIS_BASE_DIR ], $plugins_by_dir[ WP_PLUGIN_DIR . '/query-monitor/' ] );

		return $plugins_by_dir;
	}

	public static function get_plugin_from_file( $file ) {
		$plugins_by_dir = self::get_plugin_by_dir();

		foreach ( $plugins_by_dir as $dir => $plugin ) {
			if ( $dir === substr( $file, 0, strlen( $dir ) ) ) {
				return $plugin;
			}
		}

		return null;
	}
}
