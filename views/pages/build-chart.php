<?php
/**
 * @var $snapshots \PPerf_Analysis\Models\Snapshot[]
 */

$reports = [
	[
		'id'    => 'page_speed',
		'label' => 'Page Speed',
	],
	[
		'id'    => 'cum_qry_spd',
		'label' => 'Cumulative Query Speed',
	],
	[
		'id'    => 'ttl_qry_spd',
		'label' => 'Total Query Speed',
	],
];

?>
<div class='wrap'>
	<div class="row">
		<div class="col">
			<h1>Custom Report</h1>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<div style="padding: 20px; border-radius: 20px; min-height: 120px; border: 1px solid rgba(0,0,0,0.2);">
				<div class="row">
					<div class="col-6">
						<h3>Reports</h3>
						<fieldset>
							<div class="row">
								<?php foreach ( $reports as $report ) { ?>
									<div class="col-6">
										<legend class="screen-reader-text">
											<span><?= $report['label'] ?></span>
										</legend>
										<label for="<?= $report['id'] ?>">
											<input name="<?= $report['id'] ?>" type="checkbox" id="<?= $report['id'] ?>"
											       value="1">
											<?= $report['label'] ?>
										</label>
									</div>
								<?php } ?>
							</div>
						</fieldset>
					</div>
					<div class="col-6">
						<h3>Historical Snapshots</h3>
						<fieldset>
							<select style="width: 100%; max-width: 100%;" name="historical_snapshots"
							        id="historical_snapshots" multiple>
								<?php foreach ( $snapshots as $snapshot ) { ?>
									<option
											title="<?= esc_attr( $snapshot->extended_label . "\n\nUpdated on " . $snapshot->created_datetime->format( 'F jS, Y \\a\\t H:i:s' ) ) ?>"
											value="<?= esc_attr( $snapshot->plugins_version_hash ) ?>"><?= $snapshot->abbreviated_label . ' - ' . $snapshot->created_datetime->format( 'M jS, Y' ) ?></option>
								<?php } ?>
							</select>
						</fieldset>
					</div>
					<div class="col">
						<button type="submit" class="button button-primary">Run Report</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>