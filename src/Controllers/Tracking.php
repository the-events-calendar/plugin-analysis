<?php
namespace PPerf_Analysis\Controllers;

use PPerf_Analysis\Models\Plugin_Run;
use PPerf_Analysis\Models\Run;
use PPerf_Analysis\Controllers\Custom_Chart_Page;
use PPerf_Analysis\Controllers\Overview_Page;
use PPerf_Analysis\Controllers\Plugin_Cumulative_Query_Page;
use PPerf_Analysis\Controllers\Settings_Page;
use PPerf_Analysis\lucatume\DI52\App;
use PPerf_Analysis\lucatume\DI52\Container;
use PPerf_Analysis\Services\Tracking as TrackingService;




class Tracking {
	public function register() {
		$tracker = new TrackingService();

		add_action( 'shutdown', [ $tracker, 'on_shutdown' ], 9999 );
		add_action( 'log_query_custom_data', [ $tracker, 'on_query' ], 10, 5 );
	}
}