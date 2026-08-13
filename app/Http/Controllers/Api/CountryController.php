<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CountriesDto;
use OpenApi\Attributes as OA;

class CountryController extends Controller
{
    private \App\Http\Controllers\Backend\CountryController $countryController;

    public function __construct(\App\Http\Controllers\Backend\CountryController $countryController)
    {
        parent::__construct();
        $this->countryController = $countryController;
    }

    #[OA\Get(
        path: '/statistics/countries',
        operationId: 'getCountriesForUser',
        description: 'Return the ISO A2 codes of countries a user has visited or merely travelled through',
        summary: 'User country insights',
        tags: ['Statistics'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: CountriesDto::class))]
    )]
    public function index(): CountriesDto
    {
        return $this->countryController->getVisitedAndTransitedCountries($this->auth->user()->id);
    }
}
