<?php

namespace StellarWP\PluginStarter;

use StellarWP\PluginFramework as Framework;

/**
 * The main plugin instance.
 */
class Plugin extends Framework\Plugin
{
    /**
     * An array containing all registered WP-CLI commands.
     *
     * @var Array<string,class-string<Framework\Console\WPCommand>>
     */
    protected $commands = [
        'plugin-starter extension'    => Framework\Console\Commands\ExtensionCommand::class,
        'plugin-starter ithemes'      => Framework\Console\Commands\iThemesCommand::class,
        'plugin-starter setup'        => Console\Commands\SetupCommand::class,
        'plugin-starter support-user' => Framework\Console\Commands\SupportUserCommand::class,
    ];

    /**
     * An array containing all registered modules.
     *
     * @var Array<int,class-string<Framework\Modules\Module>>
     */
    protected $modules = [
        Framework\Modules\ExtensionConfig::class,
        Framework\Modules\PurgeCaches::class,
    ];

    /**
     * An array containing all registered plugin configurations.
     *
     * @var Array<string,class-string<Framework\Extensions\Plugins\PluginConfig>>
     */
    protected $plugins = [
        'cache-enabler/cache-enabler.php' => Framework\Extensions\Plugins\CacheEnabler::class,
        'redis-cache/redis-cache.php'     => Framework\Extensions\Plugins\RedisCache::class,
    ];
}
