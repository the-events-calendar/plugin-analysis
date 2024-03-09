<?php
/**
 * @var array $page_speed_by_plugin_charts
 * @var array $query_speed_charts
 * @var array $total_queries_by_plugin_charts
 */

use PPerf_Analysis\Services\Templates;
$stagger_by = 0;
?>
<div class='wrap'>
	<div class="row">
		<div class="col">
			<h1>Performance Analysis Overview</h1>
		</div>
	</div>
	<div class="row">
		<div class='col'>
			<h2>Page Speed</h2>
		</div>
	</div>
	<div class="row">
		<?php
		foreach ( $page_speed_by_plugin_charts as $i => $chart ) {
			$data = [
				'stagger_by' => $stagger_by+=120,
				'chart_data' => $chart,
				'id'         => "pperf_main_pg_spd_$i",
			];
			?>
			<div class='col-sm-12 col-md-3'>
				<?= Templates::render_view( 'd3-chart', $data ); ?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="row">
		<div class='col'><h2>Cumulative Query Speed</h2></div>
	</div>
	<div class="row">
		<?php
		foreach ( $query_speed_charts as $i => $chart ) {
			$data = [
				'stagger_by' => $stagger_by+=120,
				'chart_data' => $chart,
				'id'         => "pperf_main_qry_spd_$i",
			];
			?>
			<div class='col-sm-12 col-md-3'>
				<?= Templates::render_view( 'd3-chart', $data ); ?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="row">
		<div class='col'><h2>Total Queries</h2></div>
	</div>
	<div class="row">
		<?php
		foreach ( $total_queries_by_plugin_charts as $i => $chart ) {
			$data = [
				'stagger_by' => $stagger_by+=120,
				'chart_data' => $chart,
				'id'         => "pperf_main_total_qry_$i",
			];
			?>
			<div class='col-sm-12 col-md-3'>
				<?= Templates::render_view( 'd3-chart', $data ); ?>
			</div>
			<?php
		}
		?>
	</div>
</div>