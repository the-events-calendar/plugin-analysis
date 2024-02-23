<?php
/**
 * @var string $id The canvas ID.
 * @var array $chart_data The associative array of chart data passed in d3 Chart.
 * @var int $staggery_by The ms to stagger rendering by.
 */
?>
<canvas id="<?=$id?>"></canvas>
<script>
 jQuery( document ).ready(function() {
	 const ctx = document.getElementById('<?=$id?>');
	 const chartData = <?=json_encode($chart_data)?>;
        chartData.options = {
		 plugins: {
			 tooltip: {
				 callbacks: {
					 title: function(item) {
						 const index = item[0].dataIndex;
						 return item[0].dataset.context.expandedLabels[index]
                       }
				 }
			 },
		 }
	 };
        setTimeout(function() {
	        new Chart(ctx, chartData);
        },<?=$stagger_by?>);
	});
</script>