<?php
namespace PPerf_Analysis\Models;

use PPerf_Analysis\StellarWP\Models\Model;
use PPerf_Analysis\StellarWP\Models\ModelQueryBuilder;
use PPerf_Analysis\StellarWP\Models\Repositories\Repository;
use PPerf_Analysis\StellarWP\Models\Repositories\Contracts;
use PPerf_Analysis\StellarWP\Models\Contracts\Model as ModelContract;
use PPerf_Analysis\StellarWP\DB\DB;

class Run_Repository extends Repository implements Contracts\Deletable, Contracts\Insertable, Contracts\Updatable {
	/**
	 * {@inheritDoc}
	 */
	public function delete( ModelContract $model ): bool {
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$table     = "{$table_prefix}pperf_run";
		// @todo schema?
		return (bool) DB::delete( $table, [ 'perf_run_id' => $model->perf_run_id ], [ '%d' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function insert(  ModelContract $model ):  ModelContract {
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$table     = "{$table_prefix}pperf_run";
		// @todo schema?

		DB::insert( $table, $model->toArray(), array_fill(0,count($model->toArray()),'%s') );
		$model->perf_run_id = DB::last_insert_id();

		return $model;
	}

	/**
	 * {@inheritDoc}
	 */
	function prepareQuery(): ModelQueryBuilder {
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$table     = "{$table_prefix}pperf_run";
		$builder = new ModelQueryBuilder( Run::class );

		return $builder->from( $table );
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(  ModelContract $model ):  ModelContract {
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$table     = "{$table_prefix}pperf_run";
		DB::update( $table, $model->toArray(), [ 'perf_run_id' => $model->perf_run_id ], array_fill(0,count($model->toArray()),'%s'), [ '%d' ] );

		return $model;
	}


	public function find_by_id( int $id ): ? ModelContract {
		return $this->prepareQuery()->where( 'id', $id )->get();
	}
}