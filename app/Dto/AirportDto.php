<?php

namespace App\Dto;

class AirportDto
{
    public string $provider;

    public string $foreignIdentifier;

    public ?string $iataCode = null;

    public ?string $icaoCode = null;

    public ?string $gpsCode = null;

    public ?string $localCode = null;

    public ?string $ident = null;

    public ?string $type = null;

    public string $name;

    public float $latitude;

    public float $longitude;

    public ?string $municipality = null;
}
