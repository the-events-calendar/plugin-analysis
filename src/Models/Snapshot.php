<?php

namespace PPerf_Analysis\Models;

use DateTime;
use PPerf_Analysis\Services\Label_Util;
use PPerf_Analysis\StellarWP\Models\Model;
use PPerf_Analysis\StellarWP\Models\ModelQueryBuilder;
use PPerf_Analysis\StellarWP\Models\Contracts\ModelFromQueryBuilderObject;

/**
 * @property string $active_plugins 	 The active plugins in the snapshot.
 * @property string $plugins_version_hash The hash of the active plugins.
 * @property DateTime $created_datetime The date and time the snapshot was created.
 * @property array $plugins The active plugins in the snapshot.
 * @property string $abbreviated_label A shortened label for the snapshot.
 * @property string $extended_label An extended label for the snapshot.
 */
class Snapshot extends Model implements ModelFromQueryBuilderObject{
	protected $table = 'pperf_snapshot';
	protected $primary_key = 'plugins_version_hash';
	protected $properties = [
		'active_plugins'       => 'string',
		'plugins_version_hash' => 'string',
		'created_datetime'     => 'string',
	];

	public function __get( $field ) {
		switch($field){
			case 'plugins':
				return unserialize($this->active_plugins);
			case 'abbreviated_label':
				return Label_Util::get_shortened_snapshot_label($this->plugins);
			case 'created_datetime':
				return new DateTime( $this->getAttribute('created_datetime'));
			case 'extended_label':
				return Label_Util::get_extended_snapshot_label($this->plugins);
			default:
				return parent::__get( $field );
		}
	}

	public static function fromQueryBuilderObject( $object ) {
		$attributes = [
			'active_plugins'       => $object->active_plugins,
			'plugins_version_hash' => $object->plugins_version_hash,
			'created_datetime'     => $object->created_datetime,
		];

		return new static( $attributes );
	}

	/**
	 * @inheritDoc
	 */
	public static function create( array $attributes ): Model {
		$obj = new static( $attributes );

		return tribe( Snapshot_Repository::class )->insert( $obj );
	}

	/**
	 * @inheritDoc
	 */
	public static function find( $id ): ?Model {
		return tribe( Snapshot_Repository::class )->find_by_id( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function save(): Model {
		return tribe( Snapshot_Repository::class )->insert( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function update(): Model {
		return tribe( Snapshot_Repository::class )->update( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function delete(): bool {
		return tribe( Snapshot_Repository::class )->delete( $this );
	}

	/**
	 * @inheritDoc
	 */
	public static function query(): ModelQueryBuilder {
		return tribe( Snapshot_Repository::class )->prepareQuery();
	}
}
