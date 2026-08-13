<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountriesDto;
use App\Repositories\CountryRepository;

class CountryController extends Controller
{
    private CountryRepository $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function getVisitedAndTransitedCountries(string $userId): CountriesDto
    {
        $visited = $this->countryRepository->getVisitedCountryCodes($userId);
        $transitedOnly = $this->countryRepository->getTransitedOnlyCountryCodes($userId, $visited);

        return new CountriesDto($visited, $transitedOnly);
    }
}
