<?php

namespace Tests\Unit;

use StellarWP\PluginStarter\Settings;
use Tests\TestCase;

/**
 * @covers StellarWP\PluginStarter\Settings
 *
 * @group Settings
 */
class SettingsTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_expose_a_framework_url_property()
    {
        $this->assertStringEndsWith('/stellarwp/plugin-framework/', (new Settings())->framework_url);
    }
}
