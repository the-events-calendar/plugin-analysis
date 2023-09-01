<?php
namespace PPerf_Analysis\Models;

use PPerf_Analysis\StellarWP\Models\Model;
use PPerf_Analysis\StellarWP\Models\ModelQueryBuilder;
use PPerf_Analysis\Models\Run_Repository;
use PPerf_Analysis\StellarWP\Models\Contracts;

class Run extends Model  {
	protected $table = 'pperf_run';
	protected $primary_key = 'perf_run_id';
	protected $properties = [
		'perf_run_id' => 'int',
		'start_time' => 'float',
		'end_time' => 'float',
		'request_id' => 'string',
		'request_uri' => 'string',
		'num_queries' => 'int',
		'active_plugins' => 'string',
		'hooks' => 'string',
		'plugins_version_hash' => 'string',
		'created_datetime' => 'datetime',
		'total_query_time' => 'float'
	];

	public function __todsave() {
		$active_plugins = $this->active_plugins;
		ksort($active_plugins );
		$this->active_plugins = $active_plugins;
		$this->plugins_version_hash = sha1(serialize($active_plugins));

		//return parent::save();
	}

	/**
	 * @inheritDoc
	 */
	public static function create( array $attributes ) : Model {
		$obj = new static( $attributes );

		return tribe( Run_Repository::class )->insert( $obj );
	}

	/**
	 * @inheritDoc
	 */
	public static function find( $id ) : Model {
		return tribe( Run_Repository::class )->get_by_id( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function save() : Model {
		if($this->perf_run_id) {
			return tribe( Run_Repository::class )->update( $this );
		} else {
			return tribe( Run_Repository::class )->insert( $this );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete() : bool {
		return tribe( Run_Repository::class )->delete( $this );
	}

	/**
	 * @inheritDoc
	 */
	public static function query() : ModelQueryBuilder {
		return tribe( Run_Repository::class )->prepareQuery();
	}
}
