<?php

namespace StellarWP\PluginStarter;

use StellarWP\PluginFramework as Framework;

/**
 * The Dependency Injection (DI) container definition for the plugin.
 *
 * @link https://github.com/stellarwp/container
 */
class Container extends Framework\Container
{
    /**
     * Retrieve a mapping of abstract identifiers to callables.
     *
     * When an abstract is requested through the container, the container will find the given
     * dependency in this array, execute the callable, and return the result.
     *
     * @return Array<string,callable|object|string|null> A mapping of abstracts to callables.
     *
     * @codeCoverageIgnore
     */
    public function config()
    {
        return array_merge(parent::config(), [
            Framework\Plugin::class => Plugin::class,
            Plugin::class           => function ($app) {
                return new Plugin(
                    $app,
                    $app->make(Framework\Services\Logger::class)
                );
            },
            Settings::class         => null,

            // Contracts.
            Framework\Contracts\ProvidesSettings::class => Settings::class,

            // WP-CLI Commands.
            Console\Commands\SetupCommand::class => function ($app) {
                return new Console\Commands\SetupCommand(
                    $app->make(Framework\Services\Cache::class),
                    $app->make(Framework\Contracts\ProvidesSettings::class),
                    $app->make(Framework\Services\SetupInstructions::class)
                );
            },
        ]);
    }
}
