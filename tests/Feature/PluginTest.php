<?php

namespace Tests\Feature;

use StellarWP\PluginStarter\Console\Commands\SetupCommand;
use StellarWP\PluginStarter\Plugin;
use Tests\TestCase;

/**
 * @covers StellarWP\PluginStarter\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * @test
     * @group Commands
     */
    public function it_should_expose_the_setup_command()
    {
        $commands = $this->container->get(Plugin::class)->getCommands();

        $this->assertArrayHasKey(
            'plugin-starter setup',
            $commands,
            'NocWorx expects the setup command to be available at `wp plugin-starter setup`.'
        );
        $this->assertSame(SetupCommand::class, $commands['plugin-starter setup']);
    }
}
