<?php
namespace PPerf_Analysis\Services;
class Templates {
	const VIEW_DIR = PPERF_ANALYSIS_BASE_DIR.'views/';
	public static function render_view( $path, $vars = [] ) {
		extract($vars);
		$file_path = file_exists(self::VIEW_DIR.$path) ? self::VIEW_DIR.$path : self::VIEW_DIR.$path.'.php';
		ob_start();
		include($file_path);
		return ob_get_clean();
	}
}