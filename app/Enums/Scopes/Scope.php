<?php

namespace App\Enums\Scopes;

/**
 * High-level OAuth scopes. Like Mastodon, each grants all of its granular
 * scopes (e.g. "read" grants "read:statuses").
 */
enum Scope: string
{
    use DescribesScope;

    case Read = 'read';
    case Write = 'write';
    case AdminRead = 'admin:read';
    case AdminWrite = 'admin:write';

    /** @return list<ReadScope|WriteScope|AdminReadScope|AdminWriteScope> */
    public function granular(): array
    {
        return match ($this) {
            self::Read => ReadScope::cases(),
            self::Write => WriteScope::cases(),
            self::AdminRead => AdminReadScope::cases(),
            self::AdminWrite => AdminWriteScope::cases(),
        };
    }

    /** The high-level scope that grants the given granular scope, if any. */
    public static function parentOf(string $scope): ?self
    {
        foreach (self::cases() as $parent) {
            foreach ($parent->granular() as $granular) {
                if ($granular->value === $scope) {
                    return $parent;
                }
            }
        }

        return null;
    }

    /** @return array<string, string> every scope id => description, for Passport::tokensCan() */
    public static function all(): array
    {
        $all = [];
        foreach (self::cases() as $scope) {
            $all[$scope->value] = $scope->description();
            foreach ($scope->granular() as $granular) {
                $all[$granular->value] = $granular->description();
            }
        }

        return $all;
    }
}
