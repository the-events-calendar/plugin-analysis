<?php
/**
 * Plugin Name: {{Plugin Starter}} (Development)
 * Description: Bootstrap the {{Plugin Starter}} plugin for development.
 * Author:      StellarWP
 * Version:     local-development
 */

// Don't verify SSL requests in development environments.
add_filter('https_ssl_verify', '__return_false');

/**
 * Set any necessary environment variables here.
 *
 * To emulate SiteWorx variables in development environments, prefix the SiteWorx variable name
 * with "siteworx_".
 *
 * @see StellarWP\PluginStarter\Settings::loadSettings()
 * @see StellarWP\PluginFramework\Settings::load()
 */

// Postman mock server for the Nexcess MAPPS API
putenv('siteworx_mapp_endpoint=https://fbbc473f-a386-4cd1-8b0c-c55f9006d7a8.mock.pstmn.io');
putenv('siteworx_mapp_token=PMAK-5db1d28873646a002a75d3d3-730c723572a2acc8b9fcd5753dc5f88ff6');

/**
 * Finally, load the main plugin file.
 */
$bootstrap = __DIR__ . '/plugin-starter/plugin-starter.php';

// Uncomment this line to use the built version in dist/:
//$bootstrap = __DIR__ . '/plugin-starter/dist/plugin-starter.php';

require_once $bootstrap;
