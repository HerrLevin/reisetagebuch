<?php

namespace Tests\Feature\Controllers\Api;

use Tests\TestCase;

class AppConfigurationControllerTest extends TestCase
{
    public function test_index_reports_debug_disabled_and_production_environment(): void
    {
        config(['app.debug' => false, 'app.env' => 'production']);

        $response = $this->getJson(route('app.configuration'));

        $response->assertOk();
        $response->assertJson([
            'isDebug' => false,
            'environment' => 'production',
        ]);
    }

    public function test_index_reports_debug_enabled_and_local_environment(): void
    {
        config(['app.debug' => true, 'app.env' => 'local']);

        $response = $this->getJson(route('app.configuration'));

        $response->assertOk();
        $response->assertJson([
            'isDebug' => true,
            'environment' => 'local',
        ]);
    }
}
