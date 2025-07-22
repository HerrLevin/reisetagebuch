<?php

namespace Tests\Unit\Controllers\Backend;

use App\Http\Controllers\Backend\InviteController;
use App\Repositories\InviteRepository;
use PHPUnit\Framework\TestCase;

class InviteControllerTest extends TestCase
{
    private InviteRepository $repository;

    private InviteController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(InviteRepository::class);
        $this->controller = new InviteController($this->repository);
    }

    public function test_index()
    {
        $userId = 'userId';
        $invites = ['invite1', 'invite2'];

        $this->repository->expects($this->once())
            ->method('getAllInvitesForUser')
            ->with($userId)
            ->willReturn($invites);

        $result = $this->controller->index($userId);
        $this->assertEquals($invites, $result);
    }

    public function test_store()
    {
        $userId = 'userId';
        $expiresAt = '2023-12-31';

        $this->repository->expects($this->once())
            ->method('createInvite')
            ->with($userId, $expiresAt);

        $this->controller->store($userId, $expiresAt);
    }
}
