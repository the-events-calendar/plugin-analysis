<?php
namespace PPerf_Analysis\Services\Charts;

interface Chart_Contract {
	public function __construct( string $shape, array $snapshots );

	public function name(): string;

	public function data(): array;

	public function to_d3( array $data ): array;

	public function html(): string;

}