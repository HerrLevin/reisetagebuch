<?php

namespace App\Dto;

readonly class OsmImportResult
{
    public function __construct(
        public string $extract,
        public int $imported,
        public int $skipped,
    ) {}
}
