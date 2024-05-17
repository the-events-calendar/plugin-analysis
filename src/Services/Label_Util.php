<?php

namespace PPerf_Analysis\Services;

class Label_Util {
	/**
	 * Get the shortened snapshot label.
	 *
	 * @param array $plugins The plugins in the snapshot.
	 *
	 * @return string The shortened snapshot label.
	 */
	public static function get_shortened_snapshot_label( $plugins ) {
		$short_labels = array_map( function ( $plugin ) use ( $plugins ) {
			return ucwords( trim( preg_replace( '/\b(\w)|./', '$1', $plugin ) ) ) . ' ' . $plugins[ $plugin ];
		}, array_keys( $plugins ) );

		return implode( ', ', $short_labels );
	}

	/**
	 * Get the extended snapshot label.
	 *
	 * @param array $plugins The plugins in the snapshot.
	 *
	 * @return string The extended snapshot label.
	 */
	public static function get_extended_snapshot_label( $plugins ) {
		$full_labels = [];
		foreach ( $plugins as $plugin => $version ) {
			$full_labels[] = "$plugin $version";
		}
		$plugins_expanded = implode( "\n", $full_labels );

		return $plugins_expanded;
	}
}
