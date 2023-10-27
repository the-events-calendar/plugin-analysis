<?php

namespace PPerf_Analysis\Pages;

use PPerf_Analysis\Repositories\Chart_Repository;

class Plugin_Cumulative_Query_Page {


	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}


	public function render() {
		echo "<div class='wrap'><h1>Cumulative Query Speed per Plugin</h1>";
		$chart_repo = new Chart_Repository();

		$plugin_data = $chart_repo->get_query_speed_by_plugin_chart_data();

		foreach ( $plugin_data as $name => $charts ) {
			$html = '';
			foreach ( $charts as $i => $chart ) {
				$id   = sha1( $name . $i );
				$html .= $this->get_column( $chart_repo->get_chart( "pperf_main_qry_spd_$id", $chart ) );
			}
			echo $this->get_section( $name,
				$this->get_container( $html )
			);
		}


		echo "</div>";
		echo $this->get_style();
	}

	public function get_container( $content ) {
		return <<<STR
<div class='pperf_container'>
{$content}
</div>
STR;
	}

	public function get_column( $content ) {
		return <<<STR
<div class='pperf_column'>
{$content}
</div>
STR;
	}

	public function get_section( $title, $content ) {
		return <<<STR
<h2>{$title}</h2>
{$content}
STR;

	}

	public function get_style() {
		return <<<STR
<style>
.pperf_container {
  display: flex;
}

.pperf_column {
  flex: 1;
  padding: 1em; 
}

/* Responsive breakpoints */
@media (max-width: 768px) {
  .pperf_container {
    flex-wrap: wrap;
  }

  .pperf_column {
    flex-basis: 100%;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .pperf_column {
    flex-basis: 50%;
  }
}

@media (min-width: 1025px) {
  .pperf_column {
    flex-basis: 33.33%;    
  }
}
</style>
STR;

	}

}