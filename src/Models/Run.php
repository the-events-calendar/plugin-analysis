<?php

namespace PPerf_Analysis\Models;

class Run extends Model {
	protected $table = 'pperf_run';
	protected $primary_key = 'perf_run_id';
	protected $fields = [
		'start_time',
		'end_time',
		'request_id',
		'request_uri',
		'num_queries',
		'active_plugins',
		'hooks',
		'plugins_version_hash',
		'created_datetime',
		'total_query_time'
	];


	public function save() {
		$active_plugins = $this->active_plugins;
		ksort($active_plugins );
		$this->active_plugins = $active_plugins;
		$this->plugins_version_hash = sha1(serialize($active_plugins));

		return parent::save();
	}
}
