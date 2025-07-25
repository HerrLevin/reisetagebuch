<?php

namespace Feature\Services;

use App\Services\VersionService;
use Tests\TestCase;

class VersionServiceTest extends TestCase
{
    public function test_get_version()
    {
        config()->set('app.version', '1.0.0');
        $versionService = new VersionService;
        $version = $versionService->getVersion();
        $this->assertEquals('1.0.0', $version);
    }

    public function test_get_user_agent()
    {
        config()->set('app.version', '1.2.3');
        config()->set('app.name', 'TestApp');
        config()->set('app.url', 'https://example.com');
        config()->set('app.legal.email', 'mail@example.com');

        $versionService = new VersionService;
        $userAgent = $versionService->getUserAgent();
        $this->assertTrue(false);
        $this->assertEquals('TestApp/1.2.3 (https://example.com; bot; contact: mail@example.com)', $userAgent);
    }

    public function test_get_version_without_version()
    {
        config()->set('app.version', '');
        $versionService = new VersionService;
        $version = $versionService->getVersion();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{5}$/', $version);
        $this->assertNotEquals('unknown', $version);
    }

    public function test_git_version_without_head()
    {
        config()->set('app.version', '');
        $versionServiceMock = $this->getMockBuilder(VersionService::class)
            ->onlyMethods(['getGitHead'])
            ->getMock();

        $versionServiceMock->method('getGitHead')->willReturn(null);

        $version = $versionServiceMock->getVersion();
        $this->assertEquals('unknown', $version);
    }

    public function test_git_version_with_defective_head()
    {
        config()->set('app.version', '');
        $versionServiceMock = $this->getMockBuilder(VersionService::class)
            ->onlyMethods(['getGitHead'])
            ->getMock();

        $versionServiceMock->method('getGitHead')->willReturn('ThisWillNotWork');

        $version = $versionServiceMock->getVersion();
        $this->assertEquals('unknown', $version);
    }
}
