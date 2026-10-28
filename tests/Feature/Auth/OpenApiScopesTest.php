<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Route;
use OpenApi\Annotations\Operation;
use OpenApi\Generator;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Keeps the OpenAPI `security` of each operation in sync with the auth and
 * scope middleware that actually protects its route.
 */
class OpenApiScopesTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test_documented_security_matches_route_middleware(): void
    {
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (! str_starts_with($action, 'App\\Http\\Controllers\\Api\\') || ! str_contains($action, '@')) {
                continue;
            }

            $attributes = new ReflectionMethod(...explode('@', $action))
                ->getAttributes(Operation::class, ReflectionAttribute::IS_INSTANCEOF);
            if (! $attributes) {
                continue; // undocumented route
            }

            $middleware = $route->gatherMiddleware();
            $scopes = collect($middleware)
                ->filter(fn ($m) => is_string($m) && str_starts_with($m, 'scopes:'))
                ->map(fn ($m) => substr($m, strlen('scopes:')))
                ->values()
                ->all();
            $auth = in_array('auth:api', $middleware, true);

            $expected = match (true) {
                $auth => [['passport' => $scopes]],
                $scopes !== [] => [[], ['passport' => $scopes]],
                default => Generator::UNDEFINED,
            };

            $this->assertSame($expected, $attributes[0]->newInstance()->security, "OpenAPI security of $action");
            $checked++;
        }

        $this->assertGreaterThan(70, $checked);
    }
}
