<?php

declare(strict_types=1);

namespace App\Dto\MotisApi;

class LegGeometryDto
{
    public string $points;

    public int $precision;

    public function setPoints(string $points): LegGeometryDto
    {
        $this->points = $points;

        return $this;
    }

    public function setPrecision(int $precision): LegGeometryDto
    {
        $this->precision = $precision;

        return $this;
    }
}
