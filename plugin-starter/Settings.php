<?php

namespace StellarWP\PluginStarter;

use StellarWP\PluginFramework\Settings as BaseSettings;

/**
 * A global settings object that can retrieve details about a site from its environment.
 */
class Settings extends BaseSettings
{
    /**
     * Load all custom settings.
     *
     * This method gets called as part of $this->load(), and can override any of the default settings
     * (but can still be overridden via $this->overrides).
     *
     * @param Array<string,mixed> $settings Current settings. These are provided for reference and may
     *                                      be returned, but it's not required to do so.
     *
     * @return Array<string,mixed> Custom settings.
     */
    protected function loadSettings(array $settings)
    {
        return [
            'framework_url'  => plugins_url('/vendor/stellarwp/plugin-framework/', __FILE__),
            'plugin_version' => '1.0.0-dev',
        ];
    }
}
