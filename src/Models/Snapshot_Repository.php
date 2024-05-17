<?php

namespace PPerf_Analysis\Models;

use PPerf_Analysis\StellarWP\Models\ModelQueryBuilder;
use PPerf_Analysis\StellarWP\Models\Repositories\Repository;
use PPerf_Analysis\StellarWP\Models\Repositories\Contracts;
use PPerf_Analysis\StellarWP\Models\Contracts\Model as ModelContract;
use PPerf_Analysis\StellarWP\DB\DB;

class Snapshot_Repository extends Repository implements Contracts\Deletable, Contracts\Insertable, Contracts\Updatable {
	/**
	 * {@inheritDoc}
	 */
	public function delete( ModelContract $model ): bool {
		// @todo schema?
		return (bool) DB::delete( pperf_get_snapshot_table_name(), [ 'plugins_version_hash' => $model->plugins_version_hash ], [ '%s' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function insert( ModelContract $model ): ModelContract {
		// @todo schema?
		DB::insert( pperf_get_snapshot_table_name(), $model->toArray(), array_fill( 0, count( $model->toArray() ), '%s' ) );

		return $model;
	}

	/**
	 * {@inheritDoc}
	 */
	function prepareQuery(): ModelQueryBuilder {
		$builder = new ModelQueryBuilder( Snapshot::class );

		return $builder->from( 'pperf_snapshot' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function update( ModelContract $model ): ModelContract {
		DB::update( pperf_get_snapshot_table_name(), $model->toArray(), [ 'plugins_version_hash' => $model->plugins_version_hash ], array_fill( 0, count( $model->toArray() ), '%s' ), [ '%s' ] );

		return $model;
	}


	public function find_by_id( string $id ): ?ModelContract {
		return $this->prepareQuery()->where( 'plugins_version_hash', $id )->get();
	}
}