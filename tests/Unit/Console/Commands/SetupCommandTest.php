<?php

namespace Tests\Unit\Console\Commands;

use StellarWP\PluginFramework\Console\Command;
use StellarWP\PluginFramework\Console\Response;
use StellarWP\PluginFramework\Services\Cache;
use StellarWP\PluginFramework\Services\SetupInstructions;
use StellarWP\PluginFramework\Support\InstructionSet;
use StellarWP\PluginStarter\Console\Commands\SetupCommand;
use Tests\TestCase;

/**
 * @covers StellarWP\PluginStarter\Console\Commands\SetupCommand
 *
 * @group Console
 */
class SetupCommandTest extends TestCase
{
    /**
     * @var \Mockery\Mock&Cache
     */
    protected $cache;

    /**
     * @var \Mockery\Mock&Command
     */
    protected $command;

    /**
     * @todo https://liquidweb.atlassian.net/browse/SPG-59
     */
    public function setUpAfterContainer()
    {
        $this->cache = $this->mock(Cache::class)
            ->shouldIgnoreMissing();
        $this->container->extend(Cache::class, $this->cache);

        $this->command = $this->mock(SetupCommand::class, $this->getClassDependencies(SetupCommand::class))
            ->shouldAllowMockingProtectedMethods();
        $this->command
            ->shouldIgnoreMissing($this->command)
            ->shouldReceive('__invoke')->passthru();
    }

    /**
     * @test
     */
    public function the_setup_command_and_setup_instructions_should_access_the_same_settings()
    {
        $this->assertSame(
            $this->getProtectedProperty($this->command, 'settings'),
            $this->getProtectedProperty($this->getProtectedProperty($this->command, 'setupInstructions'), 'settings'),
            'SetupCommand and SetupInstructions should reference the same Settings, but they do not.'
        );
    }

    /**
     * @test
     */
    public function the_setup_command_should_update_WordPress_core_and_extensions()
    {
        $this->command->shouldReceive('updateWordPress')
            ->once()
            ->andReturnSelf();

        call_user_func([$this->command, '__invoke'], [], []);
    }

    /**
     * @test
     */
    public function the_setup_command_should_set_a_default_permalink_structure()
    {
        $this->command->shouldReceive('setDefaultPermalinkStructure')
            ->once()
            ->andReturnSelf();

        call_user_func([$this->command, '__invoke'], [], []);
    }

    /**
     * @test
     * @testdox The setup command should not flush all caches without the --provision flag
     */
    public function the_setup_command_should_not_flush_all_caches_without_the_provision_flag()
    {
        $this->cache->shouldReceive('purgeAll')->never();

        call_user_func([$this->command, '__invoke'], [], []);
    }

    /**
     * @test
     * @testdox The setup command should flush all caches when called with the --provision flag
     */
    public function the_setup_command_should_flush_all_caches_when_called_with_the_provision_flag()
    {
        $this->cache->shouldReceive('purgeAll')->once();

        call_user_func([$this->command, '__invoke'], [], [
            'provision' => true,
        ]);
    }

    /**
     * @test
     */
    public function the_setup_command_should_follow_setup_instructions_from_the_Partner_Gateway()
    {
        $instructions = new InstructionSet();

        for ($i = 0; $i < 3; $i++) {
            /** @var \Mockery\Mock&Command $command */
            $command = $this->mock(Command::class);
            $command->shouldReceive('getShellCommand')
                ->andReturn(sprintf('command %d', $i));
            $command->shouldReceive('execute')
                ->once()
                ->andReturn(new Response(sprintf('command %d', $i), 0, 'Some output'));
            $instructions->addCommand($command);
        }

        $service = $this->mock(SetupInstructions::class);
        $service->shouldReceive('getInstructions')
            ->once()
            ->andReturn($instructions);
        $this->container->extend(SetupInstructions::class, $service);

        // Build a fresh mock, since we've altered the container contents.
        $command = $this->mock(SetupCommand::class, $this->getClassDependencies(SetupCommand::class))
            ->shouldAllowMockingProtectedMethods();
        $command->shouldIgnoreMissing($command);
        $command->shouldReceive('__invoke')->passthru();
        $command->shouldReceive('runSetupInstructions')->passthru();

        $command([], []);
    }
}
