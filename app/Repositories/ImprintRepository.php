<?php

namespace App\Repositories;

use App\Dto\ImprintDto;
use App\Models\Imprint;

class ImprintRepository
{
    public function get(): ImprintDto
    {
        return new ImprintDto(
            content: Imprint::query()->first()?->content,
        );
    }

    public function update(string $content): ImprintDto
    {
        $imprint = Imprint::query()->firstOrCreate([]);
        $imprint->content = $content;
        $imprint->save();

        return new ImprintDto(
            content: $imprint->content,
        );
    }
}
