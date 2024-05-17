<?php
namespace PPerf_Analysis\Repositories;

class Snapshot_Repository {

	/**
	 * @var wpdb
	 */
	protected $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}



	public function get_all(   ) {
		$table     = pperf_get_plugin_run_table_name();
		$run_table = pperf_get_run_table_name();
		$q    = "select distinct plugins_version_hash from $run_table";
		$rows = $this->wpdb->get_results( $q );
		foreach ( $rows as $row ) {
			$q = "select * 
from $run_table  
order by created_datetime
limit 1";
			$run_row = $this->wpdb->get_row( $q );
			// @todo create a shortened label util
			$labels[]                    = $this->get_shortened_label( $run_row );
		}

		return $labels;
	}
}