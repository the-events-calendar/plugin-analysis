<?php
namespace PPerf_Analysis\Models;

use lucatume\DI52\Container;
use PPerf_Analysis\StellarWP\Models\Model;
use PPerf_Analysis\StellarWP\Models\ModelQueryBuilder;

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
		'hooks' => 'string',
		'plugins_version_hash' => 'string',
		'created_datetime' => 'datetime',
		'total_query_time' => 'float'
	];

	/**
	 * @inheritDoc
	 */
	public static function create( array $attributes ) : Model {
		$obj = new static( $attributes );

		return (new Container())->make( Run_Repository::class )->insert( $obj );
	}

	/**
	 * @inheritDoc
	 */
	public static function find( $id ) : Model {
		return (new Container())->make( Run_Repository::class )->get_by_id( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function save() : Model {
		if($this->perf_run_id) {
			return (new Container())->make( Run_Repository::class )->update( $this );
		} else {
			return (new Container())->make( Run_Repository::class )->insert( $this );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete() : bool {
		return (new Container())->make( Run_Repository::class )->delete( $this );
	}

	/**
	 * @inheritDoc
	 */
	public static function query() : ModelQueryBuilder {
		return (new Container())->make( Run_Repository::class )->prepareQuery();
	}
}
