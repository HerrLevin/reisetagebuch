<?php

namespace Tests\Unit\Hydrators;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class InviteHydratorTest extends TestCase
{
    public function testModelToDto()
    {
        $invite = $this->createMock(\App\Models\Invite::class);
        $invite->method('__get')->willReturnCallback(function ($property) {
            return match ($property) {
                'id' => 'asdf',
                'created_at' => Carbon::parse('2023-01-01 00:00:00'),
                'expires_at' => Carbon::parse('2023-01-01 00:00:01'),
                'used_at' => Carbon::parse('2023-01-01 00:00:02'),
                default => null,
            };
        });

        $inviteHydrator = new \App\Hydrators\InviteHydrator();
        $dto = $inviteHydrator->modelToDto($invite);

        $this->assertEquals('asdf', $dto->id);
        $this->assertEquals('2023-01-01T00:00:00+00:00', $dto->createdAt);
        $this->assertEquals('2023-01-01T00:00:01+00:00', $dto->expiresAt);
        $this->assertEquals('2023-01-01T00:00:02+00:00', $dto->usedAt);
    }
}
