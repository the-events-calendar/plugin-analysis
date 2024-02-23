<?php
namespace PPerf_Analysis\Services\Charts;

use PPerf_Analysis\Repositories\Page_Speed as Repository;

class Page_Speed extends Abstract_Chart {
	public function data():array {
		$repository = new Repository();
		$data = [];
		// @todo Per
		foreach ($this->snapshots as $snapshot) {
			$data[] = $repository->fetch($watched_page, $snapshot);
		}

		return $data;
	}

	public function name():string {
		return (string)__('Page Speed', 'plugin-performance-analysis'); // @todo slug
	}
}