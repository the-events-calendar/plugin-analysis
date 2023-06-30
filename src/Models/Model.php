<?php

namespace Perf\Models;

class Model {
	protected $primary_key = '';
	protected $fields = [];
	protected $table = '';
	protected $data = [];
	protected $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		// Validate
		if(!$this->primary_key) {
			trigger_error("Invalid model ".get_class($this)." - missing `primary_key` field.", E_USER_WARNING);
		}
		if(!$this->table) {
			trigger_error("Invalid model ".get_class($this)." - missing `table` field.", E_USER_WARNING);
		}
		if(!in_array($this->primary_key, $this->fields, true)) {
			$this->fields[] = $this->primary_key;
		}
	}

	public function save() {
		$primary_key = $this->primary_key;
		$is_insert = !($this->$primary_key);


		$vals = $this->to_array();
		if($is_insert) {
			foreach ($vals as $key => $val) {
				if(!is_scalar($val)) {
					$vals[$key] = json_encode($val);
				}
			}
			$this->wpdb->insert( $this->wpdb->prefix. $this->table, $vals );
			$id = $this->wpdb->insert_id;
			$this->$primary_key = $id;
		} else {
			$this->wpdb->update( $this->wpdb->prefix.$this->table, $vals, [$primary_key => $this->$primary_key] );
		}

		return true;
	}

	public function get_table() {
		return $this->wpdb->prefix.$this->table;
	}

	public function to_array():array {
		return $this->data;
	}

	public function __set( $field, $val ) {
		$valid_field = in_array($field, $this->fields, true);
		if(!$valid_field) {
			trigger_error("Trying to set an undefined field $field.", E_USER_NOTICE);
			return;
		}

		$this->data[$field] = $val;
	}

	public function __get( $field ) {
		$valid_field = in_array($field, $this->fields, true);
		if(!$valid_field) {
			trigger_error("Undefined field $field.", E_USER_NOTICE);
			return null;
		}

		if(!isset($this->data[$field])) {
			return null;
		}

		return $this->data[$field];
	}
}