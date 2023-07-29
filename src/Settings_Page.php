<?php

namespace PPerf_Analysis;

class Settings_Page {
	public function register_settings() {
		register_setting(
			PPERF_ANALYSIS_SLUG . '-settings',
			PPERF_ANALYSIS_SLUG . '-options',
		);

		add_settings_section(
			PPERF_ANALYSIS_SLUG . '-analysis-settings-section',
			'Performance Analysis',
			'',
			PPERF_ANALYSIS_SLUG . '-settings'
		);

		add_settings_field(
			PPERF_ANALYSIS_SLUG . '-watched-pages',
			'Watched Pages',
			[ $this, 'render_watched_pages' ],
			PPERF_ANALYSIS_SLUG . '-settings',
			PPERF_ANALYSIS_SLUG . '-analysis-settings-section'
		);
	}

	function handle_settings_form_submission() {
		// Process and save the form data here
		$settings_page = PPERF_ANALYSIS_SLUG . '-settings';
		$key           = PPERF_ANALYSIS_SLUG . '-options';
		$watched_pages = sanitize_textarea_field( $_POST['watched_pages'] );
		$watched_pages = array_filter( array_map( 'trim', explode( "\n", $watched_pages ) ) );
		$fields        = [
			'watched_pages' => $watched_pages,
		];
		update_option( $key, $fields, true );
		// Redirect back to the settings page
		wp_redirect( admin_url( "admin.php?page=$settings_page" ) );
		exit;
	}


	function render_watched_pages() {
		$key           = PPERF_ANALYSIS_SLUG . '-options';
		$options       = get_option( $key );
		$watched_pages = [];
		if ( ! empty( $options['watched_pages'] ) && is_array( $options['watched_pages'] ) ) {
			$watched_pages = $options['watched_pages'];
		}

		$field_value = implode( "\n", array_filter( $watched_pages ) );
		?>
        <textarea style="white-space: pre-wrap; width: 440px; min-height: 100px"
                  name="watched_pages"><?php echo $field_value; ?></textarea>
        <p class="description"><?php echo _x( "A list of relative request URI's being watched for performance metrics. <br>Add one URI per line.", "The watched pages admin setting help text.", PPERF_ANALYSIS_SLUG ); ?></p>
		<?php
	}

	public function render() {
		$action_slug = PPERF_ANALYSIS_SLUG . '-save';
		$action      = admin_url( 'admin-post.php' );

		?>
        <div class="wrap">
            <h1>Settings</h1>
            <form method="post" action="<?php echo esc_attr( $action ); ?>">
				<?php
				settings_fields( PPERF_ANALYSIS_SLUG . '-settings' );
				do_settings_sections( PPERF_ANALYSIS_SLUG . '-settings' );
				?>
                <input type="hidden" name="action" value="<?php echo $action_slug; ?>"/>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
            </form>
        </div>
		<?php
	}
}