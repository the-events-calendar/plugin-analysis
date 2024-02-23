<?php
namespace PPerf_Analysis\Models;

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
		// @todo schema?
		return (bool) DB::delete( pperf_get_run_table_name(), [ 'perf_run_id' => $model->perf_run_id ], [ '%d' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function insert(  ModelContract $model ):  ModelContract {
		// @todo schema?
		DB::insert( pperf_get_run_table_name(), $model->toArray(), array_fill(0,count($model->toArray()),'%s') );
		$model->perf_run_id = DB::last_insert_id();

		return $model;
	}

	/**
	 * {@inheritDoc}
	 */
	function prepareQuery(): ModelQueryBuilder {
		$builder = new ModelQueryBuilder( Run::class );

		return $builder->from( pperf_get_run_table_name() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(  ModelContract $model ):  ModelContract {
		DB::update( pperf_get_run_table_name(), $model->toArray(), [ 'perf_run_id' => $model->perf_run_id ], array_fill(0,count($model->toArray()),'%s'), [ '%d' ] );

		return $model;
	}


	public function find_by_id( int $id ): ? ModelContract {
		return $this->prepareQuery()->where( 'id', $id )->get();
	}
}