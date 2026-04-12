<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\ImprintDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImprintUpdateRequest;
use App\Repositories\ImprintRepository;
use Illuminate\Support\Facades\Cache;

class ImprintBackend extends Controller
{
    public function __construct(
        private readonly ImprintRepository $imprintRepository
    ) {}

    public function show(): ImprintDto
    {
        $imprint = $this->imprintRepository->get();

        return $this->cache($imprint);
    }

    public function update(ImprintUpdateRequest $request): ImprintDto
    {
        $imprint = $this->imprintRepository->update($request->input('content'));

        return $this->cache($imprint);
    }

    private function cache(ImprintDto $dto): ImprintDto
    {
        Cache::forget('imprint');

        return Cache::rememberForever(
            'imprint',
            function () use ($dto): ImprintDto {
                return $dto;
            }
        );
    }
}
