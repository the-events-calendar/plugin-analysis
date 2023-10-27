<?php

namespace PPerf_Analysis\Pages;

use PPerf_Analysis\Repositories\Chart_Repository;

class Overview_Page {


	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}


	public function render() {
		echo "<div class='wrap'><h1>Performance Analysis Overview</h1>";
		$chart_repo = new Chart_Repository();
		$charts = $chart_repo->get_page_speed_by_plugin_charts();
		$html = '';
		foreach ( $charts as   $chart ) {
			$html .= $this->get_column( $chart );
		}
		echo $this->get_section( 'Page Speed',
			$this->get_container(
				$html
			)
		);
		$charts = $chart_repo->get_query_speed_charts();
		$html = '';
		foreach ( $charts as   $chart ) {
			$html .= $this->get_column( $chart );
		}
		echo $this->get_section( 'Cumulative Query Speed',
			$this->get_container(
				$html
			)
		);

		$charts = $chart_repo->get_total_queries_by_plugin_charts();
		$html = '';
		foreach ( $charts as   $chart ) {
			$html .= $this->get_column( $chart );
		}
		echo $this->get_section( 'Total Queries',
			$this->get_container(
				$html
			)
		);
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