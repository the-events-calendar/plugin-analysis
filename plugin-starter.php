<?php
/**
 * Plugin Name: {{Plugin Starter}}
 * Plugin URI:  https://stellarwp.com
 * Description: (Your plugin description goes here)
 * Version:     1.0.0
 * Author:      StellarWP
 * Author URI:  https://stellarwp.com
 * Text Domain: stellarwp-plugin-starter
 */

namespace StellarWP\PluginStarter;

use StellarWP\PluginFramework\Exceptions\StellarWPException;
use StellarWP\PluginFramework\Services\Logger;

// Define a few constants to help with pathing.
define('StellarWP\PluginStarter\PLUGIN_VERSION', '1.0.0');
define('StellarWP\PluginStarter\PLUGIN_URL', plugins_url('/plugin-starter/', __FILE__));
define('StellarWP\PluginStarter\PLUGIN_PATH', plugin_dir_path(__FILE__) . '/plugin-starter/');
define('StellarWP\PluginStarter\VENDOR_DIR', __DIR__ . '/plugin-starter/vendor/');

require_once VENDOR_DIR . 'autoload.php';

// Initialize the plugin.
try {
    /** @var Plugin $plugin */
    $plugin = Container::getInstance()
        ->get(Plugin::class);
    $plugin->init();
} catch (\Exception $e) {
    $message = $e instanceof StellarWPException
        ? 'The {{Plugin Starter}} plugin generated an error: %s'
        : 'The {{Plugin Starter}} plugin caught the following error: %s';

    /** @var Logger $logger */
    $logger = Container::getInstance()
        ->get(Logger::class);

    $logger->error(sprintf($message, $e->getMessage()), [
        'exception' => $e,
    ]);
}
